<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\utilisateur\ContactAgenceAte;
use App\Form\admin\utilisateur\ContactAgenceAteType;

class ContactAgenceAteController extends Controller
{

    /**
     * @Route("/admin/contact-agence-ate-index", name="contact_agence_ate_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(ContactAgenceAte::class)->findBy([]);
        
        foreach ($data as $value) {
            $user = self::$em->getRepository(User::class)->findOneBy(['matricule' => $value->getMatricule()]);
            $value
                ->setNomPrenom($user->getPersonnels()->getNom().' '.$user->getPersonnels()->getPrenoms())
                ->setPoste($user->getPoste() === null ? '' : $user->getPoste())
                ->setEmail($user->getMail() === null ? '' : $user->getMail())
                ->setTelephone($user->getNumTel() === null ? '' : $user->getNumTel())
            ;
        }

        self::$twig->display('admin/contactAgenceAte/index.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/contact-agence-ate-new", name="contact_agence_ate_new")
     *
     * @return void
     */
    public function new()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(ContactAgenceAteType::class)->getForm();

        self::$twig->display('admin/contactAgenceAte/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}