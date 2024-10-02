<?php

namespace App\Controller\Traits\dit;

use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DitOrsSoumisAValidation;

trait DitOrSoumisAValidationTrait
{
       /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     */
    private function uplodeFile($form, $ditInsertionOr, $nomFichier, &$pdfFiles)
    {
        
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = 'oRValidation_' .$ditInsertionOr->getNumeroOR().'_'.$ditInsertionOr->getNumeroVersion(). '_01.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/fichier/';
      
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }


    }

    private function envoiePieceJoint($form, $ditInsertionOr, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i=1; $i < 5; $i++) { 
        $nom = "pieceJoint0{$i}";
        if($form->get($nom)->getData() !== null){
                $this->uplodeFile($form, $ditInsertionOr, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' .$ditInsertionOr->getNumeroOR().'_'. $ditInsertionOr->getNumeroVersion(). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' .$ditInsertionOr->getNumeroOR().'_'. $ditInsertionOr->getNumeroVersion(). '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dit_index");
    }

    private function autoIncrement($num)
    {
        if($num === null){
            $num = 0;
        }
        return $num + 1;
    }

    private function calculeSommeMontant($orSoumisValidataion)
    {
        $totalRecapOr = [
            'total' => 'TOTAL',
            'montant_itv' => 0,
            'montant_piece' => 0,
            'montant_mo' => 0,
            'montant_achats_locaux' => 0,
            'montant_frais_divers' => 0,
            'montant_lubrifiants' => 0,
        ];
        foreach ($orSoumisValidataion as $orSoumis) {
            // Faire la somme des montants et les stocker dans le tableau
            $totalRecapOr['montant_itv'] += $orSoumis->getMontantItv();
            $totalRecapOr['montant_piece'] += $orSoumis->getMontantPiece();
            $totalRecapOr['montant_mo'] += $orSoumis->getMontantMo();
            $totalRecapOr['montant_achats_locaux'] += $orSoumis->getMontantAchatLocaux();
            $totalRecapOr['montant_frais_divers'] += $orSoumis->getMontantFraisDivers();
            $totalRecapOr['montant_lubrifiants'] += $orSoumis->getMontantLubrifiants();
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
            } elseif (($value['nbLigAv'] === 0 || $value['nbLigAv'] === '' ) && $value['mttTotalAv'] === 0.0) {
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
    

    private function montantpdf($orSoumisValidataion, $OrSoumisAvant, $OrSoumisAvantMax)
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

    private function orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis)
    {
        $orSoumisValidataion = []; // Tableau pour stocker les objets

                foreach ($orSoumisValidationModel as $orSoumis) {
                    // Instancier une nouvelle entité pour chaque entrée du tableau
                    $ditInsertionOr = new DitOrsSoumisAValidation(); 
                    
                    $ditInsertionOr
                                ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                                ->setHeureSoumission($this->getTime())
                                ->setDateSoumission(new \DateTime($this->getDatesystem()))
                                ->setNumeroOR($ditInsertionOrSoumis->getNumeroOR())
                                ->setNumeroItv($orSoumis['numero_itv'])
                                ->setNombreLigneItv($orSoumis['nombre_ligne'])
                                ->setMontantItv($orSoumis['montant_itv'])
                                ->setMontantPiece($orSoumis['montant_piece'])
                                ->setMontantMo($orSoumis['montant_mo'])
                                ->setMontantAchatLocaux($orSoumis['montant_achats_locaux'])
                                ->setMontantFraisDivers($orSoumis['montant_divers'])
                                ->setMontantLubrifiants($orSoumis['montant_lubrifiants'])
                                ->setLibellelItv($orSoumis['libelle_itv']);
                    
                    $orSoumisValidataion[] = $ditInsertionOr; // Ajouter l'objet dans le tableau
                
                }
                return $orSoumisValidataion;
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

private function verificationDatePlanning($ditInsertionOrSoumis)
{
    $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanning1($ditInsertionOrSoumis->getNumeroOR());
                $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanning2($ditInsertionOrSoumis->getNumeroOR());
            
            if(!empty($datePlannig1)){
                $datePlanning = $datePlannig1[0]['dateplanning1'];
            } else if(!empty($datePlannig2)){
                $datePlanning = $datePlannig2[0]['dateplanning2'];
            } else {
                $datePlanning = '';
            }

    return $datePlanning;
}

private function nomUtilisateur($em){
    $userId = $this->sessionService->get('user_id', []);
    $user = $em->getRepository(User::class)->find($userId);
    return $user->getNomUtilisateur();
}
}
