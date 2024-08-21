<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use App\Entity\Casier;
use App\Entity\CasierValider;
use App\Entity\StatutDemande;
use App\Form\CasierSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CasierListTemporaireController extends Controller
{
    use Transformation;

    /**
     * @Route("/listTemporaireCasier", name="listeTemporaire_affichageListeCasier")
     */
    public function AffichageListeCasier(Request $request)
    {

        $form = self::$validator->createBuilder(CasierSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
           $criteria = $form->getData();
        } 

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $paginationData = self::$em->getRepository(Casier::class)->findPaginatedAndFilteredTemporaire($page, $limit, $criteria);


        if(empty($paginationData['data'])){
            $empty = true;
        }

        self::$twig->display(
            'badm/casier/listTemporaireCasier.html.twig',
            [
                'casier' => $paginationData['data'],
                'form' => $form->createView(),
                'criteria' => $criteria,
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'empty' => $empty,
            ]
        );
    }


    
    /**
     * @Route("/btnValide/{id}", name="CasierListTemporaire_btnValide")
     */
    public function tratitementBtnValide($id)
    {
       $casierValide = new CasierValider();
        //$CasierSeul = $this->caiserListTemporaire->recuperSeulCasier($id);

         $CasierSeul = self::$em->getRepository(Casier::class)->find($id);
         $CasierSeul->setIdStatutDemande(self::$em->getRepository(StatutDemande::class)->find(53));

         self::$em->persist($CasierSeul);
            self::$em->flush();

        $casierValide
        ->setCasier($CasierSeul->getCasier())
        ->setDateCreation($CasierSeul->getDateCreation())
        ->setNumeroCas($CasierSeul->getNumeroCas())
        ->setNomSessionUtilisateur($CasierSeul->getNomSessionUtilisateur())
        ->setAgenceRattacher($CasierSeul->getAgenceRattacher())
        ->setIdStatutDemande($CasierSeul->getIdStatutDemande())
        ;

        self::$em->persist($casierValide);
        self::$em->flush();
      
        
        $this->redirectToRoute("liste_affichageListeCasier");
    }
}
