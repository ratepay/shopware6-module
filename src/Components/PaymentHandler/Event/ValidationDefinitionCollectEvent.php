<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use RuntimeException;
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
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    private object $baseData;

    /**
     * @param OrderEntity|SalesChannelContext $baseData
     * @noinspection PhpDocSignatureInspection
     */
    public function __construct(
        array $definitions,
        DataBag $requestDataBag,
        object $baseData
    )
    {
        $this->definitions = $definitions;
        $this->requestDataBag = $requestDataBag;

        if (!$baseData instanceof SalesChannelContext && !$baseData instanceof OrderEntity) {
            throw new RuntimeException('$baseData should be on of OrderEntity or SalesChannelContext');
        }

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
