<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\dw\DwDemandeIntervention;
use App\Form\dw\DossierInterventionAtelierSearchType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\DossierInterventionAtelierModel;
use DateTime;

class DossierInterventionAtelierController extends Controller
{
    /**
     * @Route("/dit-dossier-intervention-atelier", name="dit_dossier_intervention_atelier")
     *
     * @return void
     */
    public function dossierInterventionAtelier()
    {
        
        $form = self::$validator->createBuilder(DossierInterventionAtelierSearchType::class)->getForm();

        $dwModel = new DossierInterventionAtelierModel();

        $dwDits = $dwModel->findAllDwDit();
        
        $dwfac = [];
        $dwRi = [];
        $dwCde = [];
        
        for ($i = 0; $i < count($dwDits); $i++) {
            // Récupérer les données de la demande d'intervention et de l'ordre de réparation
            $dwDit = $dwModel->findDwDit($dwDits[$i]['numero_dit_intervention']) ?? [];
            $dwOr = $dwModel->findDwOr($dwDits[$i]['numero_dit_intervention']) ?? [];
            
            // Si un ordre de réparation est trouvé, récupérer les autres données liées
            if (!empty($dwOr)) {
                $dwfac = $dwModel->findDwFac($dwOr[0]['numero_doc']) ?? [];
                $dwRi = $dwModel->findDwRi($dwOr[0]['numero_doc']) ?? [];
                $dwCde = $dwModel->findDwCde($dwOr[0]['numero_doc']) ?? [];
            }
        
            // Fusionner toutes les données dans un tableau associatif
            $data = array_merge($dwDit, $dwOr, $dwfac, $dwRi, $dwCde);
        
            // Ajouter le nombre de documents à l'élément actuel de $dwDits
            $dwDits[$i]['nbDoc'] = count($data) ;
        }
        

        //dd($dwDits[0]->getOrdreDeReparation()->get);

        //Facture
        // $facture = $dwDits[0]->getOrdreDeReparation()->getFactures();

        // foreach ($facture as $value) {
        //     dump($value);
        // }

        $date = new DateTime();

        self::$twig->display('dw/dossierInterventionAtelier.html.twig', [
            'form' => $form->createView(),
            'dwDits' => $dwDits,
            'date' => $date
        ]);
    }
}