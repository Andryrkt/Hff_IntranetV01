<?php

namespace App\Service\autres;

use App\Entity\dit\DitOrsSoumisAValidation;

class MontantPdfService
{
    public function montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax)
    {
        $recapAvantApres =$this->recuperationAvantApres($OrSoumisAvantMax, $OrSoumisAvant);
                return [
                    'avantApres' => $this->affectationStatut($recapAvantApres)['recapAvantApres'],
                    'totalAvantApres' => $this->calculeSommeAvantApres($recapAvantApres),
                    'recapOr' => $this->recapitulationOr($orSoumisValidataion),
                    'totalRecapOr' => $this->calculeSommeMontant($orSoumisValidataion),
                    'nombreStatutNouvEtSupp' => $this->affectationStatut($recapAvantApres)['nombreStatutNouvEtSupp']
                ];
    }

    private function calculeSommeMontant($orSoumisValidataion)
    {
        $totalRecapOr = [
            'total' => 'TOTAL',
            'montant_itv' => 0,
            'montant_piece' => 0,
            'montant_mo' => 0,
            'montant_achats_locaux' => 0,
            'montant_lubrifiants' => 0,
            'montant_frais_divers' => 0,
        ];
        foreach ($orSoumisValidataion as $orSoumis) {
            // Faire la somme des montants et les stocker dans le tableau
            $totalRecapOr['montant_itv'] += $orSoumis->getMontantItv();
            $totalRecapOr['montant_piece'] += $orSoumis->getMontantPiece();
            $totalRecapOr['montant_mo'] += $orSoumis->getMontantMo();
            $totalRecapOr['montant_achats_locaux'] += $orSoumis->getMontantAchatLocaux();
            $totalRecapOr['montant_lubrifiants'] += $orSoumis->getMontantLubrifiants();
            $totalRecapOr['montant_frais_divers'] += $orSoumis->getMontantFraisDivers();
        }

        return $totalRecapOr;
    }

    private function recuperationAvantApres($OrSoumisAvantMax, $OrSoumisAvant)
    {
    
        if(!empty($OrSoumisAvantMax)){
            // Trouver les objets manquants par numero d'intervention dans chaque tableau
            $manquantDansOrSoumisAvantMax = $this->objetsManquantsParNumero($OrSoumisAvantMax, $OrSoumisAvant);
            $manquantDansOrSoumisAvant = $this->objetsManquantsParNumero($OrSoumisAvant, $OrSoumisAvantMax);

            // Ajouter les objets manquants dans chaque tableau
            $OrSoumisAvantMax = array_merge($OrSoumisAvantMax, $manquantDansOrSoumisAvantMax);
            $OrSoumisAvant = array_merge($OrSoumisAvant, $manquantDansOrSoumisAvant);

            // Trier les tableaux par numero d'intervention
            $this->trierTableauParNumero($OrSoumisAvantMax);
            $this->trierTableauParNumero($OrSoumisAvant);
        }
        

        $recapAvantApres = [];

        for ($i = 0; $i < count($OrSoumisAvant); $i++) {
            
                $itv = $OrSoumisAvant[$i]->getNumeroItv();
                $libelleItv = $OrSoumisAvant[$i]->getLibellelItv();
                $nbLigAp = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getNombreLigneItv() : 0;
                $mttTotalAp = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantItv() : 0;
                $nbLigAv = isset($OrSoumisAvantMax[$i]) ? $OrSoumisAvantMax[$i]->getNombreLigneItv() : 0;
                $mttTotalAv = isset($OrSoumisAvantMax[$i]) ? $OrSoumisAvantMax[$i]->getMontantItv() : 0;

            $recapAvantApres[] = [
                'itv' => $itv,
                'libelleItv' => $libelleItv,
                'nbLigAv' => $nbLigAv,
                'nbLigAp' => $nbLigAp,
                'mttTotalAv' => $mttTotalAv,
                'mttTotalAp' => $mttTotalAp,
            ];
        }

        return $recapAvantApres;
    }

 // Fonction pour trouver les numéros d'intervention manquants
 private function objetsManquantsParNumero($tableauA, $tableauB) {
    $manquants = [];
    foreach ($tableauB as $objetB) {
        $trouve = false;
        foreach ($tableauA as $objetA) {
            if ($objetA->estEgalParNumero($objetB)) {
                $trouve = true;
                break;
            }
        }
        if (!$trouve) {
            $numeroItvExist = $objetB->getNumeroItv() === 0 ? $objetA->getNumeroItv() : $objetB->getNumeroItv();
            // Créer un nouvel objet avec uniquement le numero et les autres propriétés à null ou 0
            $nouvelObjet = new DitOrsSoumisAValidation();
            $nouvelObjet->setNumeroItv($numeroItvExist);
            $manquants[] = $nouvelObjet;
        }
    }
    return $manquants;
}

