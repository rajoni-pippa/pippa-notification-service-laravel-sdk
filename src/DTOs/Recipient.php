<?php

namespace Pippa\NotificationSdkLaravel\DTOs;


class Recipient
{
    private array $data = [];

    private function __construct(array $data)
    {
        $this->data = array_filter($data, fn($v) => $v !== null && $v !== '');
    }


    public static function email(string $email): static
    {
        return new static(['email' => $email]);
    }

    public static function phone(string $phone): static
    {
        return new static(['phone' => $phone]);
    }

    public static function whatsapp(string $whatsapp): static
    {
        return new static(['whatsapp' => $whatsapp]);
    }

    public static function userId(string $userId): static
    {
        return new static(['user_id' => $userId]);
    }

    public static function push(string $push): static
    {
        return new static(['push' => $push]);
    }

    public static function discord(string $discord): static
    {
        return new static(['discord' => $discord]);
    }

    public static function slack(string $slack): static
    {
        return new static(['slack' => $slack]);
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public function only(array $channels): static
    {
        $this->data['channels'] = $channels;
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
