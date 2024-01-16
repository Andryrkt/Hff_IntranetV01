<?php
class PersonnelControl
{
    private $PersonnelModel;

    public function __construct(PersonnelModel $PersonnelModel)
    {
        $this->PersonnelModel = $PersonnelModel;
    }

    public function showPersonnelForm()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {
            $UserConnect = $_SESSION['user'];
            include 'Views/Principe.php';
            include 'Views/Personnel/PersonnelForm.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showListePersonnel()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {
            $UserConnect = $_SESSION['user'];
            include 'Views/Principe.php';
            include 'Views/Personnel/PersonnelList.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