// Fonction pour trier les tableaux par numero d'intervention
private function trierTableauParNumero(&$tableau) {
    usort($tableau, function($a, $b) {
        return strcmp($a->getNumeroItv(), $b->getNumeroItv());
    });
}

    private function affectationStatut($recapAvantApres)
    {
        $nombreStatutNouvEtSupp = [
            'nbrNouv' => 0,
            'nbrSupp' => 0,
            'nbrModif' => 0,
            'mttModif' => 0
        ];
//dump($recapAvantApres);
        foreach ($recapAvantApres as &$value) { // Référence les éléments pour les modifier directement
            if ($value['nbLigAv'] === $value['nbLigAp'] && $value['mttTotalAv'] === $value['mttTotalAp']) {
                $value['statut'] = '';
            } elseif ($value['nbLigAv'] !== 0 && $value['mttTotalAv'] !== 0.0 && $value['nbLigAp'] === 0 && $value['mttTotalAp'] === 0.0) {
               //dump($value);
                $value['statut'] = 'Supp';
                $nombreStatutNouvEtSupp['nbrSupp']++;
            } elseif (($value['nbLigAv'] === 0 || $value['nbLigAv'] === '' ) && $value['mttTotalAv'] === 0.0 || $value['mttTotalAv'] === 0) {
                $value['statut'] = 'Nouv';
                $nombreStatutNouvEtSupp['nbrNouv']++;
            } elseif (($value['nbLigAv'] !== $value['nbLigAp'] || $value['mttTotalAv'] !== $value['mttTotalAp']) && ($value['nbLigAv'] !== 0 || $value['nbLigAv'] !== '' || $value['nbLigAp'] !== 0)) {
                //dump($value);
                $value['statut'] = 'Modif';
                $nombreStatutNouvEtSupp['nbrModif']++;
                $nombreStatutNouvEtSupp['mttModif'] = $nombreStatutNouvEtSupp['mttModif'] + ($value['mttTotalAp'] - $value['mttTotalAv']);
            }
        }
//dd($recapAvantApres);
        // Retourner le tableau modifié et les statistiques de nouveaux et supprimés
        return [
            'recapAvantApres' => $recapAvantApres,
            'nombreStatutNouvEtSupp' => $nombreStatutNouvEtSupp
        ];
    }


    private function calculeSommeAvantApres($recapAvantApres)
    {
        $totalRecepAvantApres = [
            'premierLigne' => '',
            'total' => 'TOTAL',
            'totalNbLigAv' => 0,
            'totalNbLigAp' => 0,
            'totalMttTotalAv' => 0,
            'totalMttTotalAp' => 0,
            'dernierLigne' => ''
        ];
        foreach ($recapAvantApres as  $value) {
            $totalRecepAvantApres['totalNbLigAv'] += $value['nbLigAv'] === '' ? 0 : $value['nbLigAv'];
            $totalRecepAvantApres['totalNbLigAp'] += $value['nbLigAp'];
            $totalRecepAvantApres['totalMttTotalAv'] += $value['mttTotalAv'] === '' ? 0 : $value['mttTotalAv'];
            $totalRecepAvantApres['totalMttTotalAp'] += $value['mttTotalAp'];
        }

        return $totalRecepAvantApres;
    }

    private function recapitulationOr($orSoumisValidataion)
    {
        $recapOr = [];
        foreach ($orSoumisValidataion as $orSoumis) {
            $recapOr[] = [
                'itv' => $orSoumis->getNumeroItv(),
                'mttTotal' => $orSoumis->getMontantItv(),
                'mttPieces' => $orSoumis->getMontantPiece(),
                'mttMo' => $orSoumis->getMontantMo(),
                'mttSt' => $orSoumis->getMontantAchatLocaux(),
                'mttLub' => $orSoumis->getMontantLubrifiants(),
                'mttAutres' => $orSoumis->getMontantFraisDivers(),
            ];
        }
        return $recapOr;
    }
}