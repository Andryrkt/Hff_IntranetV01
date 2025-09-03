<?php

namespace Tests\Dom\Unit;

use PHPUnit\Framework\TestCase;
use App\Service\dom\DomValidationService;
use App\Entity\dom\Dom;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Tests unitaires pour DomValidationService
 */
class DomValidationServiceTest extends TestCase
{
    private DomValidationService $validationService;
    private EntityManagerInterface $entityManager;
    private EntityRepository $domRepository;
    private EntityRepository $catgRepository;

    protected function setUp(): void
    {
        // Mock de l'EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock des repositories
        $this->domRepository = $this->createMock(EntityRepository::class);
        $this->catgRepository = $this->createMock(EntityRepository::class);

        // Configuration des mocks
        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [Dom::class, $this->domRepository],
                [Catg::class, $this->catgRepository]
            ]);

        // Instanciation du service
        $this->validationService = new DomValidationService($this->entityManager);
    }

    /**
     * Test de validation d'un matricule valide
     */
    public function testValidateMatriculeValid(): void
    {
        $matricule = 'EMP001';

        $this->domRepository->method('findOneBy')
            ->with(['matricule' => $matricule])
            ->willReturn(null); // Aucun DOM existant avec ce matricule

        $result = $this->validationService->validateMatricule($matricule);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test de validation d'un matricule invalide (déjà utilisé)
     */
    public function testValidateMatriculeInvalid(): void
    {
        $matricule = 'EMP001';
        $existingDom = new Dom();
        $existingDom->setMatricule($matricule);

        $this->domRepository->method('findOneBy')
            ->with(['matricule' => $matricule])
            ->willReturn($existingDom);

        $result = $this->validationService->validateMatricule($matricule);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('déjà utilisé', $result['errors'][0]);
    }

    /**
     * Test de vérification de chevauchement de dates
     */
    public function testCheckDateOverlap(): void
    {
        $matricule = 'EMP001';
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-01-05');

        // Mock d'un DOM existant avec des dates qui se chevauchent
        $existingDom = new Dom();
        $existingDom->setMatricule($matricule);
        $existingDom->setDateDebut(new \DateTime('2024-01-03'));
        $existingDom->setDateFin(new \DateTime('2024-01-07'));

        $this->domRepository->method('findBy')
            ->willReturn([$existingDom]);

        $hasOverlap = $this->validationService->checkDateOverlap($matricule, $dateDebut, $dateFin);

        $this->assertTrue($hasOverlap);
    }

    /**
     * Test de vérification de chevauchement de dates sans conflit
     */
    public function testCheckDateOverlapNoConflict(): void
    {
        $matricule = 'EMP001';
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-01-05');

        // Mock d'un DOM existant avec des dates qui ne se chevauchent pas
        $existingDom = new Dom();
        $existingDom->setMatricule($matricule);
        $existingDom->setDateDebut(new \DateTime('2024-01-10'));
        $existingDom->setDateFin(new \DateTime('2024-01-15'));

        $this->domRepository->method('findBy')
            ->willReturn([$existingDom]);

        $hasOverlap = $this->validationService->checkDateOverlap($matricule, $dateDebut, $dateFin);

        $this->assertFalse($hasOverlap);
    }

    /**
     * Test de validation des règles métier
     */
    public function testValidateBusinessRules(): void
    {
        $dom = new Dom();
        $dom->setDateDebut(new \DateTime('2024-01-01'));
        $dom->setDateFin(new \DateTime('2024-01-05'));
        $dom->setMatricule('EMP001');

        $errors = $this->validationService->validateBusinessRules($dom);

        // Vérifier que les règles métier sont appliquées
        $this->assertIsArray($errors);
    }

    /**
     * Test de validation de cohérence
     */
    public function testValidateConsistency(): void
    {
        $dom = new Dom();
        $dom->setDateDebut(new \DateTime('2024-01-01'));
        $dom->setDateFin(new \DateTime('2024-01-05'));

        $warnings = $this->validationService->validateConsistency($dom);

        $this->assertIsArray($warnings);
    }

    /**
     * Test de validation complète d'un DOM
     */
    public function testValidateDom(): void
    {
        $dom = new Dom();
        $dom->setDateDebut(new \DateTime('2024-01-01'));
        $dom->setDateFin(new \DateTime('2024-01-05'));
        $dom->setMatricule('EMP001');

        $result = $this->validationService->validateDom($dom);

        $this->assertInstanceOf(\App\Service\dom\ValidationResult::class, $result);
        $this->assertIsBool($result->isValid());
        $this->assertIsArray($result->getErrors());
        $this->assertIsArray($result->getWarnings());
    }
}
