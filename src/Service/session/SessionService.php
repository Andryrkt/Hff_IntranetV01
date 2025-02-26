<?php

namespace App\Service\session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SessionService
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function destroySession(): RedirectResponse
    {
        $this->session->invalidate(); // Supprime toutes les données de session
        return new RedirectResponse('/Hffintranet/');
    }
}
