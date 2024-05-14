<?php

namespace App\Controller\admin\personnel;

use App\Controller\Controller;

use Symfony\Component\Form\Forms;
use App\Controller\Traits\Transformation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class PersonnelControl extends Controller
{

    use Transformation;

    /**
     * @Route("/index")
     */
    public function index(){
        $formFactory = Forms::createFormFactoryBuilder()
    ->addExtensions([self::$validator]) // Ajoutez les extensions nécessaires
    ->getFormFactory();

$form = $formFactory->createBuilder()
    ->add('name', TextType::class)
    ->add('submit', SubmitType::class)
    ->getForm();

    $this->twig->display('form.html.twig', [
        'form' => $form->createView(),
    ]);
}


    public function showPersonnelForm()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo 'okey';
        } else {
            $codeSage = $this->transformEnSeulTableau($this->Person->recupAgenceServiceSage());
            $codeIrium = $this->transformEnSeulTableau($this->Person->recupAgenceServiceIrium());
            $serviceIrium = $this->transformEnSeulTableau($this->Person->recupServiceIrium());


            $this->twig->display(
                'admin/personnel/addPersonnel.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'codeSage' => $codeSage,
                    'codeIrium' => $codeIrium,
                    'serviceIrium' => $serviceIrium
                ]
            );
        }
    }

    public function showListePersonnel()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $infoPersonnel = $this->Person->recupInfoPersonnel();

        // var_dump($infoPersonnel);
        // die();



        $this->twig->display(
            'admin/personnel/listPersonnel.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'infoPersonnel' => $infoPersonnel
            ]
        );
    }

    public function updatePersonnel()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $codeSage = $this->transformEnSeulTableau($this->Person->recupAgenceServiceSage());
        $codeIrium = $this->transformEnSeulTableau($this->Person->recupAgenceServiceIrium());


        $infoPersonnelId = $this->Person->recupInfoPersonnelMatricule($_GET['matricule']);
        $this->twig->display(
            'admin/personnel/addPersonnel.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'codeSage' => $codeSage,
                'codeIrium' => $codeIrium,
                'infoPersonnelId' => $infoPersonnelId
            ]
        );
    }
}
