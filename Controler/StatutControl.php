<?php

class StatutControl
{
    private $StatutModel;
    public function __construct(StatutModel $StatutModel)
    {
        $this->StatutModel = $StatutModel;
    }

    public function ShowFormStatut()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $UserConnect = $_SESSION['user'];
        include 'Views/Principe.php';
        $ListStatut = $this->StatutModel->getListStatut();
        include 'Views/TypeDoc/StatutForm.php';
    }
    public function MoveStatut()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']  === 'POST') {
            $CoedApp = $_POST['CodeApp'];
            $CodeStatut = $_POST['CodeStatut'];
            $Descript = $_POST['descStatut'];
            $DateSyst = $this->StatutModel->getDatesystem();
            $insertStatut = $this->StatutModel->InsertStatut( $CoedApp, $CodeStatut,$Descript,$DateSyst);
            header('Location:/Hff_IntranetV01/index.php?action=Statut');
        }
    }
}
