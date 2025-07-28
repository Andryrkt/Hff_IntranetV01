<?php

namespace App\Service\session;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

        return new RedirectResponse('/'.$_ENV['BASE_URL']);
    }
}
