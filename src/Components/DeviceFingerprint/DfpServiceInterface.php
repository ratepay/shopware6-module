<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\DeviceFingerprint;

interface DfpServiceInterface
{
    public const SESSION_VAR_NAME = 'ratepay_dfp_token';

    public function getDfpId(): ?string;

    public function isDfpIdAlreadyGenerated(): bool;

    public function deleteToken(): void;
}
