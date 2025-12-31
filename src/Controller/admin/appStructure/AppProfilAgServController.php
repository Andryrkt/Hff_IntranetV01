<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\ApplicationProfilAgenceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/appProfilAgServ") */
class AppProfilAgServController extends Controller
{
    /** @Route("/", name="app_profil_ag_serv_index") */
    public function index()
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(ApplicationProfilAgenceService::class)->findAll();
        return $this->render('admin/appProfilAgServ/list.html.twig', [
            'data' => $data,
        ]);
    }

    /** @Route("/new", name="app_profil_ag_serv_new") */
    public function new(Request $request)
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        return $this->render('admin/appProfilAgServ/new.html.twig');
    }

    /** @Route("/edit/{id}", name="app_profil_ag_serv_update") */
    public function edit(Request $request, $id)
    {
        // verifier si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();

        return $this->render('admin/appProfilAgServ/edit.html.twig');
    }
}
