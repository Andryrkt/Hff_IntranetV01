<?php


namespace App\Controller\admin;


use App\Controller\Controller;
use App\Entity\admin\dit\CategorieAteApp;
use App\Form\admin\dit\CategorieAteAppType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieAteAppController extends Controller
{
    /**
     * @Route("/admin/categorieAte", name="categorieAte_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(CategorieAteApp::class)->findBy([], ['id' => 'DESC']);

        //  dd($data[0]->getDerniereId());
        return $this->render('admin/categorieAte/list.html.twig', [

            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/categorieAte/new", name="categorieAte_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(CategorieAteAppType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorieAte = $form->getData();

            $this->getEntityManager()->persist($categorieAte);
            $this->getEntityManager()->flush();
            $this->redirectToRoute("categorieAte_index");
        }

        return $this->render(
            'admin/categorieAte/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}
