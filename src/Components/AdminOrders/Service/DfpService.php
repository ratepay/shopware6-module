<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\AdminOrders\Service;

use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DfpService implements DfpServiceInterface
{

    private DfpServiceInterface $decorated;

    private string $sessionKey;

    private RequestStack $requestStack;

    public function __construct(DfpServiceInterface $decorated, RequestStack $requestStack, string $sessionKey)
    {
        $this->decorated = $decorated;
        $this->requestStack = $requestStack;
        $this->sessionKey = $sessionKey;
    }

    public function generatedDfpId(Request $request, $baseData): ?string
    {
        return $this->decorated->generatedDfpId($request, $baseData);
    }

    public function getDfpSnippet(Request $request, $baseData): ?string
    {
        return $this->decorated->getDfpSnippet($request, $baseData);
    }

    public function isDfpIdValid($baseData, string $dfpId = null): bool
    {
        return $this->decorated->isDfpIdValid($baseData, $dfpId);
    }

    public function isDfpRequired($object): bool
    {
        $session = $this->requestStack->getMainRequest()->getSession();
        if ($session->get($this->sessionKey)) {
            return false;
        }

        return $this->decorated->isDfpRequired($object);
    }
}
