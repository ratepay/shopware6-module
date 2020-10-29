<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePay\RpayPayments\Tests;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Definition\ProfileConfigDefinition;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class DefaultTest extends TestCase
{
    //use IntegrationTestBehaviour;

    private const DEFAULT_PROFILE_ID = 'e818164cb2a346d394a742bd8e22084e';

    public function testProfileCreate(): void
    {
        $context = Context::createDefaultContext();
        /** @var EntityRepositoryInterface $profileRepository */
        $profileRepository = $this->getContainer()->get(ProfileConfigDefinition::ENTITY_NAME . '.repository');

        $profileRepository->upsert([
            array(
                ProfileConfigEntity::FIELD_ID => self::DEFAULT_PROFILE_ID,
                ProfileConfigEntity::FIELD_PROFILE_ID => 'INTEGRATION_TE_DACH_OPT',
                ProfileConfigEntity::FIELD_SECURITY_CODE => 'a---LZuRV3fDp5dIeQ9pxFGN2lgQ85FS9WZi',
                ProfileConfigEntity::FIELD_SANDBOX => true,
                ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => Defaults::SALES_CHANNEL,
            )
        ], $context);

        $profiles = $profileRepository->search(new Criteria([self::DEFAULT_PROFILE_ID]), $context);

        $this->assertCount(1, $profiles);
        $this->assertInstanceOf(ProfileConfigEntity::class, $profiles->first());
        $this->assertEquals('INTEGRATION_TE_DACH_OPT', $profiles->first()->getProfileId());
    }

    public function testProfileConfigResponseConverter(): void
    {


    }

    /**
     * @depends testProfileCreate
     */
    public function testProfileRefresh(): void
    {

        /** @var ProfileConfigService $profileConfigService */
        $profileConfigService = $this->getContainer()->get(ProfileConfigService::class);

        $profileConfigService->refreshProfileConfigs([
            self::DEFAULT_PROFILE_ID
        ]);
    }
}
