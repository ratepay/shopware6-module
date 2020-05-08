<?php


namespace Ratepay\RatepayPayments\Core\RatepayApi\Services\Request;


use RatePAY\Model\Response\AbstractResponse;
use RatePAY\ModelBuilder;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use RatePAY\RequestBuilder;
use Ratepay\RatepayPayments\Core\RatepayApi\Services\RequestLogger;

abstract class AbstractRequest
{

    const CALL_PAYMENT_REQUEST = "paymentRequest";
    const CALL_PAYMENT_CONFIRM = "paymentConfirm";
    const CALL_DELIVER = "confirmationDeliver";
    const CALL_CHANGE = "paymentChange";
    const CALL_PROFILE_REQUEST = "profileRequest";
    /**
     * @var ConfigService
     */
    protected $configService;
    /**
     * @var RequestLogger
     */
    protected $requestLogger;
    protected $_subType = null;
    /** @var bool */
    protected $isRequestSkipped = false;
    private $shopwareVersionNumber;

    public function __construct(
        ConfigService $configService,
        RequestLogger $requestLogger,
        $shopwareVersionNumber
    )
    {
        $this->configService = $configService;
        $this->requestLogger = $requestLogger;
        $this->shopwareVersionNumber = $shopwareVersionNumber;
    }

    /**
     * @return AbstractResponse
     */
    public final function doRequest()
    {
        /** @var AbstractResponse $response */
        $response = $this->call(null, false);
        if ($response === true || $response->isSuccessful()) {
            $this->processSuccess();
        }
        return $response;
    }

    private function call(array $content = null, $isRetry = false)
    {
        if ($this->isSkipRequest()) {
            $this->isRequestSkipped = true;
            return true;
        }
        $content = $content ?: $this->getRequestContent();
        $profileConfig = $this->getProfileConfig();
        if ($profileConfig == null) {
            throw new \Exception('Transaction can not performed, cause no profile was found.');
        }

        $mbHead = new ModelBuilder('head');
        $mbHead->setArray($this->getRequestHead($profileConfig));

        $mbContent = null;
        if ($content) {
            $mbContent = new ModelBuilder('Content');
            $mbContent->setArray($content);
        }

        $rb = new RequestBuilder($profileConfig->isSandbox());
        $rb = $rb->__call('call' . ucfirst($this->getCallName()), $mbContent ? [$mbHead, $mbContent] : [$mbHead]);
        if ($this->_subType) {
            $rb = $rb->subtype($this->_subType);
        }

        //yes this is "correct" - all functions with "get" or "is" as prefix will piped to this (abstract) model
        /** @var AbstractResponse $responseModel */
        $responseModel = $rb;
        $this->requestLogger->logRequest($rb->getRequestRaw(), $rb->getResponseRaw());

        if ($responseModel->isSuccessful()) {
            return $responseModel;
        } elseif ($isRetry === false && intval($responseModel->getReasonCode()) == 2300) {
            return $this->call($content, true);
        } else {
            return $responseModel;
        }
    }

    protected function isSkipRequest()
    {
        return false;
    }

    /**
     * @return array
     */
    abstract protected function getRequestContent();

    /**
     * @param $isBackend
     * @return ProfileConfigEntity
     */
    abstract protected function getProfileConfig();

    protected function getRequestHead(ProfileConfigEntity $profileConfig)
    {
        $head = [
            'SystemId' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'cli/cronjob/api',
            'Credential' => [
                'ProfileId' => $profileConfig->getProfileId(),
                'Securitycode' => $profileConfig->getSecurityCode()
            ],
            'Meta' => [
                'Systems' => [
                    'System' => [
                        'Name' => 'Shopware',
                        'Version' => $this->shopwareVersionNumber . '/' . $this->configService->getPluginVersion()
                    ]
                ]
            ]
        ];
        return $head;
    }

    /**
     * @return string
     */
    abstract protected function getCallName();

    abstract protected function processSuccess();

}
