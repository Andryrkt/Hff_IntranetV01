<?php
class ProfilControl
{
    private $ProfilModel;
    public function __construct(ProfilModel $ProfilModel)
    {
        $this->ProfilModel = $ProfilModel;
    }
    public function showInfoProfilUser()
    {
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
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
    public function showinfoAllUsercours()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
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
    public function showPageAcceuil(){
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
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
