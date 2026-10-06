<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionManagerService
{
    private $session;

    public function __construct()
    {
        // Vérifier si une session est déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->session = new Session(new NativeSessionStorage());
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
