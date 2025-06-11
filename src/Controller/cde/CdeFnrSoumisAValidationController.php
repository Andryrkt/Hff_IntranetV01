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
    public function cdeFournisseur(Request $request)
    {
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(CdeFnrSoumisAValidationType::class)->getForm();

        $this->traitementFormulaire($request, $form);

        self::$twig->display('cde/cdeFnr.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormulaire(Request $request, $form): void
    {

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dd($data);
            $originalName = $data->getPieceJoint01()->getClientOriginalName();
            $numCdeFournisseur = array_key_exists(0, explode('_', $originalName)) ? explode('_', $originalName)[0] : '';
            $originalNameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $codeFournisseur = array_key_exists(1, explode('_', $originalNameWithoutExt)) ? explode('_', $originalNameWithoutExt)[1] : '';

            $blockages = $this->conditionDeBlockage($originalName, $numCdeFournisseur,  $codeFournisseur);

            if ($this->blockageSoumissionCdeFnr($blockages, $numCdeFournisseur, $originalName)) {
                $cdeFournisseur = $this->ajoutDonnerEntity($numCdeFournisseur, $codeFournisseur);

                //Enregistrement du fichier
                $numFnrCde = $numCdeFournisseur . '_' . $codeFournisseur;
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
    }

    private function conditionDeBlockage(string $originalName, string $numCdeFournisseur, string $codeFournisseur): array
    {
        $statutCdeFrn = $this->cdeFnrRepository->findStatut($numCdeFournisseur);
        $statut = ['Soumis à validation', 'Validé', 'en cours de validation', 'Refusé'];
        return [
            'nomFichier'      => !$this->verifierFormatFichier($originalName),
            'conditionStatut' => in_array($statutCdeFrn, $statut),
        ];
    }

    private function blockageSoumissionCdeFnr($blockages, $numCdeFournisseur, $originalName): bool
    {
        if ($blockages['conditionStatut']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le cde fournisseur . . . La commande {$numCdeFournisseur} est déjà en cours de validation ";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCdeFournisseur, 'profil_acceuil');
        } elseif ($blockages['nomFichier']) {
            $message = " Erreur lors de la soumission, Impossible de soumettre le cde fournisseur . . . Le fichier '{$originalName}' soumis a été renommé";
            $this->historiqueOperation->sendNotificationSoumission($message, $numCdeFournisseur, 'profil_acceuil');
        } else {
            return true;
        }
    }

    /**
     * permet de vérifier le format du nom du fichier
     *
     * @param string $nomFichier
     * @return void
     */
    private function verifierFormatFichier(string $nomFichier): bool
    {
        // Pattern: ^ = début de chaîne
        //          [a-zA-Z0-9]+ = un ou plusieurs caractères alphanumériques (numeroCde)
        //          _ = underscore
        //          [a-zA-Z0-9]+ = un ou plusieurs caractères alphanumériques (numeroFRN)
        //          \.pdf$ = extension .pdf à la fin
        return preg_match('/^[a-zA-Z0-9]+_[a-zA-Z0-9]+\.pdf$/i', $nomFichier) === 1;
    }

    /**
     * permet de vérifier si une chaîne de caractères contient tous les mots donnés
     *
     * @param string $chaine
     * @param [type] ...$mots
     * @return bool
     */
    private function contientTousLesMots(string $chaine, ...$mots): bool
    {
        foreach ($mots as $mot) {
            if (strpos($chaine, $mot) === false) {
                return false;
            }
        }
        return true;
    }

    private function autoIncrement($num)
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }

    private function ajoutDonnerEntity(string $numCdeFournisseur, string $codeFournisseur): CdefnrSoumisAValidation
    {
        $numeroVersionMax = $this->cdeFnrRepository->findNumeroVersionMax($numCdeFournisseur);

        $cdeFournisseur = new CdefnrSoumisAValidation();
        return $cdeFournisseur
            ->setDateHeureSoumission(new \DateTime())
            ->setStatut('Soumis à validation')
            ->setNumVersion($this->autoIncrement($numeroVersionMax))
            ->setNumCdeFournisseur($numCdeFournisseur)
            ->setCodeFournisseur($codeFournisseur)
        ;
    }

    private function ajoutDonnerDansDb($cdeFournisseur)
    {
        self::$em->persist($cdeFournisseur);
        self::$em->flush();
    }

    private function enregistrementFichier(FormInterface $form, string $numFnrCde, string $numeroVersion)
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/cde_fournisseur/';
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
