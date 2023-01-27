<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\AdminOrders\Service;

use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DfpService implements DfpServiceInterface
{

    private DfpServiceInterface $decorated;

    private SessionInterface $session;

    private string $sessionKey;

    public function __construct(DfpServiceInterface $decorated, SessionInterface $session, string $sessionKey)
    {
        $this->decorated = $decorated;
        $this->session = $session;
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
        if ($this->session->get($this->sessionKey)) {
            return false;
        }

        return $this->decorated->isDfpRequired($object);
    }
}
