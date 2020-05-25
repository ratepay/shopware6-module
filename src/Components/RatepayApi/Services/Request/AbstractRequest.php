<?php


namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Response\AbstractResponse;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\RequestLogger;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use RatePAY\RequestBuilder;

abstract class AbstractRequest
{

    const CALL_PAYMENT_REQUEST = "PaymentRequest"; // yes, first letter is uppercase. The sdk expect that
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

    /**
     * @var string
     */
    protected $_operation = null;

    protected $_subType = null;
    /** @var bool */
    protected $isRequestSkipped = false;
    /**
     * @var HeadFactory
     */
    private $headFactory;

    public function __construct(
        ConfigService $configService,
        RequestLogger $requestLogger,
        HeadFactory $headFactory
    )
    {
        $this->configService = $configService;
        $this->requestLogger = $requestLogger;
        $this->headFactory = $headFactory;
    }

    /**
     * @return RequestBuilder
     */
    public final function doRequest()
    {
        $response = $this->call();
        if ($response->getResponse()->isSuccessful()) {
            $this->processSuccess($response);
        }
        return $response;
    }

    /**
     * @return RequestBuilder
     */
    private function call()
    {
        $profileConfig = $this->getProfileConfig();
        if ($profileConfig == null) {
            throw new \Exception('Transaction can not performed, cause no profile was found.');
        }

        $head = $this->getRequestHead($profileConfig);
        $content = $this->getRequestContent();

        $requestBuilder = new RequestBuilder($profileConfig->isSandbox());
        $requestBuilder = $requestBuilder->__call('call' . $this->_operation, $content ? [$head, $content] : [$head]);
        if ($this->_subType) {
            $requestBuilder = $requestBuilder->subtype($this->_subType);
        }

        $this->requestLogger->logRequest($requestBuilder->getRequestRaw(), $requestBuilder->getResponseRaw());

        return $requestBuilder;
    }

    /**
     * @return ProfileConfigEntity
     */
    abstract protected function getProfileConfig();

    protected function getRequestHead(ProfileConfigEntity $profileConfig): Head
    {
        return $this->headFactory->getData($profileConfig);
    }

    /**
     * @return Content
     */
    protected function getRequestContent(): ?Content
    {
        return null;
    }

    protected function processSuccess(RequestBuilder $response)
    {
        // do nothing
    }

}