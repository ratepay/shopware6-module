<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\AdminOrders\Service;


use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
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

    public function getDfpId(): ?string
    {
        if (!$this->session->get($this->sessionKey)) {
            return $this->decorated->getDfpId();
        }

        $this->deleteToken(); // make sure that no token is stored in the session by a previous session
        return null;
    }

    public function isDfpIdAlreadyGenerated(): bool
    {
        return $this->decorated->isDfpIdAlreadyGenerated();
    }

    public function deleteToken(): void
    {
        $this->decorated->deleteToken();
    }
}
