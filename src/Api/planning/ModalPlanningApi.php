<?php

namespace App\Api\planning;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Model\planning\ModalPlanningModel;
use Symfony\Component\Routing\Annotation\Route;

class ModalPlanningApi extends Controller
{
    private ModalPlanningModel $planningModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningModel = new ModalPlanningModel();
    }


    /**
     * @Route("/api/detail-modal/{numOr}", name="api_liste_detailModal")
     *
     * @return void
     */
    public function detailModal($numOr)
    {
        // Récupération de la liste de détails
        $details = $this->fusionData($numOr);
        if (strpos($numOr, '-') !== false) {
            $groupedDetails = $details; //recupe les informations avec intervention preci
        } elseif (strpos($numOr, '-') === false) {
            $groupedDetails = $this->regroupeParIntervention($details); // recupe tous les interventions en les regroupants dans des tableaus par intervenant
        } else {
            $groupedDetails = [];
        }

        header("Content-type:application/json");

        echo json_encode($groupedDetails);
    }

    /**
     * @Route("/api/technicien-intervenant/{numOr}/{numItv}", name="api_technicien_intervenant")
     */
    public function TechnicienIntervenant($numOr, $numItv)
    {
        $matriculeNom = $this->planningModel->recupTechnicientIntervenant($numOr, $numItv);

        if (empty($matriculeNom)) {
            $matriculeNom = $this->planningModel->recupTechnicien2($numOr, $numItv);
        }

        header("Content-type:application/json");

        echo json_encode($matriculeNom);
    }


    private function fusionData(string $numOr): array
    {
        $criteria = $this->getSessionService()->get('planning_search_criteria', []);

        $details = $this->planningModel->recuperationDetailPieceInformix($numOr, $criteria);

        $ditRepositoryConditionner = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $numOr)[0]]);
        $numDit = $ditRepositoryConditionner->getNumeroDemandeIntervention();
        $migration = $ditRepositoryConditionner->getMigration();

        $recupPariel = [];
        $recupGot = [];

        foreach ($details as $i => $detail) {
            // Déterminer la clé à utiliser (numerocdecis ou numerocmd)
            $numeroKey = ($numOr[0] == '5') ? 'numerocdecis' : 'numerocmd';
            $numero = $detail[$numeroKey] ?? '';

            // Initialiser les données si le numéro est vide ou invalide
            if (empty($numero) || $numero == "0") {
                $recupGot = [];
            } else {
                $etaMag = $this->planningModel->recuperationEtaMag($numero, $detail['ref'], $detail['cst']);
                $recupPariel[] = $this->planningModel->recuperationPartiel($numero, $detail['ref']);
                $recupGot['ord'] = $this->planningModel->recuperationinfodGcot($numero);
            }

            // Ajouter les données récupérées au détail actuel
            $details[$i]['Eta_ivato'] = !empty($etaMag[0]['Eta_ivato']) ? $etaMag[0]['Eta_ivato'] : "";
            $details[$i]['Eta_magasin'] = !empty($etaMag[0]['Eta_magasin']) ? $etaMag[0]['Eta_magasin'] : "";

            $recupParielCurrent = $recupPariel[$i] ?? null;
            $details[$i]['qteSlode'] = $recupParielCurrent['0']['solde'] ?? 0;
            $details[$i]['qte'] = $recupParielCurrent['0']['qte'] ?? 0;

            $details[$i]['Ord'] = $recupGot['ord']['Ord'] ?? "";

            // Ajouter les informations supplémentaires
            $details[$i]['numDit'] = $numDit;
            $details[$i]['migration'] = $migration;
        }
        return $details;
    }

    private function regroupeParIntervention(array $details): array
    {
        $groupedDetails = [];

        foreach ($details as $detail) {
            $intvKey = $detail['intv']; // La valeur de 'intv' utilisée comme clé
            if (!isset($groupedDetails[$intvKey])) {
                $groupedDetails[$intvKey] = [];
            }
            $groupedDetails[$intvKey][] = $detail; // Ajouter l'élément au groupe correspondant
        }
        return $groupedDetails;
    }
}
