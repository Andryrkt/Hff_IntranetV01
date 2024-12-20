<?php

namespace App\Controller\Traits\dit;

use FileException;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\FormInterface;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\dit\DitFactureSoumisAValidation;

trait DitFactureSoumisAValidationtrait
{

    private function nomUtilisateur($em){
        $userId = $this->sessionService->get('user_id', []);
        $user = $em->getRepository(User::class)->find($userId);
        return [
            'nomUtilisateur' => $user->getNomUtilisateur(),
            'emailUtilisateur' => $user->getMail()
        ];
    }

    private function etatOr($dataForm, $ditFactureSoumiAValidationModel): string
    {
        $etatFac = $ditFactureSoumiAValidationModel->recupEtatOr($dataForm->getNumeroOR())[0];

        if($etatFac == 'PF'){
            return 'Partiellement facturé';
        } else {
            return 'Complètement facturé';
        }
    }


    private function ditFactureSoumisAValidation($numDit, $dataForm, $ditFactureSoumiAValidationModel, $numeroSoumission, $em, $ditFactureSoumiAValidation): array
    { 
        $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact());
        $agServDebDit = $em->getRepository(DemandeIntervention::class)->findAgSevDebiteur($numDit);

        $factureSoumisAValidation = [];
            foreach ($infoFacture as $value) {
                $factureSoumis = new DitFactureSoumisAValidation();
                //$nombreItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNbrItv($value['numeroor']);
                
                $statutOrsSoumisValidation = $em->getRepository(DitOrsSoumisAValidation::class)->findStatutByNumeroVersionMax($value['numeroor'], (int)$value['numeroitv']);
                $montantValide = $em->getRepository(DitOrsSoumisAValidation::class)->findMontantValide($dataForm->getNumeroOR(), (int)$value['numeroitv']);
                //$statutFacControle = $this->affectationStatutFac($statutOrsSoumisValidation, $nombreItv, $agServDebDit, $value, $nombreStatutControle);
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
                        ->setMttItv($montantValide)
                        ->setLibelleItv($value['libelleitv'] === null ? '' : $value['libelleitv'])
                        ->setStatut('')
                        ->setStatutItv($statutOrsSoumisValidation)
                        ->setAgServDebDit($agServDebDit)
                ;
                $factureSoumisAValidation[] = $factureSoumis;
            }
            
        return  $factureSoumisAValidation;
        
    }


    private function affectationStatutFac($em, $numDit, $dataForm, $ditFactureSoumiAValidationModel, $ditFactureSoumiAValidation)
    {   
        $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact());
        $agServDebDit = $em->getRepository(DemandeIntervention::class)->findAgSevDebiteur($numDit);
        $migration = $em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit])->getMigration();
        $statutFac = [];
        $nombreStatutControle = [
            'nbrNonValideFacture' => 0,
            'nbrServDebDitDiffServDebFac' => 0,
            'nbrMttValideDiffMttFac' => 0,
            ];
        foreach ($infoFacture as $value) {

            $nombreItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNbrItv($value['numeroor']);
            $statutOrsSoumisValidation = $em->getRepository(DitOrsSoumisAValidation::class)->findStatutByNumeroVersionMax($value['numeroor'], (int)$value['numeroitv']);
            $montantValide = $em->getRepository(DitOrsSoumisAValidation::class)->findMontantValide($value['numeroor'], (int)$value['numeroitv']);
            if(empty($statutOrsSoumisValidation) || $nombreItv === 0 || ($statutOrsSoumisValidation <> 'Livré' && $statutOrsSoumisValidation <> 'Validé' && $statutOrsSoumisValidation <> 'Livré partiellement') || $statutOrsSoumisValidation === 'Refusée') {
                $statutFac[] = 'Itv non validée';
                $nombreStatutControle['nbrNonValideFacture']++;
            } elseif(($statutOrsSoumisValidation === 'Validé' || $statutOrsSoumisValidation === 'Livré') && $agServDebDit <> ($value['agencedebiteur'].'-'.$value['servicedebiteur'])){
                $statutFac[] = 'Serv deb DIT # Serv deb FAC';
                $nombreStatutControle['nbrServDebDitDiffServDebFac']++;
            } elseif($montantValide <> $value['montantfactureitv']) {
                if($migration == 1){
                    $statutFac[] = 'DIT migrée';
                } else {
                    $statutFac[] = 'Mtt validé # Mtt facturé';
                }
                
                $nombreStatutControle['nbrMttValideDiffMttFac']++;
            } else {
                $statutFac[] ='OK';
            }
        }
        return [
            'statutFac' => $statutFac,
            'nombreStatutControle' => $nombreStatutControle
        ];
    }

    private function infoItvFac($factureSoumisAValidation, $statut)
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

        for ($i = 0; $i < count($infoItvFac); $i++) { 
            $infoItvFac[$i]['statut'] = $statut[$i];
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

    private function montantpdf($orSoumisValidataion, $factureSoumisAValidation, $statut, $orSoumisFact)
    {
        
        return [
            'infoItvFac' => $this->infoItvFac($factureSoumisAValidation, $statut['statutFac']),
            'totalItvFac' => $this->calculeSommeItvFacture($factureSoumisAValidation),
            'recapOr' => $this->recapitulationOr($orSoumisFact),
            'totalRecapOr' => $this->calculeSommeMontant($orSoumisFact),
            'controleAFaire' => $statut['nombreStatutControle']
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
                                ->setNumeroItv($orSoumis->getNumeroItv())
                                ->setNombreLigneItv($orSoumis->getNombreLigneItv())
                                ->setMontantItv($orSoumis->getMontantItv())
                                ->setMontantPiece($orSoumis->getMontantPiece())
                                ->setMontantMo($orSoumis->getMontantMo())
                                ->setMontantAchatLocaux($orSoumis->getMontantAchatLocaux())
                                ->setMontantFraisDivers($orSoumis->getMontantFraisDivers())
                                ->setMontantLubrifiants($orSoumis->getMontantLubrifiants())
                                ->setLibellelItv($orSoumis->getLibellelItv());
                    
                    $orSoumisValidataion[] = $ditInsertionOr; // Ajouter l'objet dans le tableau
                
                }
                return $orSoumisValidataion;
    }


    private function recapitulationOr($orSoumisFact)
    {
        $recapOr = [];
        foreach ($orSoumisFact as $orSoumis) {
            $recapOr[] = [
                'itv' => $orSoumis['numero_itv'],
                'mttTotal' => $orSoumis['montant_itv'],
                'mttPieces' => $orSoumis['montant_piece'],
                'mttMo' => $orSoumis['montant_mo'],
                'mttSt' => $orSoumis['montant_achats_locaux'],
                'mttLub' => $orSoumis['montant_lubrifiants'],
                'mttAutres' => $orSoumis['montant_divers'],
            ];
        }
        return $recapOr;
    }

    private function calculeSommeMontant($orSoumisFact)
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
        foreach ($orSoumisFact as $orSoumis) {
            // Faire la somme des montants et les stocker dans le tableau
            $totalRecapOr['montant_itv'] += $orSoumis['montant_itv'];
            $totalRecapOr['montant_piece'] += $orSoumis['montant_piece'];
            $totalRecapOr['montant_mo'] += $orSoumis['montant_mo'];
            $totalRecapOr['montant_achats_locaux'] += $orSoumis['montant_achats_locaux'];
            $totalRecapOr['montant_lubrifiants'] += $orSoumis['montant_lubrifiants'];
            $totalRecapOr['montant_frais_divers'] += $orSoumis['montant_divers'];
        }

        return $totalRecapOr;
    }


