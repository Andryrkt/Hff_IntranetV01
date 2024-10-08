<?php

namespace App\Controller\Traits\dit;

use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\dit\DitFactureSoumisAValidation;

trait DitFactureSoumisAValidationtrait
{

    private function nomUtilisateur($em){
        $userId = $this->sessionService->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return $user->getNomUtilisateur();
    }

    private function etatOr($dataForm, $ditFactureSoumiAValidationModel): string
    {
        $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $dataForm->getNumeroFact());

        $estNumfac = false;
        foreach ($infoFacture as $value) {
            if(empty($value['numerofac'])){
                $estNumfac = true;
                break;
            }
        }

        if($estNumfac){
            $etatOr = 'partiellement facturé';
        } else {
            $etatOr = 'Complètement facturé';
        }
    
        return $etatOr;
    }

  


    private function ditFactureSoumisAValidation($numDit, $dataForm, $ditFactureSoumiAValidationModel, $numeroSoumission, $em): array
    { 
        $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $dataForm->getNumeroFact());
        $agServDebDit = $em->getRepository(DemandeIntervention::class)->findAgSevDebiteur($numDit);

        $nombreStatutControle = [
            'nbrNonValideFacture' => 0,
            'nbrServDebDitDiffServDebFac' => 0,
            'nbrMttValideDiffMttFac' => 0,
            ];
        $factureSoumisAValidation = [];
            foreach ($infoFacture as $value) {
                $factureSoumis = new DitFactureSoumisAValidation();
                $nombreItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNbrItv($value['numeroor']);
                
                $statutOrsSoumisValidation = $em->getRepository(DitOrsSoumisAValidation::class)->findStatutByNumeroVersionMax($value['numeroor'], (int)$value['numeroitv']);
            
                $statutFacControle = $this->affectationStatutFac($statutOrsSoumisValidation, $nombreItv, $agServDebDit, $value, $nombreStatutControle);
            
                $factureSoumis
                        ->setNumeroDit($numDit)
                        ->setNumeroOR($dataForm->getNumeroOR())
                        ->setNumeroFact($dataForm->getNumeroFact())
                        ->setHeureSoumission($this->getTime())
                        ->setDateSoumission(new \DateTime($this->getDatesystem()))
                        ->setNumeroSoumission($numeroSoumission)
                        ->setNumeroItv($value['numeroitv'])
                        ->setMontantFactureitv($value['montantfactureitv'])
                        ->setAgenceDebiteur($value['agencedebiteur'])
                        ->setServiceDebiteur($value['servicedebiteur'])
                        ->setMttItv($value['montantitv'])
                        ->setLibelleItv($value['libelleitv'] === null ? '' : $value['libelleitv'])
                        ->setStatut($statutFacControle['statutFac'])
                        ->setStatutItv($statutOrsSoumisValidation)
                        ->setAgServDebDit($agServDebDit)
                ;
                $factureSoumisAValidation[] = $factureSoumis;
            }
            
        return [
            'factureSoumisAValidation' => $factureSoumisAValidation,
            'nombreStatutControle' => $nombreStatutControle
        ];
    }


    private function affectationStatutFac($statutOrsSoumisValidation, $nombreItv, $agServDebDit, $value, $nombreStatutControle)
    {   
        if(empty($statutOrsSoumisValidation) || $nombreItv === 0 || ($statutOrsSoumisValidation <> 'Livré' && $statutOrsSoumisValidation <> 'Validé') || $statutOrsSoumisValidation === 'Refusée') {
            $statutFac = 'Itv non validée';
            $nombreStatutControle['nbrNonValideFacture']++;
        } elseif($agServDebDit <> ($value['agencedebiteur'].'-'.$value['servicedebiteur'])){
            $statutFac = 'Serv deb DIT != Serv deb FAC';
            $nombreStatutControle['nbrServDebDitDiffServDebFac']++;
        } elseif($statutOrsSoumisValidation === 'Validé' && $value['montantitv'] <> $value['montantfactureitv']) {
            $statutFac = 'Mtt validé != Mtt facturé';
            $nombreStatutControle['nbrMttValideDiffMttFac']++;
        } else {
            $statutFac ='OK';
        }
    
        return [
            'statutFac' => $statutFac,
            'nombreStatutControle' => $nombreStatutControle
        ];
    }
    private function infoItvFac($factureSoumisAValidation)
    {
        $infoItvFac = [];
        foreach ($factureSoumisAValidation as $value) {
        
            $infoItvFac[] = [
                'itv' => $value->getNumeroItv(),
                'libelleItv' => $value->getLibelleItv(),
                'statutItv' => $value->getStatutItv(),
                'mttItv' => (float)$value->getMttItv(),
                'mttFac' => $value->getMontantFactureitv(),
                'AgServDebDit' => $value->getAgServDebDit(),
                'AgServDebFac' => $value->getAgenceDebiteur() .'-'.$value->getServiceDebiteur(),
                'controleAFaire' => $value->getStatut()
            ];
        }
        
        return $infoItvFac;
    }


    
    private function calculeSommeItvFacture($factureSoumisAValidation)
    {
        $totalItvFacture = [
            'premierLigne' => '',
            'total' => 'TOTAL',
            'statur' => '',
            'totalMttItv' => 0,
            'totalMttFac' => 0,
            'AgServDebDit' => '',
            'AgServDebFac' => '',
            'controleAFaire' => ''
        ];
        foreach ($factureSoumisAValidation as  $value) {
            $totalItvFacture['totalMttItv'] += $value->getMttItv();
            $totalItvFacture['totalMttFac'] += $value->getMontantFactureitv();
        }

        return $totalItvFacture;
    }

    private function montantpdf($orSoumisValidataion, $factureSoumisAValidation)
    {
        return [
            'infoItvFac' => $this->infoItvFac($factureSoumisAValidation['factureSoumisAValidation']),
            'totalItvFac' => $this->calculeSommeItvFacture($factureSoumisAValidation['factureSoumisAValidation']),
            'recapOr' => $this->recapitulationOr($orSoumisValidataion),
            'totalRecapOr' => $this->calculeSommeMontant($orSoumisValidataion),
            'controleAFaire' => $factureSoumisAValidation['nombreStatutControle']
        ];
    }


    private function orSoumisValidataion($orSoumisValidationModel, $ditInsertionOrSoumis)
    {
        $orSoumisValidataion = []; // Tableau pour stocker les objets

                foreach ($orSoumisValidationModel as $orSoumis) {
                    // Instancier une nouvelle entité pour chaque entrée du tableau
                    $ditInsertionOr = new DitOrsSoumisAValidation(); 
                    
                    $ditInsertionOr
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


        /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     */
    private function uplodeFile($form, $ditfacture, $nomFichier, &$pdfFiles)
    {
        
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = 'factureValidation_' .$ditfacture->getNumeroFact().'_'.$ditfacture->getNumeroSoumission(). '_01.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vfac/fichier/';
      
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }

    }

    private function envoiePieceJoint($form, $ditfacture, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i=1; $i < 5; $i++) { 
        $nom = "pieceJoint0{$i}";
        if($form->get($nom)->getData() !== null){
                $this->uplodeFile($form, $ditfacture, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Upload/vfac/factureValidation_' .$ditfacture->getNumeroFact().'_'.$ditfacture->getNumeroSoumission(). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vfac/factureValidation_' .$ditfacture->getNumeroFact().'_'.$ditfacture->getNumeroSoumission(). '.pdf';

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
}