<?php

namespace App\Service\autres;

use App\Entity\dit\DitDevisSoumisAValidation;
use App\Entity\dit\DitOrsSoumisAValidation;

class MontantPdfService
{
    public function montantpdf($devisSoumisAvant)
    {
        $recapAvantApresVte =$this->recuperationAvantApres($devisSoumisAvant['devisSoumisAvantMaxVte'], $devisSoumisAvant['devisSoumisAvantVte']);
        $recapAvantApresForfait =$this->recuperationAvantApresForfait($devisSoumisAvant['devisSoumisAvantMaxForfait'], $devisSoumisAvant['devisSoumisAvantForfait']);
        $recapAvantApresCes =$this->recuperationAvantApres($devisSoumisAvant['devisSoumisAvantMaxCes'], $devisSoumisAvant['devisSoumisAvantCes']);
        
        return [
            'avantApresForfait' => $this->affectationStatut($recapAvantApresForfait)['recapAvantApres'],
            'totalAvantApresForfait' => $this->calculeSommeAvantApres($recapAvantApresForfait),
            'nombreStatutNouvEtSuppForfait' => $this->affectationStatut($recapAvantApresForfait)['nombreStatutNouvEtSupp'],
            'avantApresVte' => $this->affectationStatut($recapAvantApresVte)['recapAvantApres'],
            'totalAvantApresVte' => $this->calculeSommeAvantApres($recapAvantApresVte),
            'nombreStatutNouvEtSuppVte' => $this->affectationStatut($recapAvantApresVte)['nombreStatutNouvEtSupp'],
            'avantApresCes' => $this->affectationStatut($recapAvantApresCes)['recapAvantApres'],
            'totalAvantApresCes' => $this->calculeSommeAvantApres($recapAvantApresCes),
            'nombreStatutNouvEtSuppCes' => $this->affectationStatut($recapAvantApresCes)['nombreStatutNouvEtSupp'],
            'recapVte' => $this->recapitulationOr($devisSoumisAvant['vteData']),
            'totalRecapVte' => $this->calculeSommeMontant($devisSoumisAvant['vteData']),
            'recapCes' => $this->recapitulationOr($devisSoumisAvant['cesData']),
            'totalRecapCes' => $this->calculeSommeMontant($devisSoumisAvant['cesData']),
        ];
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
            // dump($OrSoumisAvantMax[$i]);
            if(null !== $OrSoumisAvant[$i]->getNatureOperation() ){
                if($OrSoumisAvant[$i]->getNatureOperation() === 'VTE'){
                    $montantAvant = isset($OrSoumisAvantMax[$i])? $OrSoumisAvantMax[$i]->getMontantVente() : 0.00;
                    $montantApres = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantVente() : 0.00;
                } else {
                    $montantAvant = isset($OrSoumisAvantMax[$i])? $OrSoumisAvantMax[$i]->getMontantItv() : 0.00;
                $montantApres = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantItv() : 0.00;
                }
            } else {
                $montantAvant = isset($OrSoumisAvantMax[$i])? $OrSoumisAvantMax[$i]->getMontantItv() : 0.00;
                $montantApres = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantItv() : 0.00;
            }
            $itv = $OrSoumisAvant[$i]->getNumeroItv();
            $libelleItv = $OrSoumisAvant[$i]->getLibellelItv();
            $nbLigAp = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getNombreLigneItv() : 0;
            $mttTotalAp = $montantApres;
            $nbLigAv = isset($OrSoumisAvantMax[$i]) ? $OrSoumisAvantMax[$i]->getNombreLigneItv() : 0;
            $mttTotalAv = $montantAvant;

            $recapAvantApres[] = [
                'itv' => $itv,
                'libelleItv' => $libelleItv,
                'nbLigAv' => $nbLigAv,
                'nbLigAp' => $nbLigAp,
                'mttTotalAv' => $mttTotalAv,
                'mttTotalAp' => $mttTotalAp,
            ];
        }
// dump($recapAvantApres);
        return $recapAvantApres;
    }

    private function recuperationAvantApresForfait($OrSoumisAvantMax, $OrSoumisAvant)
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
            
            $montantAvant = isset($OrSoumisAvantMax[$i])? $OrSoumisAvantMax[$i]->getMontantForfait() : 0.00;
            $montantApres = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantForfait() : 0.00;

            $itv = $OrSoumisAvant[$i]->getNumeroItv();
            $libelleItv = $OrSoumisAvant[$i]->getLibellelItv();
            $nbLigAp = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getNombreLigneItv() : 0;
            $mttTotalAp = $montantApres;
            $nbLigAv = isset($OrSoumisAvantMax[$i]) ? $OrSoumisAvantMax[$i]->getNombreLigneItv() : 0;
            $mttTotalAv = $montantAvant;

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
            $nouvelObjet = new DitDevisSoumisAValidation();
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
            'mttModif' => 0.00
        ];


        foreach ($recapAvantApres as &$value) { // Référence les éléments pour les modifier directement

            if ($value['nbLigAv'] === $value['nbLigAp'] && $value['mttTotalAv'] === $value['mttTotalAp']) {
                $value['statut'] = '';
            } elseif ($value['nbLigAv'] !== 0 && $value['mttTotalAv'] !== 0.00 && $value['nbLigAp'] === 0 && $value['mttTotalAp'] === 0.00) {
               //dump($value);
                $value['statut'] = 'Supp';
                $nombreStatutNouvEtSupp['nbrSupp']++;
            } elseif (($value['nbLigAv'] === 0 || $value['nbLigAv'] === '' ) && $value['mttTotalAv'] === 0.00 || $value['mttTotalAv'] === 0.00) {
                $value['statut'] = 'Nouv';
                $nombreStatutNouvEtSupp['nbrNouv']++;
            } elseif (($value['nbLigAv'] !== $value['nbLigAp'] || round($value['mttTotalAv'], 2) !== round($value['mttTotalAp'], 2)) && ($value['nbLigAv'] !== 0 || $value['nbLigAv'] !== '' || $value['nbLigAp'] !== 0)) {
                //dump($value);
                $value['statut'] = 'Modif';
                $nombreStatutNouvEtSupp['nbrModif']++;
                $nombreStatutNouvEtSupp['mttModif'] = $nombreStatutNouvEtSupp['mttModif'] + ($value['mttTotalAp'] - $value['mttTotalAv']);
            }
        }
    //  die();
