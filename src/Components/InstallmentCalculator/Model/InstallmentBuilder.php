<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;


use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;

class InstallmentBuilder extends \RatePAY\Frontend\InstallmentBuilder
{

    /**
     * @var \Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity
     */
    private ProfileConfigEntity $profileConfig;

    public function __construct(ProfileConfigEntity $profileConfig, $language = 'DE', $country = 'DE')
    {
        $this->profileConfig = $profileConfig;

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
            throw new \BadMethodCallException('please do not set profile id manually. Please use constructor');
        }
        // call from constructor
        parent::setProfileId($profileId);
    }

    /**
     * @return \Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity
     */
    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }
}
