<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionManagerService
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function set($name, $value)
    {
        return $this->session->set($name, $value);
    }

    public function get($name)
    {
        return $this->session->get($name);
    }

    public function has($name)
    {
        return $this->session->has($name);
    }

    public function remove($name)
    {
        $this->session->remove($name);
    }
}