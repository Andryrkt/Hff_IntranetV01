<?php

namespace App\Controller\dit;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Controller\Controller;
use App\Controller\Traits\dit\DitFactureSoumisAValidationtrait;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitFactureSoumisAValidation;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Form\dit\DitFactureSoumisAValidationType;
use App\Model\dit\DitFactureSoumisAValidationModel;
use App\Service\fichier\FileUploaderService;
use App\Service\genererPdf\GenererPdfFactureAValidation;
use App\Service\historiqueOperation\HistoriqueOperationFACService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitFactureSoumisAValidationController extends Controller
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
        $this->historiqueOperation = new HistoriqueOperationFACService();
        $this->ditFactureSoumiAValidationModel = new DitFactureSoumisAValidationModel();
        $this->genererPdfFacture = new GenererPdfFactureAValidation();
        $this->ditFactureSoumiAValidation = new DitFactureSoumisAValidation();
        $this->fileUploaderService = new FileUploaderService($_ENV['BASE_PATH_FICHIER'].'/vfac/');
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
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

            $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
        }

        $this->ditFactureSoumiAValidation->setNumeroDit($numDit);
        $this->ditFactureSoumiAValidation->setNumeroOR($numOrBaseDonner[0]['numor']);

        $form = self::$validator->createBuilder(DitFactureSoumisAValidationType::class, $this->ditFactureSoumiAValidation)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
            $numFac = $this->ditFactureSoumiAValidation->getNumeroFact();

            $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();

            if (strpos($originalName, 'FACTURE CESSION') !== 0) {
                $message = "Le fichier '{$originalName}' soumis a été renommé ou ne correspond pas à la facture de l'OR";

                $this->historiqueOperation->sendNotificationSoumission($message, '-', 'dit_index');
            }

            $this->ditFactureSoumiAValidation->setNumeroFact(explode('_', $originalName)[1]);

            $nbFact = $this->nombreFact($this->ditFactureSoumiAValidationModel, $this->ditFactureSoumiAValidation);

            $nbFactSqlServer = self::$em->getRepository(DitFactureSoumisAValidation::class)->findNbrFact($numFac);

            if ($numOrBaseDonner[0]['numor'] !== $this->ditFactureSoumiAValidation->getNumeroOR()) {
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";

                $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            } elseif ($nbFact === 0) {
                $message = "La facture ne correspond pas à l’OR";

                $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            } elseif ($nbFactSqlServer > 0) {
                $message = "La facture n° :{$numFac} a été déjà soumise à validation ";

                $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
            } else {
                $dataForm = $form->getData();
                $numeroSoumission = $this->ditFactureSoumiAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());

                $this->ajoutInfoEntityDitFactur($this->ditFactureSoumiAValidation, $numDit, $dataForm, $numeroSoumission);

                $factureSoumisAValidation = $this->ditFactureSoumisAValidation($numDit, $dataForm, $this->ditFactureSoumiAValidationModel, $numeroSoumission, self::$em, $this->ditFactureSoumiAValidation);

                $estRi = $this->conditionSurInfoFacture($this->ditFactureSoumiAValidationModel, $dataForm, $this->ditFactureSoumiAValidation, $numDit);

                if ($estRi) {
                    $message = "La facture ne correspond pas ou correspond partiellement à un rapport d'intervention.";
                    $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
                } else {

                    $interneExterne = $this->ditRepository->findInterneExterne($numDit);
                    /** CREATION PDF */
                    $pathPageDeGarde = $this->enregistrerPdf($dataForm, $numDit, $factureSoumisAValidation, $interneExterne);
                    $pathFichiers = $this->enregistrerFichiers($form, $numFac, $this->ditFactureSoumiAValidation->getNumeroSoumission());
                    // dd($pathPageDeGarde, $pathFichiers, $interneExterne);
                    // dd('Une erreur s\'est produite');
                    /**
                     * TODO : facture pour le client externe
                     */
                    if ($interneExterne === 'INTERNE') {
                        $ficherAfusioner = $this->fileUploaderService->insertFileAtPosition($pathFichiers, $pathPageDeGarde, 0);
                        $this->fusionPdf->mergePdfs($ficherAfusioner, $pathPageDeGarde);
                        $this->genererPdfFacture->copyToDwFactureSoumis($this->ditFactureSoumiAValidation->getNumeroSoumission(), $numFac);
                    } else {
                        $this->genererPdfFacture->copyToDwFacture($this->ditFactureSoumiAValidation->getNumeroSoumission(), $numFac);
                        $this->genererPdfFacture->copyToDwFactureFichier($this->ditFactureSoumiAValidation->getNumeroSoumission(), $numFac, $pathFichiers);
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

        self::$twig->display('dit/DitFactureSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function enregistrerPdf($dataForm, string $numDit, $factureSoumisAValidation, string $interneExterne)
    {
        $orSoumisValidationModel = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumisValid($this->ditFactureSoumiAValidation->getNumeroOR());

        $orSoumisFact = $this->ditFactureSoumiAValidationModel->recupOrSoumisValidation($this->ditFactureSoumiAValidation->getNumeroOR(), $dataForm->getNumeroFact());
        $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $this->ditFactureSoumiAValidation);
        $numDevis = $this->ditModel->recupererNumdevis($this->ditFactureSoumiAValidation->getNumeroOR());
        $statut = $this->affectationStatutFac(self::$em, $numDit, $dataForm, $this->ditFactureSoumiAValidationModel, $this->ditFactureSoumiAValidation);
        $montantPdf = $this->montantpdf($orSoumisValidataion, $factureSoumisAValidation, $statut, $orSoumisFact);

        $etatOr = $this->etatOr($dataForm, $this->ditFactureSoumiAValidationModel);
        $this->modificationEtatFacturDit($etatOr, $numDit);

        return $this->genererPdfFacture->GenererPdfFactureSoumisAValidation($this->ditFactureSoumiAValidation, $numDevis, $montantPdf, $etatOr, $this->nomUtilisateur(self::$em)['emailUtilisateur'], $interneExterne);

    }

    public function enregistrerFichiers(FormInterface $form, string $numeroFac, int $numeroSoumission): array
    {

        $options = [
            'prefixFichier' => 'factureValidation',
            'numeroDoc' => $numeroFac,
            'numeroVersion' => $numeroSoumission,
        ];

        return $this->fileUploaderService->getPathFiles($form, $options);

    }

    private function ajoutDataFactureAValidation(array $factureSoumisAValidation): void
    {
        foreach ($factureSoumisAValidation as $entity) {
            self::$em->persist($entity); // Persister chaque entité individuellement
        }

        self::$em->flush();
    }

    private function modificationEtatFacturDit($etatOr, $numDit): void
    {
        $demandeIntervention = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit]);
        $demandeIntervention->setEtatFacturation($etatOr);
        self::$em->persist($demandeIntervention);
        self::$em->flush();
    }

    private function conditionSurInfoFacture($ditFactureSoumiAValidationModel, $dataForm, $ditFactureSoumiAValidation, $numDit)
    {
        $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact());


        $estRi = false;
        $riSoumis = self::$em->getRepository(DitRiSoumisAValidation::class)->findRiSoumis($ditFactureSoumiAValidation->getNumeroOR(), $numDit);

        if (empty($riSoumis)) {
            $estRi = true;
        } else {

            for ($i = 0; $i < count($infoFacture); $i++) {
                if (! in_array($infoFacture[$i]['numeroitv'], $riSoumis)) {
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
