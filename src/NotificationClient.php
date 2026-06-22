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


    public function send(SendMessageRequest $request): NotificationResponse
    {
        return $this->post('/v1/notification/send', $request->toArray());
    }


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

    public function sendWhatsapp(
        string $whatsapp,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::whatsapp($whatsapp)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }

    public function sendPush(
        string $push,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::push($push)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }


    public function sendDiscord(
        string $discord,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::discord($discord)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }

    public function sendSlack(
        string $slack,
        string $template,
        array $data = [],
        array $extra = []
    ): NotificationResponse {
        return $this->send(new SendMessageRequest([
            'message' => new TemplateMessage([
                'to' => [Recipient::slack($slack)],
                'template' => $template,
                'data' => $data,
                ...$extra,
            ]),
        ]));
    }


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

            // ✅ Convert string errors to array
            if (is_string($errors)) {
                $errors = !empty($errors) ? [$errors] : [];
            }

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