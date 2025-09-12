<?php


namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Fonction;
use App\Form\admin\utilisateur\FonctionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FonctionController extends Controller
{
    /**
     * @Route("/admin/fonction", name="fonction_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Fonction::class)->findBy([], ['id'=>'DESC']);
    
    
        return $this->render('admin/fonction/list.html.twig', 
        [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/fonction/new", name="fonction_new")
     *
     * @return void
     */
    public function new(Request $request)
    {    
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $form = $this->getFormFactory()->createBuilder(FonctionType::class)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $fonction= $form->getData();
                
            $this->getEntityManager()->persist($fonction);
            $this->getEntityManager()->flush();
            $this->redirectToRoute("fonction_index");
        }

        return $this->render('admin/fonction/new.html.twig', 
        [
            'form' => $form->createView()
        ]);
    }

}