<?php

namespace App\Controller\admin\user;

use App\Controller\Controller;
use App\Entity\ProfilUserEntity;
use App\Form\ProfilUserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class ProfilUserController extends Controller
{

    private $nomTable = 'Profil_User';
    
    /**
     * Undocumented function
     *  @Route("/admin/user", name="user_index")
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $form = self::$validator->createBuilder(ProfilUserType::class)->getForm();

        $form->handleRequest($request);

         // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $profilUser = $form->getData();
            $this->profilUser->insertData($this->nomTable, $profilUser);
            
        }

        self::$twig->display('admin/user/profilUser.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function list()
    {
       
    }
}