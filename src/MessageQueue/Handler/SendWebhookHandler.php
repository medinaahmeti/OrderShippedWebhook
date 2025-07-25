<?php

namespace OrderShippedWebhook\MessageQueue\Handler;

use DateTimeInterface;
use OrderShippedWebhook\MessageQueue\Message\SendWebhookMessage;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SendWebhookHandler
{
    /**
     * @phpstan-ignore-next-line
     */
    public function __construct(
        private readonly EntityRepository    $orderRepository,
        private readonly HttpClientInterface $httpClient
    )
    {
    }

    public function __invoke(SendWebhookMessage $message): void
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria([$message->getOrderId()]);
        $criteria->addAssociations([
            'currency',
            'lineItems',
            'billingAddress.country',
            'deliveries.shippingMethod.country',
            'transactions.paymentMethod'
        ]);

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($criteria, $context)->first();

        if (!$order instanceof OrderEntity) {
            return;
        }

        $lineItems = $order->getLineItems() ?? [];
        $billingAddress = $order->getBillingAddress();
        $deliveries = $order->getDeliveries();
        $delivery = $deliveries?->first();
        $transactions = $order->getTransactions();
        $transaction = $transactions?->first();
        $shippingAddress = $delivery?->getShippingOrderAddress();
        $phoneNumber = $shippingAddress?->getPhoneNumber();

        $trackingNumbers = $delivery?->getTrackingCodes() ?? [];
        $trackingCode = implode(', ', $trackingNumbers);

        $products = [];
        foreach ($lineItems as $item) {
            $products[] = [
                'product_id' => $item->getProductId(),
                'name' => $item->getLabel(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getUnitPrice(),
                'currency' => $order->getCurrency()?->getIsoCode(),
            ];
        }

        $customer = $order->getOrderCustomer();
        $payload = [
            'order' => [
                'order_id' => $order->getId(),
                'order_date' => $order->getCreatedAt()?->format(DateTimeInterface::ATOM),
                'customer' => [
                    'customer_id' => $customer?->getCustomerId(),
                    'name' => trim(($customer?->getFirstName() ?? '') . ' ' . ($customer?->getLastName() ?? '')),
                    'email' => $customer?->getEmail(),
                    "phone" => $phoneNumber
                ],
                'shipping_address' => [
                    'name' => trim(($shippingAddress?->getFirstName() ?? '') . ' ' . ($shippingAddress?->getLastName() ?? '')),
                    'street' => $shippingAddress?->getStreet(),
                    'city' => $shippingAddress?->getCity(),
                    'state' => $shippingAddress?->getCountryState()?->getShortCode(),
                    'postal_code' => $shippingAddress?->getZipcode(),
                    'country' => $shippingAddress?->getCountry()?->getName(),
                ],
                'billing_address' => [
                    'name' => trim(($billingAddress?->getFirstName() ?? '') . ' ' . ($billingAddress?->getLastName() ?? '')),
                    'street' => $billingAddress?->getStreet(),
                    'city' => $billingAddress?->getCity(),
                    'state' => $billingAddress?->getCountryState()?->getShortCode(),
                    'postal_code' => $billingAddress?->getZipcode(),
                    'country' => $billingAddress?->getCountry()?->getName(),
                ],
                'products' => $products,
                'total_amount' => $order->getAmountTotal(),
                'payment' => [
                    'payment_method' => $transaction?->getPaymentMethod()?->getName(),
                    'payment_provider' => $transaction?->getPaymentMethod()?->getFormattedHandlerIdentifier(),
                    'payment_state' => $transaction?->getStateMachineState()?->getTechnicalName(),
                ],
                'delivery' => [
                    'delivery_method' => $delivery?->getShippingMethod()?->getName(),
                    'tracking_number' => $trackingCode,
                    'delivery_status' => $delivery?->getStateMachineState()?->getTechnicalName(),
                    'estimated_delivery_date' => $delivery?->getShippingDateEarliest()?->format('Y-m-d')
                ],
                'order_status' => $order->getStateMachineState()?->getTechnicalName()
            ]
        ];
        $this->httpClient->request('POST', 'https://webhook.site/2b117c33-df76-4701-bb5f-22f22f8645f0', [
            'json' => $payload
        ]);
    }
}
