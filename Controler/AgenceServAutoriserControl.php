<?php
class AgenceServAutoriserControl{
    private $AgenceServAutoriserModel;
    public function __construct(AgenceServAutoriserModel $AgenceServAutoriserModel)
    {
        $this->AgenceServAutoriserModel=$AgenceServAutoriserModel;
    }

    public function showListAgenceService(){
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $UserConnect = $_SESSION['user'];
        include 'Views/Principe.php';
        $ListAgenceAuto = $this->AgenceServAutoriserModel->getListAgenceServicetoUserAll();
        include 'Views/TypeDoc/AgenceAutoriser.php';
    }
}