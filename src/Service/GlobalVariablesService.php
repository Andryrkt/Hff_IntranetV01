<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GlobalVariablesService
{
    private $session;
    private $profilModel;
    private $accessFilePath;

    public function __construct(SessionInterface $session, $profilModel, string $accessFilePath)
    {
        $this->session = $session;
        $this->profilModel = $profilModel;
        $this->accessFilePath = $accessFilePath;
    }

    public function getUserConnect()
    {
        return $this->session->get('user');
    }

    public function getInfoUserCours()
    {
        $userConnect = $this->getUserConnect();
        return $this->profilModel->getInfoAllUserCours($userConnect);
    }

    public function getBoolean()
    {
        $user = $this->getUserConnect();
        $text = file_get_contents($this->accessFilePath);
        return strpos($text, $user) !== false;
    }
}
