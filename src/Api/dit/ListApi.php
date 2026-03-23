<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Model\dit\DitListModel;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Entity\dit\DitFactureSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;

class ListApi extends Controller
{
    /**
     * @Route("/api/command-modal/{numOr}", name="api_liste_commandModal")
     *
     * @return void
     */
    public function commandModal($numOr)
    {
        //RECUPERATION DE LISTE COMMANDE 
        if ($numOr === '') {
            $commandes = [];
        } else {
            $commandes = $this->ditModel->RecupereCommandeOr($numOr);
        }

        header("Content-type:application/json");

        echo json_encode($commandes);
    }

    /**
     * @Route("/section-affectee-modal-fetch/{id}", name="section_affectee_modal")
     *
     * @return void
     */
    public function sectionAffecteeModal($id)
    {
        $motsASupprimer = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];

        // Récupération des données
        $sectionSupportAffectee = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findSectionSupport($id);

        // Parcourir chaque élément du tableau et supprimer les mots
        foreach ($sectionSupportAffectee as &$value) {
            foreach ($value as &$texte) {
                // Vérification si c'est bien une chaîne de caractères avant d'effectuer le remplacement
                if (is_string($texte)) {
                    $texte = str_replace($motsASupprimer, '', $texte);
                    $texte = trim($texte); // Supprimer les espaces en trop après remplacement
                }
            }
        }

        header("Content-type:application/json");

        echo json_encode($sectionSupportAffectee);
    }

    /** 
     * RECUPERATION numero intervention, numero facture et statut du facture
     * @Route("/facturation-fetch/{numOr}", name="facturation_fetch") 
     * */
    public function facturation($numOr)
    {
        $ditListeModel = new DitListModel();
        $facture = $this->getEntityManager()->getRepository(DitFactureSoumisAValidation::class)->findNumItvFacStatut($numOr);
        $itvNumFac = $ditListeModel->recupItvNumFac($numOr);

        $result = [];
        foreach ($itvNumFac as $value) {
            $found = false;
            foreach ($facture as $item) {
                if ($item['numeroItv'] == $value['itv']) {
                    $result[] = $item;
                    $found = true;
                    break;
                }
            }


            if (!$found) {
                $result[] = [
                    "numeroItv" => $value['itv'],
                    "numeroFact" => $value['numerofac'] ? $value['numerofac'] : "-",
                    "statut" => "-"
                ];
            }
        }


        header("Content-type:application/json");
        echo json_encode($result);
    }

    /** 
     * RECUPERATION numero intervention, numero facture et statut du facture
     * @Route("/ri-fetch/{numOr}", name="ri_fetch") 
     * */
    public function ri($numOr)
    {
        if (empty($numOr)) {
            header("Content-type:application/json");
            echo json_encode([]);
            return;
        }

        $ditListeModel = new DitListModel();
        $ri = $ditListeModel->recupItvComment($numOr);
        $riSoumis = $this->getEntityManager()->getRepository(DitRiSoumisAValidation::class)->findNumItv($numOr);

        foreach ($ri as &$value) {
            $estRiSoumis = in_array($value['numeroitv'], $riSoumis);
            $value['riSoumis'] = $estRiSoumis;
        }
        unset($value); // Libère la référence

        header("Content-type:application/json");
        echo json_encode($ri);
    }

    /** 
     * 
     * @Route("/niveau-urgence-fetch/{numDit}", name="niveau_urgnece_fetch") 
     * */
    public function niveauUrgence($numDit)
    {
        /**
         *  A FAIRE, EN ATTENTE DU TABLEAU
         */
        $niveauUrgence = [];

        header("Content-type:application/json");
        echo json_encode($niveauUrgence);
    }
}
