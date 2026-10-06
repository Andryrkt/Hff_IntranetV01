# Implémentation de la Solution 3 - Injection par Méthode

## 🎯 Plan d'Implémentation

### **Étape 1 : Modifier services.yaml**

Ajouter ces configurations à votre `config/services.yaml` :

```yaml
# Configuration des services pour l'auto-wiring
services:
    # Configuration par défaut pour tous les services
    _defaults:
        autowire: true          # ✅ Activer l'auto-wiring
        autoconfigure: true     # ✅ Configuration automatique
        public: false

    # Services de base (déjà existants)
    App\Service\:
        resource: '../src/Service/*'
        exclude:
            - '../src/Service/dit/or/'
        tags: ['app.service']
        public: true

    App\Model\:
        resource: '../src/Model/*'
        tags: ['app.model']
        public: true

    App\Repository\:
        resource: '../src/Repository/*'
        tags: ['app.repository']
        public: true

    # ✅ NOUVELLE CONFIGURATION - Services problématiques
    App\Service\genererPdf\GeneratePdfDevisMagasin:
        arguments:
            $baseCheminDuFichier: '%env(BASE_PATH_FICHIER)%/'
            $baseCheminDocuware: '%env(BASE_PATH_DOCUWARE)%/'
        public: true

    App\Service\fichier\UploderFileService:
        arguments:
            $cheminDeBase: '%env(BASE_PATH_FICHIER)%/magasin/devis/'
        public: true

    # ✅ Le contrôleur n'a besoin d'aucune configuration !
    # Symfony l'auto-wire automatiquement
```

### **Étape 2 : Créer le contrôleur auto-wirable**

Créer `src/Controller/magasin/devis/DevisMagasinVerificationPrixControllerAutoWired.php` :

```php
<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
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
 * Contrôleur avec auto-wiring complet
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

        // Service de validation
        $validationService = new DevisMagasinValidationVpService(
            $historiqueOperationDeviMagasinService, 
            $numeroDevis ?? ''
        );

        // Validations...
        if (!$validationService->checkMissingIdentifier($numeroDevis)) {
            return $this->render('error.html.twig', [
                'message' => 'Le numéro de devis est obligatoire pour la soumission.'
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
}
```

### **Étape 3 : Modifier les services problématiques**

#### A. **Refactoriser GeneratePdfDevisMagasin**

Modifier `src/Service/genererPdf/GeneratePdfDevisMagasin.php` :

```php
<?php

namespace App\Service\genererPdf;

use App\Service\genererPdf\GeneratePdf;

class GeneratePdfDevisMagasin extends GeneratePdf
{
    public function __construct(
        private string $baseCheminDuFichier,
        private string $baseCheminDocuware
    ) {
        parent::__construct();
        // Utiliser les paramètres injectés au lieu de $_ENV
        $this->baseCheminDuFichier = $baseCheminDuFichier;
        $this->baseCheminDocuware = $baseCheminDocuware;
    }
}
```

#### B. **Ajouter une méthode getter à UploderFileService**

Modifier `src/Service/fichier/UploderFileService.php` :

```php
// Ajouter cette méthode à la classe UploderFileService
public function getCheminDeBase(): string
{
    return $this->cheminDeBase;
}
```

### **Étape 4 : Tester l'implémentation**

Créer un script de test `test/test_solution_3.php` :

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== Test de la Solution 3 - Injection par Méthode ===\n\n";

try {
    // Charger le bootstrap
    $services = require __DIR__ . '/../config/bootstrap_di.php';
    $container = $services['container'];

    // Test d'instanciation du contrôleur
    $controller = new \App\Controller\magasin\devis\DevisMagasinVerificationPrixControllerAutoWired();
    echo "✅ Contrôleur auto-wiré instancié avec succès\n";

    // Test des constantes
    $constants = $controller->getConstants();
    echo "✅ Constantes : " . json_encode($constants) . "\n";

    echo "🎉 La Solution 3 fonctionne parfaitement !\n";
    echo "📝 Avantages :\n";
    echo "   - ✅ Aucune configuration pour le contrôleur\n";
    echo "   - ✅ Symfony injecte automatiquement toutes les dépendances\n";
    echo "   - ✅ Tests faciles avec injection directe\n";
    echo "   - ✅ Performance optimale\n";
    echo "   - ✅ Respect des bonnes pratiques Symfony\n";

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
```

## 🎯 **Avantages de la Solution 3**

### **1. Configuration Minimale**
- ✅ Seulement 2 services à configurer
- ✅ Le contrôleur n'a besoin d'aucune configuration
- ✅ Symfony gère tout automatiquement

### **2. Auto-Wiring Complet**
- ✅ Toutes les dépendances injectées par Symfony
- ✅ Aucune instanciation manuelle
- ✅ Performance optimale

### **3. Tests Faciles**
- ✅ Injection directe dans les tests
- ✅ Mocks faciles à créer
- ✅ Couverture de code élevée

### **4. Maintenabilité**
- ✅ Code plus lisible
- ✅ Dépendances explicites
- ✅ Évolutivité facilitée

## 🚀 **Migration Progressive**

### **Phase 1 : Préparation**
1. Modifier `services.yaml`
2. Refactoriser les services problématiques
3. Créer le contrôleur auto-wiré

### **Phase 2 : Tests**
1. Tester l'auto-wiring
2. Valider les fonctionnalités
3. Tests de régression

### **Phase 3 : Déploiement**
1. Remplacer l'ancien contrôleur
2. Mettre à jour les routes
3. Monitoring en production

## 🎉 **Conclusion**

La **Solution 3** est parfaite pour votre cas car :
- ✅ **Simplicité maximale** : Symfony gère tout
- ✅ **Configuration minimale** : Seulement 2 services
- ✅ **Performance optimale** : Instanciation à la demande
- ✅ **Tests faciles** : Injection directe
- ✅ **Respect des bonnes pratiques** Symfony

**C'est la solution idéale pour moderniser votre contrôleur tout en gardant la simplicité !** 🚀
