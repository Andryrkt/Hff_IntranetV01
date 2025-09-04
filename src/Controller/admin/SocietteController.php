<?php


namespace App\Controller\admin;


use App\Controller\Controller;
use App\Entity\admin\Societte;
use App\Form\admin\SocietteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class SocietteController extends Controller
{
    /**
     * @Route("/admin/societte", name="societte_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

    $data = $this->getEntityManager()->getRepository(Societte::class)->findBy([], ['id'=>'DESC']);


    return $this->render('admin/societte/list.html.twig', 
    [
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/societte/new", name="societte_new")
         */
        public function new(Request $request)
        {
            //verification si user connecter
        $this->verifierSessionUtilisateur();

            $form = $this->getFormFactory()->createBuilder(SocietteType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $societte= $form->getData();
                

                $this->getEntityManager()->persist($societte);
                $this->getEntityManager()->flush();

                $this->redirectToRoute("societte_index");
            }
    
            return $this->render('admin/societte/new.html.twig', 
            [
                'form' => $form->createView()
            ]);
        }


                /**
     * @Route("/admin/societte/edit/{id}", name="societte_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(Societte::class)->find($id);
        
        $form = $this->getFormFactory()->createBuilder(SocietteType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("societte_index");
            
        }

        return $this->render('admin/societte/edit.html.twig', 
        [
            'form' => $form->createView(),
        ]);

    }

    /**
    * @Route("/admin/societte/delete/{id}", name="societte_delete")
    *
    * @return void
    */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $societte = $this->getEntityManager()->getRepository(Societte::class)->find($id);

        if ($societte) {
            $typeReparations = $societte->getTypeReparations();
            foreach ($typeReparations as $typeReparation) {
                $societte->removeTypeReparation($typeReparation);
                $this->getEntityManager()->persist($typeReparation); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $societte->getTypeReparations()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            $this->getEntityManager()->flush();
        
                $this->getEntityManager()->remove($societte);
                $this->getEntityManager()->flush();
        }
        
        $this->redirectToRoute("societte_index");
    }
}