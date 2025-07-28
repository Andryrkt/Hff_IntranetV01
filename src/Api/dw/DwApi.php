<?php

namespace App\Api\dw;

use App\Controller\Controller;
use App\Model\dw\DossierInterventionAtelierModel;
use Symfony\Component\Routing\Annotation\Route;

class DwApi extends Controller
{
    /**
     * @Route("/dw-fetch/{numDit}", name="fetch_dw")
     *
     * Cette fonction permet d'envoier les donners Ordre de réparation, facture, rapport d'intervention, commande
     * qui correspond à un demande d'intervention
     */
    public function dwfetch($numDit)
    {
        $dwModel = new DossierInterventionAtelierModel();

        // Récupérer les données de la demande d'intervention et de l'ordre de réparation
        $dwDit = $dwModel->findDwDit($numDit) ?? [];
        foreach ($dwDit as $key => $value) {
            $dwDit[$key]['nomDoc'] = 'Demande d\'intervention';
        }
        // dump($dwDit);
        $dwOr = $dwModel->findDwOr($numDit) ?? [];
        // dump($dwOr);
        $dwfac = [];
        $dwRi = [];
        $dwCde = [];

        // Si un ordre de réparation est trouvé, récupérer les autres données liées
        if (! empty($dwOr)) {
            $dwfac = $dwModel->findDwFac($dwOr[0]['numero_doc']) ?? [];
            $dwRi = $dwModel->findDwRi($dwOr[0]['numero_doc']) ?? [];
            $dwCde = $dwModel->findDwCde($dwOr[0]['numero_doc']) ?? [];

            foreach ($dwOr as $key => $value) {
                $dwOr[$key]['nomDoc'] = 'Ordre de réparation';
            }

            foreach ($dwfac as $key => $value) {
                $dwfac[$key]['nomDoc'] = 'Facture';
            }

            foreach ($dwRi as $key => $value) {
                $dwRi[$key]['nomDoc'] = 'Rapport d\'intervention';
            }
            foreach ($dwCde as $key => $value) {
                $dwCde[$key]['nomDoc'] = 'Commande';
            }
        }
        // dump($dwfac);
        // dump($dwRi);
        // dump($dwCde);

        // Fusionner toutes les données dans un tableau associatif
        $data = array_merge($dwDit, $dwOr, $dwfac, $dwRi, $dwCde);
        // dd($data);
        header("Content-type:application/json");

        echo json_encode($data);
    }

    /**
     *@Route("/dw-chemin-fetch/{numDoc}/{nomDoc}/{numVersion}", name="fetch_dw_chemin")
     */
    public function dwCheminFichier($numDoc, $nomDoc, $numVersion)
    {
        $dwModel = new DossierInterventionAtelierModel();

        switch ($nomDoc) {
            case 'Demande d\'intervention':
                $dw = $dwModel->findCheminDit($numDoc) ?? [];
                break;
            case 'Ordre de réparation':
                $dw = $dwModel->findCheminOr($numDoc, $numVersion) ?? [];
                break;
            case 'Facture':
                $dw = $dwModel->findCheminFac($numDoc) ?? [];
                break;
            case 'Rapport d\'intervention':
                $dw = $dwModel->findCheminRi($numDoc) ?? [];
                break;
            default:
                $dw = $dwModel->findCheminCde($numDoc) ?? [];
                break;
        }

        header("Content-type:application/json");

        echo json_encode(['chemin' => $dw[0]]);
    }
}
