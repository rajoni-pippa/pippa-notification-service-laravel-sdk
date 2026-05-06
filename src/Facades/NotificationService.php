<?php

namespace Pippa\NotificationSdkLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Pippa\NotificationSdkLaravel\NotificationClient;

/**
 * @method static \Pippa\NotificationSdkLaravel\DTOs\NotificationResponse send(\Pippa\NotificationSdkLaravel\Requests\SendMessageRequest $request)
 * @method static \Pippa\NotificationSdkLaravel\DTOs\NotificationResponse sendEmail(string $email, string $template, array $data = [], array $extra = [])
 * @method static \Pippa\NotificationSdkLaravel\DTOs\NotificationResponse sendSms(string $phone, string $template, array $data = [], array $extra = [])
 * @method static \Pippa\NotificationSdkLaravel\DTOs\NotificationResponse sendInApp(string $userId, string $template, array $data = [], array $extra = [])
 * @method static \Pippa\NotificationSdkLaravel\DTOs\NotificationResponse sendMulti(array $recipients, string $template, array $data = [], array $extra = [])
 *
 * @see NotificationClient
 */
class NotificationService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotificationClient::class;
    }
}