/**
     * Upload un fichier et retourne le chemin du fichier enregistré si c'est un PDF, sinon null.
     *
     * @param UploadedFile $file
     * @param DitFacture $ditfacture
     * @param string $fieldName
     * @param int $index
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function uploadFile( $file,  $ditfacture, string $fieldName, int $index): ?string
    {
        // Validation des extensions et types MIME
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];

        if (
            !$file->isValid() ||
            !in_array(strtolower($file->getClientOriginalExtension()), $allowedExtensions, true) ||
            !in_array($file->getMimeType(), $allowedMimeTypes, true)
        ) {
            throw new \InvalidArgumentException("Type de fichier non autorisé pour le champ $fieldName.");
        }

        // Générer un nom de fichier sécurisé et unique

        $fileName = sprintf(
            'factureValidation_%s_%s_%02d.%s',
            $ditfacture->getNumeroFact(),
            $ditfacture->getNumeroSoumission(),
            $index,
            $file->guessExtension()
        );

        // Définir le répertoire de destination
        $destination = $_SERVER['DOCUMENT_ROOT']. 'Upload/vfac/fichier/';

        // Assurer que le répertoire existe
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        // Retourner le chemin complet du fichier si c'est un PDF, sinon null
        if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
            return $destination . $fileName;
        }

        return null;
    }
    /**
     * Envoie des pièces jointes et fusionne les PDF
     */
    private function envoiePieceJoint(
        FormInterface $form,
        DitFactureSoumisAValidation $ditfacture,
        $fusionPdf
    ): void {
        $pdfFiles = [];

        // Ajouter le fichier PDF principal en tête du tableau
        $mainPdf = sprintf(
            '%s/Upload/vfac/factureValidation_%s_%s.pdf',
            $_SERVER['DOCUMENT_ROOT'],
            $ditfacture->getNumeroFact(),
            $ditfacture->getNumeroSoumission()
        );

        // Vérifier que le fichier principal existe avant de l'ajouter
        if (!file_exists($mainPdf)) {
            throw new \RuntimeException('Le fichier PDF principal n\'existe pas.');
        }

        array_unshift($pdfFiles, $mainPdf);

       // Récupérer tous les champs de fichiers du formulaire
        $fileFields = $form->all();

        foreach ($fileFields as $fieldName => $field) {
            if (preg_match('/^pieceJoint\d{2}$/', $fieldName)) {
               /** @var UploadedFile|null $file */
                $file = $field->getData();
                if ($file !== null) {
                   // Extraire l'index du champ (e.g., pieceJoint01 -> 1)
                    if (preg_match('/^pieceJoint(\d{2})$/', $fieldName, $matches)) {
                        $index = (int)$matches[1];
                        $pdfPath = $this->uploadFile($file, $ditfacture, $fieldName, $index);
                        if ($pdfPath !== null) {
                            $pdfFiles[] = $pdfPath;
                        }
                    }
                }
            }
        }

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $mainPdf;

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