<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\devis\DevisMagasin;
use App\Form\magasin\devis\DevisMagasinType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\autres\VersionService;
use App\Service\fichier\UploderFileService;
use App\Service\genererPdf\GeneratePdfDevisMagasin;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\DevisMagasinValidationVpService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur refactorisé avec injection de dépendances
 * 
 * Cette version du contrôleur utilise l'injection de dépendances pour améliorer
 * la testabilité et la maintenabilité du code.
 * 
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinVerificationPrixControllerRefactored extends Controller
{
    private const TYPE_SOUMISSION_VERIFICATION_PRIX = 'VP';
    private const STATUT_PRIX_A_CONFIRMER = 'Prix à confirmer';
    private const MESSAGE_DE_CONFIRMATION = 'verification prix';

    // ✅ DÉPENDANCES INJECTÉES
    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private GeneratePdfDevisMagasin $generatePdfDevisMagasin;
    private DevisMagasinRepository $devisMagasinRepository;
    private UploderFileService $uploderFileService;
    private VersionService $versionService;
    private string $cheminBaseUpload;

    /**
     * Constructeur avec injection de dépendances
     * 
     * @param ListeDevisMagasinModel $listeDevisMagasinModel Modèle pour les devis magasin
     * @param HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService Service d'historique
     * @param GeneratePdfDevisMagasin $generatePdfDevisMagasin Service de génération PDF
     * @param DevisMagasinRepository $devisMagasinRepository Repository des devis magasin
     * @param UploderFileService $uploderFileService Service d'upload de fichiers
     * @param VersionService $versionService Service de gestion des versions
     * @param string $cheminBaseUpload Chemin de base pour les uploads
     */
    public function __construct(
        ListeDevisMagasinModel $listeDevisMagasinModel,
        HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService,
        GeneratePdfDevisMagasin $generatePdfDevisMagasin,
        DevisMagasinRepository $devisMagasinRepository,
        UploderFileService $uploderFileService,
        VersionService $versionService,
        string $cheminBaseUpload
    ) {
        parent::__construct();

        // ✅ DÉPENDANCES INJECTÉES
        $this->listeDevisMagasinModel = $listeDevisMagasinModel;
        $this->historiqueOperationDeviMagasinService = $historiqueOperationDeviMagasinService;
        $this->generatePdfDevisMagasin = $generatePdfDevisMagasin;
        $this->devisMagasinRepository = $devisMagasinRepository;
        $this->uploderFileService = $uploderFileService;
        $this->versionService = $versionService;
        $this->cheminBaseUpload = $cheminBaseUpload;
    }

    /**
     * @Route("/soumission-devis-magasin-verification-de-prix/{numeroDevis}", name="devis_magasin_soumission_verification_prix_refactored", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $numeroDevis = null, Request $request): Response
    {
        // Vérification si user connecté
        $this->verifierSessionUtilisateur();

        // Autorisation accès
        // Note: Cette méthode doit être implémentée dans le contrôleur parent
        // $this->autorisationAcces(Application::ID_DVM);

        // ✅ SERVICE INJECTÉ
        $validationService = new DevisMagasinValidationVpService(
            $this->historiqueOperationDeviMagasinService,
            $numeroDevis ?? ''
        );

        // Validation du numéro de devis
        if (!$validationService->checkMissingIdentifier($numeroDevis)) {
            return $this->render('error.html.twig', [
                'message' => 'Le numéro de devis est obligatoire pour la soumission.'
            ]);
        }

        // Validation du statut pour la vérification de prix
        if (!$validationService->checkBlockingStatusOnSubmission($this->devisMagasinRepository, $numeroDevis)) {
            return $this->render('error.html.twig', [
                'message' => 'Soumission bloquée car le devis est en cours de vérification de prix.'
            ]);
        }

        // Validation du statut pour la validation de devis
        if (!$validationService->checkBlockingStatusOnSubmissionForVd($this->devisMagasinRepository, $numeroDevis)) {
            return $this->render('error.html.twig', [
                'message' => 'Le prix a été déjà vérifié ... Veuillez soumettre le devis à validation'
            ]);
        }

        // Instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        // Création du formulaire
        $form = $this->getFormFactory()->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        // Traitement du formulaire
        $this->traitementFormulaire($form, $request, $devisMagasin, $validationService);

        // Affichage du formulaire
        return $this->render('magasin/devis/soumission.html.twig', [
            'form' => $form->createView(),
            'message' => self::MESSAGE_DE_CONFIRMATION,
            'numeroDevis' => $devisMagasin->getNumeroDevis()
        ]);
    }

    /**
     * Traitement du formulaire de soumission
     * 
     * ✅ MÉTHODE PUBLIQUE POUR LES TESTS
     * 
     * @param FormInterface $form Le formulaire à traiter
     * @param Request $request La requête HTTP
     * @param DevisMagasin $devisMagasin L'entité devis magasin
     * @param DevisMagasinValidationVpService $validationService Le service de validation
     */
    public function traitementFormulaire(
        FormInterface $form,
        Request $request,
        DevisMagasin $devisMagasin,
        DevisMagasinValidationVpService $validationService
    ): void {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation du fichier soumis
            if (!$validationService->validateSubmittedFile($form)) {
                return;
            }

            // Récupération du suffixe constructeur
            $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());

            // Récupération des informations depuis IPS
            $devisIps = $this->listeDevisMagasinModel->getInfoDev($devisMagasin->getNumeroDevis());

            if (!empty($devisIps)) {
                $firstDevisIps = reset($devisIps);

                // Validation de la somme des lignes
                $newSumOfLines = (int)$firstDevisIps['somme_numero_lignes'];
                if ($validationService->estSommeDeLigneChanger($this->devisMagasinRepository, $devisMagasin->getNumeroDevis(), $newSumOfLines)) {
                    return;
                }

                // Récupération du numéro de version max
                $numeroVersion = $this->devisMagasinRepository->getNumeroVersionMax($devisMagasin->getNumeroDevis());

                // Récupération de l'utilisateur connecté
                $utilisateur = $this->getUser();
                $email = $this->getUserEmail($utilisateur);

                // Enregistrement du fichier
                $fichiersEnregistrer = $this->enregistrementFichier(
                    $form,
                    $devisMagasin->getNumeroDevis(),
                    $this->versionService->autoIncrement($numeroVersion),
                    $suffixConstructeur,
                    explode('@', $email)[0]
                );
                $nomFichier = !empty($fichiersEnregistrer) ? $fichiersEnregistrer[0] : '';

                // Configuration du devis magasin
                $this->configureDevisMagasin(
                    $devisMagasin,
                    $firstDevisIps,
                    $suffixConstructeur,
                    $nomFichier,
                    $this->versionService->autoIncrement($numeroVersion)
                );

                // Enregistrement du devis magasin
                $this->devisMagasinRepository->save($devisMagasin);

                // Envoi du fichier dans DW
                $this->generatePdfDevisMagasin->copyToDWDevisMagasin($nomFichier);

                // Historisation de l'opération
                $message = "la vérification de prix du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
                $this->historiqueOperationDeviMagasinService->sendNotificationSoumission(
                    $message,
                    $devisMagasin->getNumeroDevis(),
                    'devis_magasin_liste',
                    true
                );
            } else {
                // Message d'erreur si aucune donnée IPS
                $message = "Aucune information trouvée dans IPS pour le devis numero : " . $devisMagasin->getNumeroDevis();
                $this->historiqueOperationDeviMagasinService->sendNotificationSoumission(
                    $message,
                    $devisMagasin->getNumeroDevis(),
                    'devis_magasin_liste',
                    false
                );
            }
        }
    }

    /**
     * Enregistrement du fichier uploadé
     * 
     * ✅ MÉTHODE PUBLIQUE POUR LES TESTS
     * 
     * @param FormInterface $form Le formulaire contenant le fichier
     * @param string $numDevis Le numéro de devis
     * @param int $numeroVersion Le numéro de version
     * @param string $suffix Le suffixe constructeur
     * @param string $mail L'email de l'utilisateur
     * @return array Les noms des fichiers enregistrés
     */
    public function enregistrementFichier(
        FormInterface $form,
        string $numDevis,
        int $numeroVersion,
        string $suffix,
        string $mail
    ): array {
        return $this->uploderFileService->getNomsFichiers($form, [
            'repertoire' => $this->cheminBaseUpload,
            'format_nom' => 'verificationprix_{numDevis}-{numeroVersion}#{suffix}!{mail}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix,
                'mail' => $mail
            ]
        ]);
    }

    /**
     * Configuration du devis magasin avec les données IPS
     * 
     * ✅ MÉTHODE PRIVÉE EXTRACTED POUR LA LISIBILITÉ
     * 
     * @param DevisMagasin $devisMagasin L'entité devis magasin
     * @param array $devisIps Les données IPS
     * @param string $suffixConstructeur Le suffixe constructeur
     * @param string $nomFichier Le nom du fichier
     * @param int $numeroVersion Le numéro de version
     */
    private function configureDevisMagasin(
        DevisMagasin $devisMagasin,
        array $devisIps,
        string $suffixConstructeur,
        string $nomFichier,
        int $numeroVersion
    ): void {
        $devisMagasin
            ->setNumeroDevis($devisMagasin->getNumeroDevis())
            ->setMontantDevis($devisIps['montant_total'])
            ->setDevise($devisIps['devise'])
            ->setSommeNumeroLignes($devisIps['somme_numero_lignes'])
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
            ->setNumeroVersion($numeroVersion)
            ->setStatutDw(self::STATUT_PRIX_A_CONFIRMER)
            ->setTypeSoumission(self::TYPE_SOUMISSION_VERIFICATION_PRIX)
            ->setCat($suffixConstructeur === 'C' || $suffixConstructeur === 'CP' ? true : false)
            ->setNonCat($suffixConstructeur === 'P' || $suffixConstructeur === 'CP' ? true : false)
            ->setNomFichier($nomFichier);
    }

    /**
     * Récupération de l'email de l'utilisateur
     * 
     * ✅ MÉTHODE HELPER POUR LA LISIBILITÉ
     * 
     * @param mixed $utilisateur L'utilisateur connecté
     * @return string L'email de l'utilisateur
     */
    private function getUserEmail($utilisateur): string
    {
        if (method_exists($utilisateur, 'getMail')) {
            return $utilisateur->getMail();
        }

        if (method_exists($utilisateur, 'getNomUtilisateur')) {
            return $utilisateur->getNomUtilisateur();
        }

        return '';
    }

    /**
     * Récupération des constantes du contrôleur
     * 
     * ✅ MÉTHODE UTILITAIRE POUR LES TESTS
     * 
     * @return array Les constantes du contrôleur
     */
    public function getConstants(): array
    {
        return [
            'TYPE_SOUMISSION_VERIFICATION_PRIX' => self::TYPE_SOUMISSION_VERIFICATION_PRIX,
            'STATUT_PRIX_A_CONFIRMER' => self::STATUT_PRIX_A_CONFIRMER,
            'MESSAGE_DE_CONFIRMATION' => self::MESSAGE_DE_CONFIRMATION
        ];
    }

    /**
     * Récupération des dépendances injectées
     * 
     * ✅ MÉTHODE UTILITAIRE POUR LES TESTS
     * 
     * @return array Les dépendances injectées
     */
    public function getDependencies(): array
    {
        return [
            'listeDevisMagasinModel' => $this->listeDevisMagasinModel,
            'historiqueOperationDeviMagasinService' => $this->historiqueOperationDeviMagasinService,
            'generatePdfDevisMagasin' => $this->generatePdfDevisMagasin,
            'devisMagasinRepository' => $this->devisMagasinRepository,
            'uploderFileService' => $this->uploderFileService,
            'versionService' => $this->versionService,
            'cheminBaseUpload' => $this->cheminBaseUpload
        ];
    }
}
