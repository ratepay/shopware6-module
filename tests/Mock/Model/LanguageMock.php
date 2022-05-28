<?php

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageEntity;

class LanguageMock
{

    public static function createMock(string $languageCode, string $localeCode)
    {
        $language = new LanguageEntity();
        $language->setId(Uuid::randomHex());
        $language->setUniqueIdentifier($language->getId());
        $language->setName($languageCode);
        $language->setLocale(LocaleMock::createMock($localeCode));

        return $language;
    }
}
