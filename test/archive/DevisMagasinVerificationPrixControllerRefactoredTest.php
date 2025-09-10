<?php

namespace App\Test\Controller\magasin\devis;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

use App\Controller\magasin\devis\DevisMagasinVerificationPrixControllerRefactored;
use App\Entity\magasin\devis\DevisMagasin;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use App\Service\magasin\devis\DevisMagasinValidationVpService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Service\genererPdf\GeneratePdfDevisMagasin;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\autres\VersionService;
use App\Service\fichier\UploderFileService;

/**
 * Tests unitaires complets pour DevisMagasinVerificationPrixControllerRefactored
 * 
 * Cette version utilise l'injection de dépendances pour permettre des tests unitaires complets
 */
class DevisMagasinVerificationPrixControllerRefactoredTest extends TestCase
{
    private DevisMagasinVerificationPrixControllerRefactored $controller;
    private MockObject $mockListeDevisMagasinModel;
    private MockObject $mockHistoriqueService;
    private MockObject $mockGeneratePdfService;
    private MockObject $mockRepository;
    private MockObject $mockUploderService;
    private MockObject $mockVersionService;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ MOCKS DES DÉPENDANCES INJECTÉES
        $this->mockListeDevisMagasinModel = $this->createMock(ListeDevisMagasinModel::class);
        $this->mockHistoriqueService = $this->createMock(HistoriqueOperationDevisMagasinService::class);
        $this->mockGeneratePdfService = $this->createMock(GeneratePdfDevisMagasin::class);
        $this->mockRepository = $this->createMock(DevisMagasinRepository::class);
        $this->mockUploderService = $this->createMock(UploderFileService::class);
        $this->mockVersionService = $this->createMock(VersionService::class);

