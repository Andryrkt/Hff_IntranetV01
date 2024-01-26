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
        include 'Views/TypeDoc/StatutForm.php';
    }
}
