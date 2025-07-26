<?php

namespace OrderShippedWebhook\MessageQueue\Message;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

class SendWebhookMessage implements AsyncMessageInterface
{
    public function __construct(private readonly string $orderId)
    {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}