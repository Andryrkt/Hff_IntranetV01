<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use App\Entity\Casier;
use App\Entity\CasierValider;
use App\Entity\StatutDemande;
use App\Form\CasierSearchType;
use Symfony\Component\Routing\Annotation\Route;

class CasierListTemporaireController extends Controller
{
    use Transformation;

    /**
     * @Route("/listTemporaireCasier", name="listeTemporaire_affichageListeCasier")
     */
    public function AffichageListeCasier()
    {

        $data = self::$em->getRepository(Casier::class)->findBy([ 'idStatutDemande' => 52]);

        $form = self::$validator->createBuilder(CasierSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();

        


        self::$twig->display(
            'badm/casier/listTemporaireCasier.html.twig',
            [
                'casier' => $data,
                'form' => $form->createView(),
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
