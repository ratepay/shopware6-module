<?php

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
    public const FIELD_ID = 'id';

    public const FIELD_VERSION = 'version';

    public const FIELD_OPERATION = 'operation';

    public const FIELD_SUB_OPERATION = 'subOperation';

    public const FIELD_RESULT = 'result';

    public const FIELD_REQUEST = 'request';

    public const FIELD_RESPONSE = 'response';

    public const FIELD_ADDITIONAL_DATA = 'additionalData';

    public const FIELD_CREATED_AT = 'createdAt';

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var string
     */
    protected $subOperation;

    /**
     * @var string
     */
    protected $result;

    /**
     * @var array
     */
    protected $additionalData;

    /**
     * @var string
     */
    protected $request;

    /**
     * @var string
     */
    protected $response;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    public function getSubOperation(): string
    {
        return $this->subOperation;
    }

    public function setSubOperation(string $subOperation): void
    {
        $this->subOperation = $subOperation;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getRequest(): string
    {
        return $this->request;
    }

    public function setRequest(string $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }
}
