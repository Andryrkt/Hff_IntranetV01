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

        // Récupération initiale : Demande d'intervention
        $dwDit = $this->fetchAndLabel($dwModel, 'findDwDit', $numDit, "Demande d'intervention");

        // Ordre de réparation et documents liés
        $dwOr = $this->fetchAndLabel($dwModel, 'findDwOr', $numDit, "Ordre de réparation");
        $dwFac = $dwRi = $dwCde = [];

        // Si un ordre de réparation est trouvé, récupérer les autres données liées
        if (!empty($dwOr)) {
            $numeroDocOr = $dwOr[0]['numero_doc'];
            $dwFac = $this->fetchAndLabel($dwModel, 'findDwFac', $numeroDocOr, "Facture");
            $dwRi  = $this->fetchAndLabel($dwModel, 'findDwRi',  $numeroDocOr, "Rapport d'intervention");
            $dwCde = $this->fetchAndLabel($dwModel, 'findDwCde', $numeroDocOr, "Commande");
        }

        // Documents liés à la demande d'intervention
        $dwBc  = !empty($dwDit) ? $this->fetchAndLabel($dwModel, 'findDwBc',  $dwDit[0]['numero_doc'], "Bon de Commande Client") : [];
        $dwDev = !empty($dwDit) ? $this->fetchAndLabel($dwModel, 'findDwDev', $dwDit[0]['numero_doc'], "Devis") : [];

        // Fusionner toutes les données
        $data = array_merge($dwDit, $dwOr, $dwFac, $dwRi, $dwCde, $dwBc, $dwDev);

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

    /**
     * Méthode utilitaire pour récupérer et étiqueter des documents
     */
    private function fetchAndLabel($model, string $method, $param, string $label): array
    {
        $items = $model->$method($param) ?? [];
        foreach ($items as &$item) {
            $item['nomDoc'] = $label;
        }
        return $items;
    }
}
