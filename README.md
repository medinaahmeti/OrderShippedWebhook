Order Shipped Webhook plugin

````markdown
# Order Shipped Webhook

A Shopware 6 plugin that triggers a webhook when an order's delivery status changes to **Shipped**.
It sends order details (customer, products, addresses, tracking info) to a specified webhook endpoint.

## Features

- Listens for `order_delivery` state changes.
- Dispatches a `SendWebhookMessage` to send data asynchronously.
- Sends comprehensive order payload including:
  - Customer information (name, email, phone)
  - Billing and shipping addresses
  - Products with quantity and price
  - Payment details
  - Delivery method, tracking codes, and estimated delivery date
- Uses Symfony Messenger and Shopware MessageQueue.
- Configurable via System Config:
  - Enable/disable the webhook.
  - Specify the `shippedStateId` to listen for.

## Installation

1. Copy the plugin folder `OrderShippedWebhook` to your Shopware `custom/plugins/` directory.
2. Install and activate the plugin:

   ```bash
   bin/console plugin:install --activate OrderShippedWebhook
````

3. Clear cache and recompile:

   ```bash
   bin/console cache:clear
   ```

## Configuration

Navigate to **Settings → System → Plugins → OrderShippedWebhook** and configure:

* **Enable Subscriber Webhook**: Turn the webhook listener on/off.
* **Shipped State ID**: Set the technical state ID for the "Shipped" delivery status.

## Webhook Payload Example

```json
{
  "order": {
    "order_id": "12345",
    "order_date": "2025-07-25T12:34:56+00:00",
    "customer": {
      "customer_id": "c123",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+123456789"
    },
    "shipping_address": {
      "name": "John Doe",
      "street": "123 Street",
      "city": "Berlin",
      "state": "BE",
      "postal_code": "10115",
      "country": "Germany"
    },
    "billing_address": { ... },
    "products": [
      {
        "product_id": "p123",
        "name": "Product Name",
        "quantity": 2,
        "price": 19.99,
        "currency": "EUR"
      }
    ],
    "total_amount": 39.98,
    "payment": {
      "payment_method": "PayPal",
      "payment_provider": "paypal_handler",
      "payment_state": "paid"
    },
    "delivery": {
      "delivery_method": "DHL",
      "tracking_number": "123456789",
      "delivery_status": "shipped",
      "estimated_delivery_date": "2025-07-28"
    },
    "order_status": "completed"
  }
}
```

## Development

* PHP: `^8.1`
* Shopware: `~6.5.0`

### Code Structure

* **MessageQueue**

  * `SendWebhookMessage` – Encapsulates the order ID.
  * `SendWebhookHandler` – Fetches order details and sends the webhook.
* **Subscriber**

  * `OrderShippedSubscriber` – Listens for `order_delivery` state changes and dispatches messages.

## CMS Banner Slider

The plugin also registers a **Banner Slider CMS Element** with:

* Configurable slides with image & text.
* Uses Bootstrap Carousel for frontend rendering.

---

### Author

Order Shipped Webhook Plugin by Medina Ahmeti