<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\historisation\pageConsultation\PageHff;

/**
 * @Route("/admin/page-hff")
 */
class PageHffController extends Controller
{
    /**
     * @Route("/", name="page_hff_index")
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(PageHff::class)->findAll();

        return $this->render('admin/page-hff/list.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/new", name="page_hff_new")
     */
    public function new(Request $request)
    {
        return $this->render('admin/page-hff/new.html.twig');
    }

    /**
     * @Route("/edit/{id}", name="page_hff_update")
     */
    public function edit(Request $request, $id)
    {
        return $this->render('admin/page-hff/edit.html.twig');
    }

    /**
     * @Route("/delete/{id}", name="page_hff_delete")
     */
    public function delete($id)
    {
        return $this->render('admin/page-hff/delete.html.twig');
    }
}
