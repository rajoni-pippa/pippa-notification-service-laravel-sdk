<?php

namespace Pippa\NotificationSdkLaravel;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/notification.php',
            'notification'
        );

        $this->app->singleton(NotificationClient::class, function ($app) {
            $config = $app['config']['notification'];

            return new NotificationClient(
                baseUrl: 'https://naas.api.pippasync.com/api',
                apiKey: $config['api_key'],
                secretKey: $config['secret_key'],
                timeout: $config['timeout'] ?? 30,
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/notification.php' => config_path('notification.php'),
            ], 'notification-config');
        }
    }
}