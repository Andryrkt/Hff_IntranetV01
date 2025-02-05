<?php

namespace App\Controller\cde;

use App\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Service\fichier\FileUploaderService;
use App\Service\genererPdf\GenererPdfCdeFnr;
use App\Form\cde\CdeFnrSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Model\cde\CdefnrSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\cde\CdefnrSoumisAValidationRepository;
use App\Service\historiqueOperation\HistoriqueOperationCDEFNRService;

class CdefnrSoumisAValidationController extends Controller
{
    private CdefnrSoumisAValidationModel $cdeFnrModel;
    private CdefnrSoumisAValidationRepository $cdeFnrRepository;
    private HistoriqueOperationCDEFNRService $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->cdeFnrModel = new CdefnrSoumisAValidationModel();
        $this->cdeFnrRepository = self::$em->getRepository(CdefnrSoumisAValidation::class);
        $this->historiqueOperation = new HistoriqueOperationCDEFNRService();
    }


    /**
     * @Route("/cde-fournisseur", name="cde_fournisseur")
     */
    public function cdeFournisseur (Request $request)
    {
        $this->verifierSessionUtilisateur();

        $form= self::$validator->createBuilder(CdeFnrSoumisAValidationType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $blockages = $this->conditionDeBlockage($form, $data);
            
            if ($this->blockageSoumissionCdeFnr($blockages, $data)) {
                $cdeFournisseur = $this->ajoutDonnerEntity($data);
            
                //Enregistrement du fichier
                $numFnrCde = $data->getCodeFournisseur().'_'.$data->getNumCdeFournisseur();
                $fileName = $this->enregistrementFichier($form, $numFnrCde, $cdeFournisseur->getNumVersion());
                
                //envoyer le ficher dans docuware
                $genererPdfCdeFnr = new GenererPdfCdeFnr();
                $genererPdfCdeFnr->copyToDWCdeFnrSoumis($fileName);
    
                //ajout des données dan sla base de donnée
                $this->ajoutDonnerDansDb($cdeFournisseur);
    
                //historisation de l'operation
                $message = 'La commade fournisseur a été soumis avec succès';
                $this->historiqueOperation->sendNotificationCreation($message, $numFnrCde, 'profil_acceuil', true);
            }
        }

        self::$twig->display('cde/cdeFnr.html.twig', [
            //'fournisseurs' => $fournisseurs
            'form' => $form->createView(),
        ]);
    }

    private function conditionDeBlockage( FormInterface $form, CdefnrSoumisAValidation $data): array 
    {
        $originalName = $form->get("pieceJoint01")->getData()->getClientOriginalName();

        return [
            'numFnrEgale' => strpos($originalName, $data->getCodeFournisseur()) !== false,
            'numCdeFnrEgale' => strpos($originalName, $data->getNumCdeFournisseur()) !== false,
        ];
    }

    private function blockageSoumissionCdeFnr($blockages, $data): bool
    {
        if ($blockages['numFnrEgale']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le cde fournisseur . . . Le fichier soumis a été renommé ou ne correspond pas à un numero fournisseur ";
            $this->historiqueOperation->sendNotificationSoumission($message, $data->getCodeFournisseur(), 'profil_acceuil');
        } elseif ($blockages['numCdeFnrEgale']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le cde fournisseur . . . Le fichier soumis a été renommé ou ne correspond pas à un cde fournisseur ";
            $this->historiqueOperation->sendNotificationSoumission($message, $data->getNumCdeFournisseur(), 'profil_acceuil');
        } else {
            return true;
        }
    }
    private function autoIncrement($num)
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }

    private function ajoutDonnerEntity(CdefnrSoumisAValidation $data)
    {
        $numeroVersionMax = $this->cdeFnrRepository->findNumeroVersionMax($data->getNumCdeFournisseur());
        $cdeFournisseur = $this->cdeFnrModel->recupListeInitialCdeFrn($data->getCodeFournisseur(), $data->getNumCdeFournisseur());

            $dateCommande = new \DateTime($cdeFournisseur[0]['date_cde']);
            $prixTTc = $cdeFournisseur[0]['prix_cde_ttc'];
            $deviseCommande = $cdeFournisseur[0]['devise_cde'];
            $cdeFournisseur = new CdefnrSoumisAValidation();

            return $cdeFournisseur
                ->setCodeFournisseur($data->getCodeFournisseur())
                ->setNumCdeFournisseur($data->getNumCdeFournisseur())
                ->setLibelleFournisseur($data->getLibelleFournisseur())
                ->setDateHeureSoumission(new \DateTime($this->getDatesystem()))
                ->setStatut('Soumis à validation')
                ->setNumVersion($this->autoIncrement($numeroVersionMax))
                ->setDateCommande($dateCommande)
                ->setMontantCommande($prixTTc)
                ->setDeviseCommande($deviseCommande)
            ;
    }

    private function ajoutDonnerDansDb($cdeFournisseur)
    {
        self::$em->persist($cdeFournisseur);
            self::$em->flush();
    }

    private function enregistrementFichier(FormInterface $form, string $numFnrCde, string $numeroVersion)
    {
        $chemin = $_SERVER['DOCUMENT_ROOT'] . '/Upload/cde_fournisseur/';
        $fileUploader = new FileUploaderService($chemin);
        $options = [
            'prefix' => 'cdefrn',
            'numeroDoc' => $numFnrCde,
            'mergeFiles' => false,
            'numeroVersion' => $numeroVersion,
            'mainFirstPage' => false,
            'pathFichier' => '',
            'isIndex' => false
        ];
        $fileName = $fileUploader->chargerEtOuFusionneFichier($form, $options);

        return $fileName;
    }
}