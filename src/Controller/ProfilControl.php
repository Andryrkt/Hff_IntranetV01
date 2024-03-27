<?php

namespace App\Controller;

use Exception;
use App\Model\ProfilModel;

class ProfilControl
{
    private $ProfilModel;

    public function __construct()
    {
        $this->ProfilModel = new ProfilModel();
    }

    public function showInfoProfilUser()
    {
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {
            $UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
            $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);
            include 'Views/Principe.php';
            include 'Views/Acceuil.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showinfoAllUsercours()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {
            $UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
            include 'Views/Principe.php';
            $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);
            include 'Views/Propos_page.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showPageAcceuil()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {
            $UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
            include 'Views/Principe.php';
            include 'Views/Acceuil.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
