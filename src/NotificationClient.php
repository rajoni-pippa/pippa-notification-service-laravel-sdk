<?php

namespace Pippa\NotificationSdkLaravel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Pippa\NotificationSdkLaravel\DTOs\NotificationResponse;
use Pippa\NotificationSdkLaravel\DTOs\Recipient;
use Pippa\NotificationSdkLaravel\DTOs\TemplateMessage;
use Pippa\NotificationSdkLaravel\Exceptions\NotificationException;
use Pippa\NotificationSdkLaravel\Requests\SendMessageRequest;

/**
 * NotificationClient — Laravel / Guzzle
 *
 * Requires guzzlehttp/guzzle and illuminate/support.
 * Auto-discovered by Laravel via NotificationServiceProvider.
 * Bind via DI or use the NotificationService facade.
 *
 * Usage (Facade):
 *   NotificationService::sendEmail('user@example.com', 'welcome_email', ['name' => 'Rahim']);
 *
 * Usage (DI):
 *   public function __construct(protected NotificationClient $notification) {}
 */
class NotificationClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected int $timeout;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        string $secretKey,
        int $timeout = 30
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->timeout = $timeout;
    }

    // =========================================================
    //  PUBLIC API  (Courier-style)
    // =========================================================

    /**
     * Send a notification using full SendMessageRequest control.
     *
     * @throws NotificationException
     */
    public function send(SendMessageRequest $request): NotificationResponse
    {
        return $this->post('/v1/notification/send', $request->toArray());
    }

    // =========================================================
    //  SHORTCUT HELPERS
    // =========================================================

    /**
     * Send an email notification via template.
     *
     * @throws NotificationException
     */
    public function sendEmail(
        string $email,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::email($email)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }

    /**
     * Send an SMS notification via template.
     *
     * @throws NotificationException
     */
    public function sendSms(
        string $phone,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::phone($phone)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }

    /**
     * Send an in-app notification via template.
     *
     * @throws NotificationException
     */
    public function sendInApp(
        string $userId,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::userId($userId)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }

    /**
     * Send to multiple recipients/channels at once.
     *
     * @param  Recipient[]  $recipients
     * @throws NotificationException
     */
    public function sendMulti(
        array $recipients,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => $recipients,
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }

    // =========================================================
    //  HTTP — Guzzle (required dependency)
    // =========================================================

    /**
     * @throws NotificationException
     */
    protected function post(string $endpoint, array $payload): NotificationResponse
    {
        try {
            $client = new Client([
                'timeout' => $this->timeout,
                'verify' => false,
            ]);

            $response = $client->post($this->baseUrl . $endpoint, [
                'headers' => $this->buildHeaders(),
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true) ?? [];

            return NotificationResponse::fromArray($body, $statusCode);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = json_decode((string) $e->getResponse()->getBody(), true) ?? [];
            $message = $body['message'] ?? $e->getMessage();
            $errors = $body['errors'] ?? [];

            throw new NotificationException($message, $statusCode, $errors, $e);

        } catch (GuzzleException $e) {
            throw new NotificationException($e->getMessage(), $e->getCode(), [], $e);
        }
    }

    protected function buildHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'apiKey' => $this->apiKey,
            'secretKey' => $this->secretKey,
        ];
    }
}
