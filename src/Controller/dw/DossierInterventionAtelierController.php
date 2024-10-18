<?php

namespace App\Controller\dw;

use DateTime;
use App\Controller\Controller;
use App\Entity\dw\DwDemandeIntervention;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Form\dw\DossierInterventionAtelierSearchType;

class DossierInterventionAtelierController extends Controller
{
    /**
     * @Route("/dit-dossier-intervention-atelier", name="dit_dossier_intervention_atelier")
     *
     * @return void
     */
    public function dossierInterventionAtelier(Request $request)
    {
        
        $form = self::$validator->createBuilder(DossierInterventionAtelierSearchType::class, null, [ 'method' => 'GET'])->getForm();

        $dwModel = new DossierInterventionAtelierModel();

        $criteria = [
                "idMateriel" => null,
                "typeIntervention" => "INTERNE",
                "dateDebut" => null,
                "dateFin" => null,
                "numParc" => null,
                "numSerie" => null,
                "numDit" => null,
                "numOr" => null,
                "designation" => null,
        ];

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $dwDits = $this->ajoutNbDoc($dwModel, $criteria);

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

    public function ajoutNbDoc($dwModel, $criteria)
    {
        $dwDits = $dwModel->findAllDwDit($criteria);

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

        return $dwDits;
    }
}