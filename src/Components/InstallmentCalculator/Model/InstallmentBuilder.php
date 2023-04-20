<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;


use BadMethodCallException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;

class InstallmentBuilder extends \RatePAY\Frontend\InstallmentBuilder
{

    private ProfileConfigEntity $profileConfig;

    private ProfileConfigMethodEntity $methodConfig;

    public function __construct(
        ProfileConfigEntity $profileConfig,
        ProfileConfigMethodEntity $methodConfig,
        string $language = 'DE',
        string $country = 'DE'
    )
    {
        $this->profileConfig = $profileConfig;
        $this->methodConfig = $methodConfig;

        parent::__construct(
            $profileConfig->isSandbox(),
            $profileConfig->getProfileId(),
            $profileConfig->getSecurityCode(),
            $language,
            $country
        );
    }

    /**
     * @param string $profileId
     */
    public function setProfileId($profileId): void
    {
        if ($this->profileConfig->getProfileId() !== $profileId) {
            throw new BadMethodCallException('please do not set profile id manually. Please use constructor');
        }

        // call from constructor
        parent::setProfileId($profileId);
    }

    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }

    public function getMethodConfig(): ProfileConfigMethodEntity
    {
        return $this->methodConfig;
    }
}
