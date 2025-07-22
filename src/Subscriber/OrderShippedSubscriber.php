<?php

namespace OrderShippedWebhook\Subscriber;

use OrderShippedWebhook\MessageQueue\Message\SendWebhookMessage;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderShippedSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_DELIVERY_WRITTEN_EVENT => 'onDeliveryWritten',
        ];
    }

    /**
     * @throws ExceptionInterface
     */
    public function onDeliveryWritten($event): void
    {
        if ($event->getEntityName() !== 'order_delivery') {
            return;
        }

        foreach ($event->getWriteResults() as $result) {

            $payload = $result->getPayload();

            if (!isset($payload['stateId'])) {
                continue;
            }

            //medina
            if ($payload['stateId'] != '018c62a034e073e5af065a1645f380d5') {
                continue;
            }

            if (!isset($payload['orderId'])) {
                continue;
            }
            $orderId = $payload['orderId'];
            $this->bus->dispatch(new SendWebhookMessage($orderId));
        }
    }
}
