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
 * Contrôleur avec auto-wiring complet - Version Production
 * 
 * Cette version utilise l'injection par méthode pour permettre l'auto-wiring
 * de toutes les dépendances par Symfony.
 * 
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinVerificationPrixControllerAutoWired extends Controller
{
    private const TYPE_SOUMISSION_VERIFICATION_PRIX = 'VP';
    private const STATUT_PRIX_A_CONFIRMER = 'Prix à confirmer';
    private const MESSAGE_DE_CONFIRMATION = 'verification prix';

    /**
     * @Route("/soumission-devis-magasin-verification-de-prix/{numeroDevis}", 
     *        name="devis_magasin_soumission_verification_prix_autowired", 
     *        defaults={"numeroDevis"=null})
     */
    public function soumission(
        ?string $numeroDevis = null,
        Request $request,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService,
        GeneratePdfDevisMagasin $generatePdfDevisMagasin,
        DevisMagasinRepository $devisMagasinRepository,
        UploderFileService $uploderFileService,
        VersionService $versionService
    ): Response {
        // Vérification si user connecté
        $this->verifierSessionUtilisateur();

        // Autorisation accès
        $this->autorisationAcces(Application::ID_DVM);

        // Service de validation
        $validationService = new DevisMagasinValidationVpService(
            $historiqueOperationDeviMagasinService,
            $numeroDevis ?? ''
        );

        // Validation du numéro de devis
        if (!$validationService->checkMissingIdentifier($numeroDevis)) {
            return $this->render('error.html.twig', [
                'message' => 'Le numéro de devis est obligatoire pour la soumission.'
            ]);
        }

        // Validation du statut pour la vérification de prix
        if (!$validationService->checkBlockingStatusOnSubmission($devisMagasinRepository, $numeroDevis)) {
            return $this->render('error.html.twig', [
                'message' => 'Soumission bloquée car le devis est en cours de vérification de prix.'
            ]);
        }

        // Validation du statut pour la validation de devis
        if (!$validationService->checkBlockingStatusOnSubmissionForVd($devisMagasinRepository, $numeroDevis)) {
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
        $this->traitementFormulaire(
            $form,
            $request,
            $devisMagasin,
            $validationService,
            $listeDevisMagasinModel,
            $devisMagasinRepository,
            $generatePdfDevisMagasin,
            $uploderFileService,
            $versionService,
            $historiqueOperationDeviMagasinService
        );

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
     * ✅ TOUTES LES DÉPENDANCES INJECTÉES PAR SYMFONY
     * ✅ MÉTHODE PUBLIQUE POUR LES TESTS
     * 
     * @param FormInterface $form Le formulaire à traiter
     * @param Request $request La requête HTTP
     * @param DevisMagasin $devisMagasin L'entité devis magasin
     * @param DevisMagasinValidationVpService $validationService Le service de validation
     * @param ListeDevisMagasinModel $listeDevisMagasinModel Le modèle pour les devis
     * @param DevisMagasinRepository $devisMagasinRepository Le repository des devis
     * @param GeneratePdfDevisMagasin $generatePdfDevisMagasin Le service de génération PDF
     * @param UploderFileService $uploderFileService Le service d'upload
     * @param VersionService $versionService Le service de gestion des versions
     * @param HistoriqueOperationDevisMagasinService $historiqueService Le service d'historique
     */
    public function traitementFormulaire(
        FormInterface $form,
        Request $request,
        DevisMagasin $devisMagasin,
        DevisMagasinValidationVpService $validationService,
        ListeDevisMagasinModel $listeDevisMagasinModel,
        DevisMagasinRepository $devisMagasinRepository,
        GeneratePdfDevisMagasin $generatePdfDevisMagasin,
        UploderFileService $uploderFileService,
        VersionService $versionService,
        HistoriqueOperationDevisMagasinService $historiqueService
    ): void {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation du fichier soumis
            if (!$validationService->validateSubmittedFile($form)) {
                return;
            }

            // Récupération du suffixe constructeur
            $suffixConstructeur = $listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());

            // Récupération des informations depuis IPS
            $devisIps = $listeDevisMagasinModel->getInfoDev($devisMagasin->getNumeroDevis());

            if (!empty($devisIps)) {
                $firstDevisIps = reset($devisIps);

                // Validation de la somme des lignes
                $newSumOfLines = (int)$firstDevisIps['somme_numero_lignes'];
                if ($validationService->estSommeDeLigneChanger($devisMagasinRepository, $devisMagasin->getNumeroDevis(), $newSumOfLines)) {
                    return;
                }

                // Récupération du numéro de version max
                $numeroVersion = $devisMagasinRepository->getNumeroVersionMax($devisMagasin->getNumeroDevis());

                // Récupération de l'utilisateur connecté
                $utilisateur = $this->getUser();
                $email = $this->getUserEmail($utilisateur);

                // Enregistrement du fichier
                $fichiersEnregistrer = $this->enregistrementFichier(
                    $form,
                    $devisMagasin->getNumeroDevis(),
                    $versionService->autoIncrement($numeroVersion),
                    $suffixConstructeur,
                    explode('@', $email)[0],
                    $uploderFileService
                );
                $nomFichier = !empty($fichiersEnregistrer) ? $fichiersEnregistrer[0] : '';

                // Configuration du devis magasin
                $this->configureDevisMagasin(
                    $devisMagasin,
                    $firstDevisIps,
                    $suffixConstructeur,
                    $nomFichier,
                    $versionService->autoIncrement($numeroVersion)
                );

                // Enregistrement du devis magasin
                $devisMagasinRepository->save($devisMagasin);

                // Envoi du fichier dans DW
                $generatePdfDevisMagasin->copyToDWDevisMagasin($nomFichier);

                // Historisation de l'opération
                $message = "la vérification de prix du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
                $historiqueService->sendNotificationSoumission(
                    $message,
                    $devisMagasin->getNumeroDevis(),
                    'devis_magasin_liste',
                    true
                );
            } else {
                // Message d'erreur si aucune donnée IPS
                $message = "Aucune information trouvée dans IPS pour le devis numero : " . $devisMagasin->getNumeroDevis();
                $historiqueService->sendNotificationSoumission(
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
     * ✅ SERVICE INJECTÉ PAR SYMFONY
     * ✅ MÉTHODE PUBLIQUE POUR LES TESTS
     * 
     * @param FormInterface $form Le formulaire contenant le fichier
     * @param string $numDevis Le numéro de devis
     * @param int $numeroVersion Le numéro de version
     * @param string $suffix Le suffixe constructeur
     * @param string $mail L'email de l'utilisateur
     * @param UploderFileService $uploderFileService Le service d'upload (injecté)
     * @return array Les noms des fichiers enregistrés
     */
    public function enregistrementFichier(
        FormInterface $form,
        string $numDevis,
        int $numeroVersion,
        string $suffix,
        string $mail,
        UploderFileService $uploderFileService
    ): array {
        return $uploderFileService->getNomsFichiers($form, [
            'repertoire' => $uploderFileService->getCheminDeBase() . '/magasin/devis/',
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
     * Récupération des informations sur l'auto-wiring
     * 
     * ✅ MÉTHODE UTILITAIRE POUR LES TESTS
     * 
     * @return array Informations sur l'auto-wiring
     */
    public function getAutoWiringInfo(): array
    {
        return [
            'type' => 'method_injection',
            'description' => 'Toutes les dépendances sont injectées par Symfony via les paramètres de méthode',
            'advantages' => [
                'Aucune configuration nécessaire pour le contrôleur',
                'Auto-wiring complet de tous les services',
                'Tests faciles avec injection directe',
                'Performance optimale (instanciation à la demande)',
                'Respect des bonnes pratiques Symfony'
            ],
            'configuration_required' => [
                'App\Service\genererPdf\GeneratePdfDevisMagasin' => 'Paramètres de chemin',
                'App\Service\fichier\UploderFileService' => 'Chemin de base'
            ]
        ];
    }
}
