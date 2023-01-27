<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraint;

class ValidationDefinitionCollectEvent
{

    private array $definitions;

    private DataBag $requestDataBag;

    /**
     * @var OrderEntity|SalesChannelContext
     */
    private $baseData;

    /**
     * @param OrderEntity|SalesChannelContext $baseData
     */
    public function __construct(
        array $definitions,
        DataBag $requestDataBag,
        $baseData
    )
    {
        $this->definitions = $definitions;
        $this->requestDataBag = $requestDataBag;
        $this->baseData = $baseData;
    }

    /**
     * @return array
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param array<Constraint>|Constraint $constraint
     */
    public function addDefinition(string $field, $constraint): self
    {
        $path = explode('/', $field);
        array_pop($path);

        $currentNode = &$this->definitions;
        foreach ($path as $nodeKey) {
            $currentNode = &$currentNode[$nodeKey];
        }

        $currentNode[$field] = $constraint;

        return $this;
    }

    /**
     * @return OrderEntity|SalesChannelContext
     */
    public function getBaseData(): object
    {
        return $this->baseData;
    }

    public function getRequestDataBag(): DataBag
    {
        return $this->requestDataBag;
    }

}
