<?php

namespace App\Controller\admin\personnel;

use App\Controller\Controller;

use Symfony\Component\Form\Forms;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;



class PersonnelControl extends Controller
{

    use Transformation;

    /**
     * @Route("/index")
     */
    public function index(){

        $form = self::$validator->createBuilder()
        ->add('firstName', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 4)),
            ),
        ))
        ->add('lastName', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 4)),
            ),
        ))
        ->add('gender', ChoiceType::class, array(
            'choices' => array('m' => 'Male', 'f' => 'Female'),
        ))
        ->add('newsletter', CheckboxType::class, array(
            'required' => false,
        ))
        ->getForm();

        self::$twig->display('test.html.twig', [
            'form' => $form->createView()
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


            self::$twig->display(
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



        self::$twig->display(
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
        self::$twig->display(
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
