<?php

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Locale\LocaleEntity;

class LocaleMock
{

    public static function createMock(string $localeCode): LocaleEntity
    {
        $locale = new LocaleEntity();
        $locale->setId(Uuid::randomHex());
        $locale->setUniqueIdentifier($locale->getId());
        $locale->setName($localeCode);
        $locale->setCode($localeCode);

        return $locale;
    }

}
