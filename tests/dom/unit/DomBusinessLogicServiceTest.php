<?php

namespace Tests\Dom\Unit;

use PHPUnit\Framework\TestCase;
use App\Service\dom\DomBusinessLogicService;
use App\Service\dom\DomValidationService;
use App\Entity\dom\Dom;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Tests unitaires pour DomBusinessLogicService
 */
class DomBusinessLogicServiceTest extends TestCase
{
    private DomBusinessLogicService $businessLogicService;
    private DomValidationService $validationService;
    private EntityManagerInterface $entityManager;
    private EntityRepository $domRepository;
    private EntityRepository $indemniteRepository;

    protected function setUp(): void
    {
        // Mock de l'EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock du service de validation
        $this->validationService = $this->createMock(DomValidationService::class);

        // Mock des repositories
        $this->domRepository = $this->createMock(EntityRepository::class);
        $this->indemniteRepository = $this->createMock(EntityRepository::class);

        // Configuration des mocks
        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Dom::class, $this->domRepository],
                [Indemnite::class, $this->indemniteRepository]
            ]);

        // Instanciation du service
        $this->businessLogicService = new DomBusinessLogicService(
            $this->entityManager,
            $this->validationService
        );
    }

    /**
     * Test de création d'un DOM
     */
    public function testCreateDom(): void
    {
        $domData = [
            'matricule' => 'EMP001',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'dateDebut' => new \DateTime('2024-01-01'),
            'dateFin' => new \DateTime('2024-01-05'),
            'motifDeplacement' => 'Formation'
        ];

        $user = $this->createMock(User::class);

        // Mock de la validation
        $this->validationService->method('validateDom')
            ->willReturn(new \App\Service\dom\ValidationResult(true, [], []));

        $result = $this->businessLogicService->createDom($domData, $user);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Dom::class, $result['dom']);
    }

    /**
     * Test de création d'un DOM avec erreurs de validation
     */
    public function testCreateDomWithValidationErrors(): void
    {
        $domData = [
            'matricule' => 'EMP001',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'dateDebut' => new \DateTime('2024-01-01'),
            'dateFin' => new \DateTime('2024-01-05'),
            'motifDeplacement' => 'Formation'
        ];

        $user = $this->createMock(User::class);

        // Mock de la validation avec erreurs
        $this->validationService->method('validateDom')
            ->willReturn(new \App\Service\dom\ValidationResult(false, ['Erreur de validation'], []));

        $result = $this->businessLogicService->createDom($domData, $user);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
    }

    /**
     * Test de calcul des indemnités
     */
    public function testCalculateIndemnities(): void
    {
        $typeMissionId = 1;
        $categorieId = 1;
        $siteId = 1;
        $nombreJours = 2;

        // Mock d'une indemnité
        $indemnite = $this->createMock(Indemnite::class);
        $indemnite->method('getMontantIdemnite')->willReturn(50000);

        $this->indemniteRepository->method('findOneBy')
            ->willReturn($indemnite);

        $result = $this->businessLogicService->calculateIndemnities(
            $typeMissionId,
            $categorieId,
            $siteId,
            $nombreJours
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(50000, $result['data']['montant_base']);
        $this->assertEquals(100000, $result['data']['total_forfaitaire']);
        $this->assertEquals(2, $result['data']['nombre_jours']);
    }

    /**
     * Test de calcul des indemnités sans indemnité trouvée
     */
    public function testCalculateIndemnitiesNotFound(): void
    {
        $typeMissionId = 1;
        $categorieId = 1;
        $siteId = 1;
        $nombreJours = 2;

        $this->indemniteRepository->method('findOneBy')
            ->willReturn(null);

        $result = $this->businessLogicService->calculateIndemnities(
            $typeMissionId,
            $categorieId,
            $siteId,
            $nombreJours
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Aucune indemnité trouvée', $result['message']);
    }

    /**
     * Test de duplication d'un DOM
     */
    public function testDuplicateDom(): void
    {
        $domId = 1;
        $user = $this->createMock(User::class);

        // Mock d'un DOM existant
        $existingDom = $this->createMock(Dom::class);
        $existingDom->method('getId')->willReturn($domId);
        $existingDom->method('getMatricule')->willReturn('EMP001');
        $existingDom->method('getNom')->willReturn('Dupont');
        $existingDom->method('getPrenom')->willReturn('Jean');

        $this->domRepository->method('find')
            ->with($domId)
            ->willReturn($existingDom);

        // Mock de la validation
        $this->validationService->method('validateDom')
            ->willReturn(new \App\Service\dom\ValidationResult(true, [], []));

        $result = $this->businessLogicService->duplicateDom($domId, $user);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Dom::class, $result['dom']);
    }

    /**
     * Test de duplication d'un DOM inexistant
     */
    public function testDuplicateDomNotFound(): void
    {
        $domId = 999;
        $user = $this->createMock(User::class);

        $this->domRepository->method('find')
            ->with($domId)
            ->willReturn(null);

        $result = $this->businessLogicService->duplicateDom($domId, $user);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('DOM non trouvé', $result['message']);
    }

    /**
     * Test de création d'un trop-perçu
     */
    public function testCreateTropPercu(): void
    {
        $domId = 1;
        $montantTropPercu = 10000;
        $user = $this->createMock(User::class);

        // Mock d'un DOM existant
        $existingDom = $this->createMock(Dom::class);
        $existingDom->method('getId')->willReturn($domId);
        $existingDom->method('getTotalGeneralPayer')->willReturn(50000);

        $this->domRepository->method('find')
            ->with($domId)
            ->willReturn($existingDom);

        $result = $this->businessLogicService->createTropPercu($domId, $montantTropPercu, $user);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Dom::class, $result['dom']);
    }

    /**
     * Test de création d'un trop-perçu avec montant invalide
     */
    public function testCreateTropPercuInvalidAmount(): void
    {
        $domId = 1;
        $montantTropPercu = 100000; // Montant supérieur au total
        $user = $this->createMock(User::class);

        // Mock d'un DOM existant
        $existingDom = $this->createMock(Dom::class);
        $existingDom->method('getId')->willReturn($domId);
        $existingDom->method('getTotalGeneralPayer')->willReturn(50000);

        $this->domRepository->method('find')
            ->with($domId)
            ->willReturn($existingDom);

        $result = $this->businessLogicService->createTropPercu($domId, $montantTropPercu, $user);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Montant invalide', $result['message']);
    }
}
