<?php

// src/Service/SessionManager.php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionManagerService
{
    private $session;

    public function __construct()
    {
        $this->session = new Session(new NativeSessionStorage());
        //$this->session->start();
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