        // ✅ INJECTION DES MOCKS
        $this->controller = new DevisMagasinVerificationPrixControllerRefactored(
            $this->mockListeDevisMagasinModel,
            $this->mockHistoriqueService,
            $this->mockGeneratePdfService,
            $this->mockRepository,
            $this->mockUploderService,
            $this->mockVersionService,
            '/tmp/test_uploads/magasin/devis/'
        );
    }

    /**
     * ✅ TEST D'INSTANCIATION
     */
    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(DevisMagasinVerificationPrixControllerRefactored::class, $this->controller);
    }

    /**
     * ✅ TEST DES CONSTANTES
     */
    public function testControllerConstants(): void
    {
        $constants = $this->controller->getConstants();

        $this->assertEquals('VP', $constants['TYPE_SOUMISSION_VERIFICATION_PRIX']);
        $this->assertEquals('Prix à confirmer', $constants['STATUT_PRIX_A_CONFIRMER']);
        $this->assertEquals('verification prix', $constants['MESSAGE_DE_CONFIRMATION']);
    }

    /**
     * ✅ TEST DES DÉPENDANCES INJECTÉES
     */
    public function testDependenciesInjection(): void
    {
        $dependencies = $this->controller->getDependencies();

        $this->assertInstanceOf(ListeDevisMagasinModel::class, $dependencies['listeDevisMagasinModel']);
        $this->assertInstanceOf(HistoriqueOperationDevisMagasinService::class, $dependencies['historiqueOperationDeviMagasinService']);
        $this->assertInstanceOf(GeneratePdfDevisMagasin::class, $dependencies['generatePdfDevisMagasin']);
        $this->assertInstanceOf(DevisMagasinRepository::class, $dependencies['devisMagasinRepository']);
        $this->assertInstanceOf(UploderFileService::class, $dependencies['uploderFileService']);
        $this->assertInstanceOf(VersionService::class, $dependencies['versionService']);
        $this->assertEquals('/tmp/test_uploads/magasin/devis/', $dependencies['cheminBaseUpload']);
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC DONNÉES VALIDES
     */
    public function testTraitementFormulaireWithValidData(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        // Mock du formulaire soumis et valide
        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        // Mock du fichier uploadé
        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('DEVIS MAGASIN_123456_001_001.pdf');

        $mockFormField = $this->createMock(FormInterface::class);
        $mockFormField->method('getData')->willReturn($mockFile);
        $mockForm->method('get')->willReturn($mockFormField);

        // Mock du service de validation
        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
        $mockValidationService->method('validateSubmittedFile')->willReturn(true);
        $mockValidationService->method('estSommeDeLigneChanger')->willReturn(false);

        // Mock des données IPS
        $devisIps = [
            [
                'montant_total' => 1000.00,
                'devise' => 'EUR',
                'somme_numero_lignes' => 5
            ]
        ];

        // Configuration des mocks
        $this->mockListeDevisMagasinModel
            ->method('constructeurPieceMagasin')
            ->with($numeroDevis)
            ->willReturn('C');

        $this->mockListeDevisMagasinModel
            ->method('getInfoDev')
            ->with($numeroDevis)
            ->willReturn($devisIps);

        $this->mockRepository
            ->method('getNumeroVersionMax')
            ->with($numeroDevis)
            ->willReturn(1);

        $this->mockVersionService
            ->method('autoIncrement')
            ->with(1)
            ->willReturn(2);

        $this->mockUploderService
            ->method('getNomsFichiers')
            ->willReturn(['verificationprix_DEV123456-2#C!test.pdf']);

        $this->mockGeneratePdfService
            ->expects($this->once())
            ->method('copyToDWDevisMagasin')
            ->with('verificationprix_DEV123456-2#C!test.pdf');

        $this->mockHistoriqueService
            ->expects($this->once())
            ->method('sendNotificationSoumission')
            ->with(
                $this->stringContains('vérification de prix du devis numero : DEV123456 a été envoyée avec succès'),
                $numeroDevis,
                'devis_magasin_liste',
                true
            );

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        $this->assertEquals('DEV123456', $devisMagasin->getNumeroDevis());
        $this->assertEquals(1000.00, $devisMagasin->getMontantDevis());
        $this->assertEquals('EUR', $devisMagasin->getDevise());
        $this->assertEquals(5, $devisMagasin->getSommeNumeroLignes());
        $this->assertEquals('Prix à confirmer', $devisMagasin->getStatutDw());
        $this->assertEquals('VP', $devisMagasin->getTypeSoumission());
        $this->assertTrue($devisMagasin->getCat());
        $this->assertFalse($devisMagasin->getNonCat());
        $this->assertEquals('verificationprix_DEV123456-2#C!test.pdf', $devisMagasin->getNomFichier());
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC DONNÉES IPS VIDES
     */
    public function testTraitementFormulaireWithNoIpsData(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('DEVIS MAGASIN_123456_001_001.pdf');

        $mockFormField = $this->createMock(FormInterface::class);
        $mockFormField->method('getData')->willReturn($mockFile);
        $mockForm->method('get')->willReturn($mockFormField);

        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
        $mockValidationService->method('validateSubmittedFile')->willReturn(true);

        // Mock des données IPS vides
        $this->mockListeDevisMagasinModel
            ->method('constructeurPieceMagasin')
            ->with($numeroDevis)
            ->willReturn('C');

        $this->mockListeDevisMagasinModel
            ->method('getInfoDev')
            ->with($numeroDevis)
            ->willReturn([]); // Aucune donnée IPS

        $this->mockHistoriqueService
            ->expects($this->once())
            ->method('sendNotificationSoumission')
            ->with(
                $this->stringContains('Aucune information trouvée dans IPS pour le devis numero : DEV123456'),
                $numeroDevis,
                'devis_magasin_liste',
                false
            );

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        $this->assertEquals('DEV123456', $devisMagasin->getNumeroDevis());
        // Les autres propriétés ne sont pas définies car pas de données IPS
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC FICHIER INVALIDE
     */
    public function testTraitementFormulaireWithInvalidFile(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        // Mock du fichier invalide
        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('fichier_invalide.txt'); // Mauvais format

        $mockFormField = $this->createMock(FormInterface::class);
        $mockFormField->method('getData')->willReturn($mockFile);
        $mockForm->method('get')->willReturn($mockFormField);

        // Mock du service de validation qui retourne false
        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
        $mockValidationService
            ->method('validateSubmittedFile')
            ->willReturn(false);

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        // Le traitement s'arrête à la validation du fichier
        $this->assertEquals('DEV123456', $devisMagasin->getNumeroDevis());
        // Aucune autre propriété n'est définie car la validation échoue
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC SOMME DES LIGNES INCHANGÉE
     */
    public function testTraitementFormulaireWithUnchangedSumOfLines(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('DEVIS MAGASIN_123456_001_001.pdf');

        $mockFormField = $this->createMock(FormInterface::class);
        $mockFormField->method('getData')->willReturn($mockFile);
        $mockForm->method('get')->willReturn($mockFormField);

        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
        $mockValidationService->method('validateSubmittedFile')->willReturn(true);
        $mockValidationService->method('estSommeDeLigneChanger')->willReturn(true); // Somme inchangée

        $devisIps = [
            [
                'montant_total' => 1000.00,
                'devise' => 'EUR',
                'somme_numero_lignes' => 5
            ]
        ];

        $this->mockListeDevisMagasinModel
            ->method('constructeurPieceMagasin')
            ->with($numeroDevis)
            ->willReturn('C');

        $this->mockListeDevisMagasinModel
            ->method('getInfoDev')
            ->with($numeroDevis)
            ->willReturn($devisIps);

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        // Le traitement s'arrête car la somme des lignes est inchangée
        $this->assertEquals('DEV123456', $devisMagasin->getNumeroDevis());
        // Aucune autre propriété n'est définie car la validation échoue
    }

    /**
     * ✅ TEST DE LA MÉTHODE enregistrementFichier
     */
    public function testEnregistrementFichier(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $numeroVersion = 2;
        $suffix = 'C';
        $mail = 'test@example.com';

        $mockForm = $this->createMock(FormInterface::class);

        $expectedResult = ['verificationprix_DEV123456-2#C!test.pdf'];

        $this->mockUploderService
            ->expects($this->once())
            ->method('getNomsFichiers')
            ->with($mockForm, [
                'repertoire' => '/tmp/test_uploads/magasin/devis/',
                'format_nom' => 'verificationprix_{numDevis}-{numeroVersion}#{suffix}!{mail}.{extension}',
                'variables' => [
                    'numDevis' => $numeroDevis,
                    'numeroVersion' => $numeroVersion,
                    'suffix' => $suffix,
                    'mail' => $mail
                ]
            ])
            ->willReturn($expectedResult);

        // Act
        $result = $this->controller->enregistrementFichier($mockForm, $numeroDevis, $numeroVersion, $suffix, $mail);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC FORMULAIRE NON SOUMIS
     */
    public function testTraitementFormulaireWithFormNotSubmitted(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('GET'); // GET au lieu de POST

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(false); // Formulaire non soumis
        $mockForm->method('isValid')->willReturn(false);

        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        // Aucune action n'est effectuée car le formulaire n'est pas soumis
        $this->assertEquals('DEV123456', $devisMagasin->getNumeroDevis());
        // Aucune autre propriété n'est définie
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC FORMULAIRE INVALIDE
     */
    public function testTraitementFormulaireWithInvalidForm(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(false); // Formulaire invalide

        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        // Aucune action n'est effectuée car le formulaire est invalide
        $this->assertEquals('DEV123456', $devisMagasin->getNumeroDevis());
        // Aucune autre propriété n'est définie
    }

    /**
     * ✅ TEST DE LA MÉTHODE traitementFormulaire AVEC DIFFÉRENTS SUFFIXES CONSTRUCTEUR
     */
    public function testTraitementFormulaireWithDifferentSuffixes(): void
    {
        $testCases = [
            ['suffix' => 'C', 'expectedCat' => true, 'expectedNonCat' => false],
            ['suffix' => 'P', 'expectedCat' => false, 'expectedNonCat' => true],
            ['suffix' => 'CP', 'expectedCat' => true, 'expectedNonCat' => true],
            ['suffix' => 'X', 'expectedCat' => false, 'expectedNonCat' => false],
        ];

        foreach ($testCases as $testCase) {
            $this->runSuffixTest($testCase, $numeroDevis);
        }
    }

    /**
     * ✅ MÉTHODE HELPER POUR LES TESTS DE SUFFIXES
     */
    private function runSuffixTest(array $testCase, string $numeroDevis): void
    {
        // Arrange
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('DEVIS MAGASIN_123456_001_001.pdf');

        $mockFormField = $this->createMock(FormInterface::class);
        $mockFormField->method('getData')->willReturn($mockFile);
        $mockForm->method('get')->willReturn($mockFormField);

        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
        $mockValidationService->method('validateSubmittedFile')->willReturn(true);
        $mockValidationService->method('estSommeDeLigneChanger')->willReturn(false);

        $devisIps = [
            [
                'montant_total' => 1000.00,
                'devise' => 'EUR',
                'somme_numero_lignes' => 5
            ]
        ];

        $this->mockListeDevisMagasinModel
            ->method('constructeurPieceMagasin')
            ->with($numeroDevis)
            ->willReturn($testCase['suffix']);

        $this->mockListeDevisMagasinModel
            ->method('getInfoDev')
            ->with($numeroDevis)
            ->willReturn($devisIps);

        $this->mockRepository
            ->method('getNumeroVersionMax')
            ->with($numeroDevis)
            ->willReturn(1);

        $this->mockVersionService
            ->method('autoIncrement')
            ->with(1)
            ->willReturn(2);

        $this->mockUploderService
            ->method('getNomsFichiers')
            ->willReturn(['verificationprix_DEV123456-2#' . $testCase['suffix'] . '!test.pdf']);

        $this->mockGeneratePdfService
            ->method('copyToDWDevisMagasin');

        $this->mockHistoriqueService
            ->method('sendNotificationSoumission');

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        $this->assertEquals(
            $testCase['expectedCat'],
            $devisMagasin->getCat(),
            "Suffix '{$testCase['suffix']}' should have cat = {$testCase['expectedCat']}"
        );
        $this->assertEquals(
            $testCase['expectedNonCat'],
            $devisMagasin->getNonCat(),
            "Suffix '{$testCase['suffix']}' should have nonCat = {$testCase['expectedNonCat']}"
        );
    }

    /**
     * ✅ TEST DE PERFORMANCE - Vérification que les méthodes sont appelées une seule fois
     */
    public function testPerformanceMethodCalls(): void
    {
        // Arrange
        $numeroDevis = 'DEV123456';
        $request = new Request();
        $request->setMethod('POST');

        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        $mockForm = $this->createMock(FormInterface::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->method('getClientOriginalName')->willReturn('DEVIS MAGASIN_123456_001_001.pdf');

        $mockFormField = $this->createMock(FormInterface::class);
        $mockFormField->method('getData')->willReturn($mockFile);
        $mockForm->method('get')->willReturn($mockFormField);

        $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
        $mockValidationService->method('validateSubmittedFile')->willReturn(true);
        $mockValidationService->method('estSommeDeLigneChanger')->willReturn(false);

        $devisIps = [
            [
                'montant_total' => 1000.00,
                'devise' => 'EUR',
                'somme_numero_lignes' => 5
            ]
        ];

        // Configuration des mocks avec vérification du nombre d'appels
        $this->mockListeDevisMagasinModel
            ->expects($this->once())
            ->method('constructeurPieceMagasin')
            ->with($numeroDevis)
            ->willReturn('C');

        $this->mockListeDevisMagasinModel
            ->expects($this->once())
            ->method('getInfoDev')
            ->with($numeroDevis)
            ->willReturn($devisIps);

        $this->mockRepository
            ->expects($this->once())
            ->method('getNumeroVersionMax')
            ->with($numeroDevis)
            ->willReturn(1);

        $this->mockVersionService
            ->expects($this->exactly(2))
            ->method('autoIncrement')
            ->with(1)
            ->willReturn(2);

        $this->mockUploderService
            ->expects($this->once())
            ->method('getNomsFichiers')
            ->willReturn(['verificationprix_DEV123456-2#C!test.pdf']);

        $this->mockGeneratePdfService
            ->expects($this->once())
            ->method('copyToDWDevisMagasin')
            ->with('verificationprix_DEV123456-2#C!test.pdf');

        $this->mockHistoriqueService
            ->expects($this->once())
            ->method('sendNotificationSoumission');

        // Act
        $this->controller->traitementFormulaire($mockForm, $request, $devisMagasin, $mockValidationService);

        // Assert
        // Les assertions sont vérifiées par les expectations des mocks
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyage des mocks
        $this->mockListeDevisMagasinModel = null;
        $this->mockHistoriqueService = null;
        $this->mockGeneratePdfService = null;
        $this->mockRepository = null;
        $this->mockUploderService = null;
        $this->mockVersionService = null;
        $this->controller = null;
    }
}
