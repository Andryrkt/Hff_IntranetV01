<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\Agence;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\utilisateur\ContactAgenceAte;
use App\Form\admin\utilisateur\ContactAgenceAteType;
use Symfony\Component\HttpFoundation\Request;

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

        self::$twig->display('admin/contactAgenceAte/index.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/contact-agence-ate-new", name="contact_agence_ate_new")
     *
     * @return void
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $contactAgenceAte = new ContactAgenceAte();
        $form = self::$validator->createBuilder(ContactAgenceAteType::class)->getForm();

        $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $data = $form->getData();
                
                $contactAgenceAte 
                    ->setAgenceString($data->getAgence()->getCodeAgence())
                    ->setMatriculeString($data->getMatricule()->getMatricule())
                    ->setNomString($data->getNom()->getPersonnels()->getNom())
                    ->setEmailString($data->getEmail()->getMail())
                    ->setTelephone($data->getTelephone())
                    ->setAtelier($data->getAtelier())
                    ->setPrenom($data->getPrenom())
                ;

                self::$em->persist($contactAgenceAte);
                self::$em->flush();
                $this->redirectToRoute("contact_agence_ate_index");
            }

        self::$twig->display('admin/contactAgenceAte/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/contact-agence-ate-edit/{id}", name="contact_agence_ate_edit")
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $contactAgenceAte = self::$em->getRepository(ContactAgenceAte::class)->find($id);


        $agence = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $contactAgenceAte->getAgenceString()]);
        $user = self::$em->getRepository(User::class)->findOneBy(['matricule' => $contactAgenceAte->getMatriculeString()]);

        $contactAgenceAte ->setAgence($agence)
        ->setMatricule($user)
        ->setEmail($user) 
        ->setNom($user)
        ;
        $form = self::$validator->createBuilder(ContactAgenceAteType::class, $contactAgenceAte)->getForm();

        $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $data = $form->getData();
                $contactAgenceAte 
                    ->setAgenceString($data->getAgence()->getCodeAgence())
                    ->setMatriculeString($data->getMatricule()->getMatricule())
                    ->setNomString($data->getNom()->getPersonnels()->getNom())
                    ->setEmailString($data->getEmail()->getMail())
                    ->setTelephone($data->getTelephone())
                    ->setAtelier($data->getAtelier())
                    ->setPrenom($data->getPrenom())
                ;

                self::$em->flush();
                $this->redirectToRoute("contact_agence_ate_index");
            }

        self::$twig->display('admin/contactAgenceAte/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/contact-agence-ate-delete/{id}", name="contact_agence_ate_delete")
     */
    public function delete($id)
    {
         //verification si user connecter
        $this->verifierSessionUtilisateur();

        $contactAgenceAte = self::$em->getRepository(ContactAgenceAte::class)->find($id);

        self::$em->remove($contactAgenceAte);
            self::$em->flush();
            $this->redirectToRoute("contact_agence_ate_index");
    }
}