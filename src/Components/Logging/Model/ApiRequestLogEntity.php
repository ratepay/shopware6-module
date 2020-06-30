<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Model;

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

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return string
     */
    public function getSubOperation(): string
    {
        return $this->subOperation;
    }

    /**
     * @param string $subOperation
     */
    public function setSubOperation(string $subOperation): void
    {
        $this->subOperation = $subOperation;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     * @param string $request
     */
    public function setRequest(string $request): void
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @param array $additionalData
     */
    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }


}
