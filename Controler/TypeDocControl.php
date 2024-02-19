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
            header("Location:/Hffintranet/index.php?action=Logout");
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
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']  === 'POST') {
            $TypeDoc = $_POST['TypeDoc'];
            $SousTyp = $_POST['Soutyp'];
            $DateSyst =$this->TypeDocModel->getDatesystem(); 
            $Insers = $this->TypeDocModel->Insert_TypeDoc($TypeDoc,$SousTyp,$DateSyst);  
            header('Location:/Hffintranet/index.php?action=TypeDoc');

        }
    }
    public function showListServiceAgenceAll(){
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $listAll = $this->TypeDocModel->getListeServiceAgenceAll();
        $response = "<label for='ServAg' class='label-form' id='ServAg'> Agecne-Service:</label>";
        $response .= "<select id='ServAg' class='form-select' name='ServAg' >";
        foreach ($listAll as $info) {
            $categ = $info['Agence'];
            $info = iconv('Windows-1252', 'UTF-8', $categ);

            $response .= "<option value='$info'>$info</option>";
        }
        $response .= "</select>";

        echo $response;
    }
    public function showListCodeagence(){
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
     $LibAgence = $_POST['libAgServ'];
     $codeAgence = $this->TypeDocModel->getCodeAgServ($LibAgence);
     echo $codeAgence;

    }

}
