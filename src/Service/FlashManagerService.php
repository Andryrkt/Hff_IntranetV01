<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class FlashManagerService
{
    private $session;

    public function __construct()
    {
        $this->session = new Session(new NativeSessionStorage());
        $this->session->start();
    }

    public function addFlash($type, $message, $duration = 300) // 300 secondes = 5 minutes
    {
        $flash = [
            'message' => $message,
            'expire'  => time() + $duration
        ];
        $this->session->getFlashBag()->add($type, $flash);
    }

    public function getFlashes($type)
    {
        $allFlashes = $this->session->getFlashBag()->get($type);
        $validFlashes = [];

        foreach ($allFlashes as $flash) {
            if ($flash['expire'] > time()) {
                $validFlashes[] = $flash['message'];
            }
        }

        return $validFlashes;
    }
}

