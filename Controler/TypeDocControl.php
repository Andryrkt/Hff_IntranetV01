<?php

class TypeDocControl
{
    private $TypeDocModel;
    public function __construct(TypeDocModel $TypeDocModel)
    {
        $this->TypeDocModel = $TypeDocModel;
    }

    public function showTypeDocForm()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $UserConnect = $_SESSION['user'];
        $Type = $this->TypeDocModel->getTypeDocAll();
        include 'Views/Principe.php';
        include('Views/TypeDoc/TypeDoc_Form.php');
    }
    public function MoveTypeDocForm()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']  === 'POST') {
            $TypeDoc = $_POST['TypeDoc'];
            $SousTyp = $_POST['Soutyp'];
            $DateSyst = getDatesystem();
            $Insers = $this->TypeDocModel->Insert_TypeDoc($TypeDoc,$SousTyp,$DateSyst);  
            header('Location:/Hff_IntranetV01/index.php?action=TypeDoc');

        }
    }
}
