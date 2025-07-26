<?php

namespace OrderShippedWebhook\Subscriber;

use OrderShippedWebhook\MessageQueue\Message\SendWebhookMessage;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderShippedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly SystemConfigService $configService
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_DELIVERY_WRITTEN_EVENT => 'onDeliveryWritten',
        ];
    }

    public function onDeliveryWritten(EntityWrittenEvent $event): void
    {
        $enabled = $this->configService->get('OrderShippedWebhook.config.enableSubscriberWebhook');
        if (!$enabled) {
            return;
        }
        $stateId = $this->configService->get('OrderShippedWebhook.config.shippedStateId');
        if (!$stateId) {
            return;
        }

        if ($event->getEntityName() !== 'order_delivery') {
            return;
        }

        foreach ($event->getWriteResults() as $result) {

            $payload = $result->getPayload();

            if (!isset($payload['stateId'])) {
                continue;
            }

            if ($payload['stateId'] != $stateId) {
                continue;
            }

            if (!isset($payload['orderId'])) {
                continue;
            }
            
            /** @var string $orderId */
            $orderId = $payload['orderId'];
            $this->bus->dispatch(new SendWebhookMessage($orderId));
        }
    }
}
