<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Dev;

use Composer\Script\Event;

class ComposerInstall
{

    private const SW_PACKAGES = [
        'shopware/core',
        'shopware/storefront',
        'shopware/administration'
    ];

    public static function removeShopwareFromRequirements(Event $event)
    {
        $list = $event->getComposer()->getPackage()->getRequires();

        foreach(self::SW_PACKAGES as $packageToRemove) {
            unset($list[$packageToRemove]);
        }

        $event->getComposer()->getPackage()->setRequires($list);
    }

}
