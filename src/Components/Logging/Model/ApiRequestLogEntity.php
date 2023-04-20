<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ApiRequestLogEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    public const FIELD_ID = 'id';

    /**
     * @var string
     */
    public const FIELD_VERSION = 'version';

    /**
     * @var string
     */
    public const FIELD_OPERATION = 'operation';

    /**
     * @var string
     */
    public const FIELD_SUB_OPERATION = 'subOperation';

    /**
     * @var string
     */
    public const FIELD_RESULT_CODE = 'resultCode';

    /**
     * @var string
     */
    public const FIELD_RESULT_TEXT = 'resultText';

    /**
     * @var string
     */
    public const FIELD_STATUS_CODE = 'statusCode';

    /**
     * @var string
     */
    public const FIELD_STATUS_TEXT = 'statusText';

    /**
     * @var string
     */
    public const FIELD_REASON_CODE = 'reasonCode';

    /**
     * @var string
     */
    public const FIELD_REASON_TEXT = 'reasonText';

    /**
     * @var string
     */
    public const FIELD_REQUEST = 'request';

    /**
     * @var string
     */
    public const FIELD_RESPONSE = 'response';

    /**
     * @var string
     */
    public const FIELD_ADDITIONAL_DATA = 'additionalData';

    /**
     * @var string
     */
    public const FIELD_CREATED_AT = 'createdAt';

    protected string $version;

    protected string $operation;

    protected string $subOperation;

    protected array $additionalData;

    protected string $request;

    protected string $response;

    protected string $resultCode;

    protected string $resultText;

    protected string $statusCode;

    protected string $statusText;

    protected string $reasonCode;

    protected string $reasonText;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getSubOperation(): string
    {
        return $this->subOperation;
    }

    /**
     * @deprecated please use `getResultText`
     */
    public function getResult(): string
    {
        return $this->getResultText();
    }

    public function getRequest(): string
    {
        return $this->request;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    public function getResultText(): string
    {
        return $this->resultText;
    }

    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    public function getStatusText(): string
    {
        return $this->statusText;
    }

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    public function getReasonText(): string
    {
        return $this->reasonText;
    }
}
