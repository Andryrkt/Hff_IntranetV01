<?php

namespace App\Controller\admin\user;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UserController extends Controller
{
    /**
     * Undocumented function
     *  @Route("/admin/user", name="user_index")
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $form = self::$validator->createBuilder()
        ->add('Utilisateur', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 4)),
            ),
        ))
        ->add('Profil', ChoiceType::class, array(
            'choices' => array('utilisateur' => 'utilisateur', 'validateur' => 'validateur'),
        ))
        ->add('DOM', CheckboxType::class, array(
            'required' => false,
        ))
        ->add('BDM', CheckboxType::class, array(
            'required' => false,
        ))
        ->add('Matricule', NumberType::class)
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

        self::$twig->display('test.html.twig', [
            'form' => $form->createView()
        ]);
    }
}