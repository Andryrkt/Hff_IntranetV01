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
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $UserConnect = $_SESSION['user'];
        include 'Views/Principe.php';
        $ListAgenceAuto = $this->AgenceServAutoriserModel->getListAgenceServicetoUserAll();
        include 'Views/TypeDoc/AgenceAutoriser.php';
    }
    public function deleteAgenceAuto(){
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if (isset($_GET['Id'])){
            $id = $_GET['Id'];
            $Delete = $this->AgenceServAutoriserModel->deleteAgenceAuto($id);
            header('Location:/Hffintranet/index.php?action=AgenceAutoriser');
        }
    }
}