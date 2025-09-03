<?php

namespace Tests\Dom\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\dom\DomApiController;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\dom\Indemnite;

/**
 * Tests fonctionnels pour DomApiController
 */
class DomApiControllerTest extends TestCase
{
    private DomApiController $apiController;

    protected function setUp(): void
    {
        $this->apiController = new DomApiController();
    }

    /**
     * Test de récupération des catégories
     */
    public function testGetCategories(): void
    {
        $typeMissionId = 1;

        $response = $this->apiController->getCategories($typeMissionId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * Test de récupération des sites
     */
    public function testGetSites(): void
    {
        $categorieId = 1;

        $response = $this->apiController->getSites($categorieId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * Test de récupération des services
     */
    public function testGetServices(): void
    {
        $agenceId = 1;

        $response = $this->apiController->getServices($agenceId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * Test de validation de matricule
     */
    public function testValidateMatricule(): void
    {
        $request = new Request();
        $request->request->set('matricule', 'EMP001');

        $response = $this->apiController->validateMatricule($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('valid', $data);
    }

    /**
     * Test de validation de matricule avec données manquantes
     */
    public function testValidateMatriculeMissingData(): void
    {
        $request = new Request();
        // Pas de matricule fourni

        $response = $this->apiController->validateMatricule($request);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['valid']);
        $this->assertStringContainsString('requis', $data['message']);
    }

    /**
     * Test de récupération d'informations employé
     */
    public function testGetEmployeeInfo(): void
    {
        $matricule = 'EMP001';

        $response = $this->apiController->getEmployeeInfo($matricule);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Test de calcul des indemnités
     */
    public function testCalculateIndemnities(): void
    {
        $request = new Request();
        $request->request->set('typeMissionId', 1);
        $request->request->set('categorieId', 1);
        $request->request->set('siteId', 1);
        $request->request->set('nombreJours', 2);

        $response = $this->apiController->calculateIndemnities($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Test de calcul des indemnités avec paramètres manquants
     */
    public function testCalculateIndemnitiesMissingParams(): void
    {
        $request = new Request();
        $request->request->set('typeMissionId', 1);
        // Paramètres manquants

        $response = $this->apiController->calculateIndemnities($request);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Paramètres manquants', $data['message']);
    }

    /**
     * Test de récupération des catégories avec type de mission inexistant
     */
    public function testGetCategoriesWithInvalidType(): void
    {
        $typeMissionId = 999; // Type inexistant

        $response = $this->apiController->getCategories($typeMissionId);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEmpty($data['data']); // Aucune catégorie pour ce type
    }

    /**
     * Test de récupération des sites avec catégorie inexistante
     */
    public function testGetSitesWithInvalidCategory(): void
    {
        $categorieId = 999; // Catégorie inexistante

        $response = $this->apiController->getSites($categorieId);

        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('non trouvée', $data['message']);
    }

    /**
     * Test de récupération des services avec agence inexistante
     */
    public function testGetServicesWithInvalidAgency(): void
    {
        $agenceId = 999; // Agence inexistante

        $response = $this->apiController->getServices($agenceId);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEmpty($data['data']); // Aucun service pour cette agence
    }
}
