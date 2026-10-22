<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\Checkout\Cart;


use Shopware\Core\Checkout\Cart\Error\Error;

class SecciExpiredError extends Error
{
    public const KEY = 'secci-expired';

    public function getId(): string
    {
        return self::KEY;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getLevel(): int
    {
        return self::LEVEL_WARNING;
    }

    public function blockOrder(): bool
    {
        return true;
    }

    public function getParameters(): array
    {
        return [];
    }
}