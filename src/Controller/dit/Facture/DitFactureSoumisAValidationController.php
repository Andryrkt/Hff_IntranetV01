<?php

namespace App\Controller\dit\Facture;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\FormInterface;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Service\fichier\FileUploaderService;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\dit\DitFactureSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\dit\DitFactureSoumisAValidationType;
use App\Model\dit\DitFactureSoumisAValidationModel;
use App\Service\genererPdf\GenererPdfFactureAValidation;
use App\Controller\Traits\dit\DitFactureSoumisAValidationtrait;
use App\Service\historiqueOperation\HistoriqueOperationFACService;
use App\Controller\BaseController;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitFactureSoumisAValidationController extends BaseController
{

    use DitFactureSoumisAValidationtrait;
    private $historiqueOperation;
    private $ditFactureSoumiAValidationModel;
    private $genererPdfFacture;
    private $ditFactureSoumiAValidation;
    private $fileUploaderService;
    private $ditRepository;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationFACService;
        $this->ditFactureSoumiAValidationModel = new DitFactureSoumisAValidationModel();
        $this->genererPdfFacture = new GenererPdfFactureAValidation();
        $this->ditFactureSoumiAValidation = new DitFactureSoumisAValidation();
        $this->fileUploaderService = new FileUploaderService($_ENV['BASE_PATH_FICHIER'] . '/vfac/');
        $this->ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/soumission-facture/{numDit}", name="dit_insertion_facture")
     *
     * @return void
     */
    public function factureSoumisAValidation(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();


        $numOrBaseDonner = $this->ditFactureSoumiAValidationModel->recupNumeroOr($numDit);


        if (empty($numOrBaseDonner)) {
            $message = "Le DIT n'a pas encore du numéro OR";

            $this->historiqueOperation->sendNotificationSoumission($message, $numDit, 'dit_index');
        }

        $this->ditFactureSoumiAValidation->setNumeroDit($numDit);
        $this->ditFactureSoumiAValidation->setNumeroOR($numOrBaseDonner[0]['numor']);

        $form = $this->getFormFactory()->createBuilder(DitFactureSoumisAValidationType::class, $this->ditFactureSoumiAValidation)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$demandeIntervention = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);


            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();
            $typeFacVente = [200, 201, 202, 203, 204, 205, 206, 207, 208, 209];
            $parts = explode('_', $originalName);
            if (isset($parts[1])) {
                $numFac = $parts[1];
            } else {
                $message = "Le fichier '{$originalName}' soumis a été renommé ou ne correspond pas à la facture de l'OR";
                $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
            }

            if (!array_key_exists(0, $this->ditFactureSoumiAValidationModel->recupTypeFacture($numFac)) || !array_key_exists(0, $this->ditFactureSoumiAValidationModel->recupQterea($numFac))) {
                $message = "Le numero facture '{$numFac}' ne correspond pas à la facture de l'OR";
                $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            } else {
                $typeFacture = (int)$this->ditFactureSoumiAValidationModel->recupTypeFacture($numFac)[0];
                $qterea = (int)$this->ditFactureSoumiAValidationModel->recupQterea($numFac)[0];
            }

            if (strpos($originalName, 'FACTURE CESSION') !== 0 && strpos($originalName, 'FACTURE-BON DE LIVRAISON') !== 0 && !(in_array($typeFacture, $typeFacVente) && strpos($originalName, 'AVOIR') !== 0 && $qterea < 0)) {
                $message = "Le fichier '{$originalName}' soumis a été renommé ou ne correspond pas à la facture de l'OR";
                $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
            }

            $this->ditFactureSoumiAValidation->setNumeroFact($numFac);

            $numFac = $this->ditFactureSoumiAValidation->getNumeroFact();

            $nbFact = $this->nombreFact($this->ditFactureSoumiAValidationModel, $this->ditFactureSoumiAValidation);

            // $numItv = $this->ditFactureSoumiAValidationModel->recupNumeroItv($numOrBaseDonner[0]['numor'], $numFac);
            // $numItvValide = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numOrBaseDonner[0]['numor']);

            // if(in_array($numItv, $numItvValide)) {
            //     echo "
            //     <script>
            //         if (confirm('⚠️  L'intervention n° $numItv n'a pas encore été validée. Voulez-vous tout de même soumettre la facture ? Cliquez sur OUI pour confirmer ou sur NON pour abandonner')) {
            //             // Redirection ou soumission ici si l’utilisateur confirme
            //             window.location.href = 'ton-lien-ou-action.php'; // à adapter
            //         } else {
            //             // Rien ou retour en arrière
            //             history.back();
            //         }
            //     </script>
            //     ";
            //     exit;
            // }

            $nbFactSqlServer = $this->getEntityManager()->getRepository(DitFactureSoumisAValidation::class)->findNbrFact($numFac);



            if ($numOrBaseDonner[0]['numor'] !== $this->ditFactureSoumiAValidation->getNumeroOR()) {
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            } elseif (!(int)$nbFact > 0) {
                $message = "La facture ne correspond pas à l’OR";
                $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            }
            //suite à la demande de diamondra facture 18644681 cas de facture refusé à soumettre validation pour être validé
            // elseif ($nbFactSqlServer > 0) {
            //     $message = "La facture n° :{$numFac} a été déjà soumise à validation ";
            //     $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            // } 
            else {
                $dataForm = $form->getData();
                $numeroSoumission = $this->ditFactureSoumiAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());

                $this->ajoutInfoEntityDitFactur($this->ditFactureSoumiAValidation, $numDit, $dataForm, $numeroSoumission);

                $factureSoumisAValidation = $this->ditFactureSoumisAValidation($numDit, $dataForm, $this->ditFactureSoumiAValidationModel, $numeroSoumission, $this->getEntityManager(), $this->ditFactureSoumiAValidation);

                $estRi = $this->conditionSurInfoFacture($this->ditFactureSoumiAValidationModel, $dataForm, $this->ditFactureSoumiAValidation, $numDit);

                if ($estRi) {
                    $message = "La facture ne correspond pas ou correspond partiellement à un rapport d'intervention.";
                    $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
                } else {

                    $interneExterne = $this->ditRepository->findInterneExterne($numDit);
                    /** CREATION PDF */
                    $pathPageDeGarde = $this->enregistrerPdf($dataForm, $numDit, $factureSoumisAValidation, $interneExterne);
                    $pathFichiers = $this->enregistrerFichiers($form, $numFac, $this->ditFactureSoumiAValidation->getNumeroSoumission(), $interneExterne);

                    if ($interneExterne === 'INTERNE') {
                        $ficherAfusioner = $this->fileUploaderService->insertFileAtPosition($pathFichiers, $pathPageDeGarde, 0);
                        $this->fusionPdf->mergePdfs($ficherAfusioner, $pathPageDeGarde);
                        $this->genererPdfFacture->copyToDwFactureSoumis($this->ditFactureSoumiAValidation->getNumeroSoumission(), $numFac);
                    } else {
                        $this->genererPdfFacture->copyToDwFacture($this->ditFactureSoumiAValidation->getNumeroSoumission(), $numFac);
                        $this->genererPdfFacture->copyToDwFactureFichier($this->ditFactureSoumiAValidation->getNumeroSoumission(), $numFac, $pathFichiers); //d'après le demande de Antsa le 22/08/2025
                    }

                    /** ENVOIE des DONNEE dans BASE DE DONNEE */
                    // Persist les entités liées
                    $this->ajoutDataFactureAValidation($factureSoumisAValidation);

                    $this->historiqueOperation->sendNotificationSoumission('Le document de controle a été généré et soumis pour validation', $dataForm->getNumeroFact(), 'dit_index', true);
                }
            }
        }

        $this->logUserVisit('dit_insertion_facture', [
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur

        $this->getTwig()->render('dit/DitFactureSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function enregistrerPdf($dataForm, string $numDit, $factureSoumisAValidation, string $interneExterne)
    {
        $orSoumisValidationModel = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class)->findOrSoumisValid($this->ditFactureSoumiAValidation->getNumeroOR());

        $orSoumisFact = $this->ditFactureSoumiAValidationModel->recupOrSoumisValidation($this->ditFactureSoumiAValidation->getNumeroOR(), $dataForm->getNumeroFact());
        $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $this->ditFactureSoumiAValidation);
        $numDevis = $this->ditModel->recupererNumdevis($this->ditFactureSoumiAValidation->getNumeroOR());
        $statut = $this->affectationStatutFac($this->getEntityManager(), $numDit, $dataForm, $this->ditFactureSoumiAValidationModel, $this->ditFactureSoumiAValidation, $interneExterne);
        $montantPdf = $this->montantpdf($factureSoumisAValidation, $statut, $orSoumisFact);

        $etatOr = $this->etatOr($dataForm, $this->ditFactureSoumiAValidationModel);
        $this->modificationEtatFacturDit($etatOr, $numDit);

        return $this->genererPdfFacture->GenererPdfFactureSoumisAValidation($this->ditFactureSoumiAValidation, $numDevis, $montantPdf, $etatOr, $this->nomUtilisateur($this->getEntityManager())['emailUtilisateur'], $interneExterne);
    }

    public function enregistrerFichiers(FormInterface $form, string $numeroFac, int $numeroSoumission, $interneExterne): array
    {
        if ($interneExterne == 'INTERNE') {
            $prefix = 'factureValidation';
        } else {
            $prefix = 'facture_client';
        }

        $options = [
            'prefixFichier' => $prefix,
            'numeroDoc' => $numeroFac,
            'numeroVersion' => $numeroSoumission,
        ];
        return $this->fileUploaderService->getPathFiles($form, $options);
    }


    private function ajoutDataFactureAValidation(array $factureSoumisAValidation): void
    {
        foreach ($factureSoumisAValidation as $entity) {
            $this->getEntityManager()->persist($entity); // Persister chaque entité individuellement
        }

        $this->getEntityManager()->flush();
    }

    private function modificationEtatFacturDit($etatOr, $numDit): void
    {
        $demandeIntervention = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $demandeIntervention->setEtatFacturation($etatOr);
        $this->getEntityManager()->persist($demandeIntervention);
        $this->getEntityManager()->flush();
    }

    private function conditionSurInfoFacture($ditFactureSoumiAValidationModel, $dataForm, $ditFactureSoumiAValidation, $numDit)
    {
        $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact());


        $estRi = false;
        $riSoumis = $this->getEntityManager()->getRepository(DitRiSoumisAValidation::class)->findRiSoumis($ditFactureSoumiAValidation->getNumeroOR(), $numDit);

        if (empty($riSoumis)) {
            $estRi = true;
        } else {

            for ($i = 0; $i < count($infoFacture); $i++) {
                if (!in_array($infoFacture[$i]['numeroitv'], $riSoumis)) {
                    $estRi = true;
                    break;
                }
            }
        }
        return $estRi;
    }

    private function nombreFact($ditFactureSoumiAValidationModel, $ditFactureSoumiAValidation)
    {
        $nbFactInformix = $ditFactureSoumiAValidationModel->recupNombreFacture($ditFactureSoumiAValidation->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact());
        if (empty($nbFactInformix)) {
            $nbFact = 0;
        } else {
            $nbFact = $nbFactInformix[0]['nbfact'];
        }

        return $nbFact;
    }

    private function ajoutInfoEntityDitFactur($ditFactureSoumiAValidation, $numDit, $dataForm, $numeroSoumission)
    {
        $ditFactureSoumiAValidation
            ->setNumeroDit($numDit)
            ->setNumeroOR($dataForm->getNumeroOR())
            ->setNumeroFact($dataForm->getNumeroFact())
            ->setHeureSoumission($this->getTime())
            ->setDateSoumission(new \DateTime($this->getDatesystem()))
            ->setNumeroSoumission($numeroSoumission)
        ;
    }
}
