<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData;

class ProfileRequestService extends AbstractRequest
{
    /**
     * @var string
     */
    final public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    /**
     * @var string
     */
    final public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    /**
     * @var string
     */
    final public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    /**
     * @var string
     */
    final public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    /**
     * @var string
     */
    final public const EVENT_INIT_REQUEST = self::class . parent::EVENT_INIT_REQUEST;

    protected string $_operation = self::CALL_PROFILE_REQUEST;

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof ProfileRequestData;
    }
}
