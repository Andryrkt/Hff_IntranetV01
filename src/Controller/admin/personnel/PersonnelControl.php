<?php


namespace App\Controller\admin\personnel;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
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
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder()
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
        ->add('submit', SubmitType::class, [
            'label' => 'Submit'
        ])
        ->getForm();

        $form->handleRequest($request);

         // Vérifier si le formulaire est soumis et valide
         if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des données du formulaire
           dd( $form->getData());
          
        }

       return $this->render('test.html.twig', [
            'form' => $form->createView()
        ]);
}


    public function showPersonnelForm()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo 'okey';
        } else {
            $codeSage = $this->transformEnSeulTableau($this->Person->recupAgenceServiceSage());
            $codeIrium = $this->transformEnSeulTableau($this->Person->recupAgenceServiceIrium());
            $serviceIrium = $this->transformEnSeulTableau($this->Person->recupServiceIrium());


            return $this->render(
                'admin/personnel/addPersonnel.html.twig',
                [
                    'codeSage' => $codeSage,
                    'codeIrium' => $codeIrium,
                    'serviceIrium' => $serviceIrium
                ]
            );
        }
    }

    public function showListePersonnel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $infoPersonnel = $this->Person->recupInfoPersonnel();

        // var_dump($infoPersonnel);
        // die();



        return $this->render(
            'admin/personnel/listPersonnel.html.twig',
            [
                'infoPersonnel' => $infoPersonnel
            ]
        );
    }

    public function updatePersonnel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $codeSage = $this->transformEnSeulTableau($this->Person->recupAgenceServiceSage());
        $codeIrium = $this->transformEnSeulTableau($this->Person->recupAgenceServiceIrium());


        $infoPersonnelId = $this->Person->recupInfoPersonnelMatricule($_GET['matricule']);
        return $this->render(
            'admin/personnel/addPersonnel.html.twig',
            [
                'codeSage' => $codeSage,
                'codeIrium' => $codeIrium,
                'infoPersonnelId' => $infoPersonnelId
            ]
        );
    }
}
