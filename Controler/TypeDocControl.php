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
        include 'Views/Principe.php';
        include('Views/TypeDoc/TypeDoc_Form.php');
    }
}