// dd($recapAvantApres, $nombreStatutNouvEtSupp);
        // Retourner le tableau modifié et les statistiques de nouveaux et supprimés
        return [
            'recapAvantApres' => $recapAvantApres,
            'nombreStatutNouvEtSupp' => $nombreStatutNouvEtSupp
        ];
    }

    /**
     * Methode qui permet de calculer le total de chaque colonne 
     * ceci se mettre sur le footer du tableau
     *
     * @param array $recapAvantApres
     * @return array
     */
    private function calculeSommeAvantApres(array $recapAvantApres): array
    {
        $totalRecepAvantApres = [
            'itv' => '', //pour le premier ligne
            'libelleItv' => 'TOTAL', // affichage du 'TOTAL' sur le footrer
            'nbLigAv' => 0,
            'nbLigAp' => 0,
            'mttTotalAv' => 0.00,
            'mttTotalAp' => 0.00,
            'statut' => ''
        ];
        foreach ($recapAvantApres as  $value) {
            $totalRecepAvantApres['nbLigAv'] += $value['nbLigAv'] === '' ? 0 : $value['nbLigAv'];
            $totalRecepAvantApres['nbLigAp'] += $value['nbLigAp'];
            $totalRecepAvantApres['mttTotalAv'] += $value['mttTotalAv'] === '' ? 0.00 : $value['mttTotalAv'];
            $totalRecepAvantApres['mttTotalAp'] += $value['mttTotalAp'];
        }

        return $totalRecepAvantApres;
    }


    private function recapitulationOr($orSoumisValidataion)
    {
        $recapOr = [];

        
        foreach ($orSoumisValidataion as $orSoumis) {
            if ($orSoumis->getNatureOperation() !== null) {
               if ($orSoumis->getNatureOperation() === 'VTE') {
                $recapOr[] = [
                    'itv' => $orSoumis->getNumeroItv(),
                    'mttTotal' => $orSoumis->getMontantVente(),
                    'mttPieces' => $orSoumis->getMontantPiece(),
                    'mttMo' => $orSoumis->getMontantMo(),
                    'mttSt' => $orSoumis->getMontantAchatLocaux(),
                    'mttLub' => $orSoumis->getMontantLubrifiants(),
                    'mttAutres' => $orSoumis->getMontantFraisDivers(),
                ];
               } else {
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
            } else {
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
        }
        return $recapOr;
    }

    /**
     * La methode calcule la somme de chaque colonne de tableau pour la recapitulation de l'or
     * ceci se met sur le footer du tableau 
     *
     * @param array $orSoumisValidataion
     * @return arrary
     */
    private function calculeSommeMontant(array $orSoumisValidataion): array
    {
        $totalRecapOr = [
            'itv' => 'TOTAL', // c'est pour le footer
            'mttTotal' => 0.00, // montant_itv
            'mttPieces' => 0.00,
            'mttMo' => 0.00,
            'mttSt' => 0.00,
            'mttLub' => 0.00,
            'mttAutres' => 0.00,
        ];
        foreach ($orSoumisValidataion as $orSoumis) {
            // Faire la somme des montants et les stocker dans le tableau
            if ($orSoumis->getNatureOperation() !== null) {
                if ($orSoumis->getNatureOperation() === 'VTE') {
                    $totalRecapOr['mttTotal'] += $orSoumis->getMontantVente();
                    $totalRecapOr['mttPieces'] += $orSoumis->getMontantPiece();
                    $totalRecapOr['mttMo'] += $orSoumis->getMontantMo();
                    $totalRecapOr['mttSt'] += $orSoumis->getMontantAchatLocaux();
                    $totalRecapOr['mttLub'] += $orSoumis->getMontantLubrifiants();
                    $totalRecapOr['mttAutres'] += $orSoumis->getMontantFraisDivers();
                } else {
                    $totalRecapOr['mttTotal'] += $orSoumis->getMontantItv();
                    $totalRecapOr['mttPieces'] += $orSoumis->getMontantPiece();
                    $totalRecapOr['mttMo'] += $orSoumis->getMontantMo();
                    $totalRecapOr['mttSt'] += $orSoumis->getMontantAchatLocaux();
                    $totalRecapOr['mttLub'] += $orSoumis->getMontantLubrifiants();
                    $totalRecapOr['mttAutres'] += $orSoumis->getMontantFraisDivers();
                }
            } else {
                $totalRecapOr['mttTotal'] += $orSoumis->getMontantItv();
                $totalRecapOr['mttPieces'] += $orSoumis->getMontantPiece();
                $totalRecapOr['mttMo'] += $orSoumis->getMontantMo();
                $totalRecapOr['mttSt'] += $orSoumis->getMontantAchatLocaux();
                $totalRecapOr['mttLub'] += $orSoumis->getMontantLubrifiants();
                $totalRecapOr['mttAutres'] += $orSoumis->getMontantFraisDivers();
            }
        }

        return $totalRecapOr;
    }
}