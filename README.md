# Notification SDK — Laravel

Laravel SDK for the **Notification Service** API.  
Requires **Guzzle** — includes ServiceProvider, Facade, and DI support.

---

## Installation

```bash
composer require pippa/notification-sdk-laravel
```

Laravel will auto-discover the service provider and facade.

---

## Configuration

Publish config:

```bash
php artisan vendor:publish --tag=notification-config
```

Add to `.env`:

```env
NOTIFICATION_API_KEY=your_api_key
NOTIFICATION_SECRET_KEY=your_secret_key
```

---

## Usage — Facade

```php
use Pippa\NotificationSdkLaravel\Facades\NotificationService;

// Send Email
$response = NotificationService::sendEmail(
    email:    'user@example.com',
    template: 'welcome_email',
    data:     ['name' => 'Rahim']
);

// Send SMS
$response = NotificationService::sendSms(
    phone:    '+8801700000000',
    template: 'otp_sms',
    data:     ['otp' => '1234']
);

// Send In-App
$response = NotificationService::sendInApp(
    userId:   'user_123',
    template: 'order_update',
    data:     ['order_id' => 'ORD-456', 'status' => 'Shipped']
);
```

### Multi-channel (Courier-style)

```php
use Pippa\NotificationSdkLaravel\Facades\NotificationService;
use Pippa\NotificationSdkLaravel\Requests\SendMessageRequest;
use Pippa\NotificationSdkLaravel\DTOs\TemplateMessage;
use Pippa\NotificationSdkLaravel\DTOs\Recipient;

$response = NotificationService::send(
    new SendMessageRequest([
        'message' => new TemplateMessage([
            'to' => [
                Recipient::email('user@example.com'),
                Recipient::phone('+8801700000000'),
                Recipient::userId('user_123'),
            ],
            'template' => 'welcome_notification',
            'data'     => ['name' => 'Rahim'],
        ]),
    ])
);

echo $response->getRequestId();
```

### Restrict a recipient to specific channels

```php
Recipient::make([
    'email'   => 'user@example.com',
    'phone'   => '+8801700000000',
    'user_id' => 'user_123',
])->only(['email', 'sms'])
```

---

## Usage — Dependency Injection

```php
use Pippa\NotificationSdkLaravel\NotificationClient;

class OrderService
{
    public function __construct(protected NotificationClient $notification) {}

    public function notifyShipped(string $email, string $orderId): void
    {
        $this->notification->sendEmail(
            email:    $email,
            template: 'order_shipped',
            data:     ['order_id' => $orderId]
        );
    }
}
```

---

## Exception Handling

```php
use Pippa\NotificationSdkLaravel\Exceptions\NotificationException;

try {
    NotificationService::sendEmail('user@example.com', 'my_template');
} catch (NotificationException $e) {
    $e->getMessage();
    $e->getCode();
    $e->getErrors();
}
```

---

## Available Methods

| Method                                       | Description                    |
| -------------------------------------------- | ------------------------------ |
| `send(SendMessageRequest)`                   | Full control                   |
| `sendEmail($email, $template, $data)`        | Send email via template        |
| `sendSms($phone, $template, $data)`          | Send SMS via template          |
| `sendInApp($userId, $template, $data)`       | Send in-app notification       |
| `sendMulti($recipients[], $template, $data)` | Multi-channel, multi-recipient |

---

## Recipient Helpers

```php
use Pippa\NotificationSdkLaravel\DTOs\Recipient;

Recipient::email('user@example.com')
Recipient::phone('+8801700000000')
Recipient::userId('user_123')
Recipient::make(['email' => '...', 'phone' => '...', 'user_id' => '...'])
Recipient::make([...])->only(['email', 'sms'])
```

---

## License

MIT
