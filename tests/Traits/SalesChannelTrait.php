<?php
declare(strict_types=1);

namespace Ratepay\RpayPayments\Tests\Traits;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

trait SalesChannelTrait
{
    protected function getSalesChannel(): SalesChannelEntity
    {
        /** @var EntityRepositoryInterface $salesChannelRepository */
        $salesChannelRepository = $this->getContainer()->get('sales_channel.repository');
        $salesChannelCriteria = new Criteria([]);

        /** @var SalesChannelEntity $salesChannel */
        return $salesChannelRepository->search($salesChannelCriteria, Context::createDefaultContext())->first();
    }
}
