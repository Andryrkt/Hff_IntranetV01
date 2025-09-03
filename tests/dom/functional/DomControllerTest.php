<?php

namespace Tests\Dom\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use App\Controller\dom\DomController;
use App\Entity\dom\Dom;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\Application;

/**
 * Tests fonctionnels pour DomController
 */
class DomControllerTest extends TestCase
{
    private DomController $controller;
    private Session $session;

    protected function setUp(): void
    {
        $this->controller = new DomController();
        $this->session = new Session(new MockArraySessionStorage());
    }

    /**
     * Test de l'étape 1 de création DOM
     */
    public function testCreateStep1(): void
    {
        // Simulation d'une requête GET
        $request = new Request();
        $request->setSession($this->session);

        // Simulation d'un utilisateur connecté
        $this->session->set('user_id', 1);

        // Test de l'accès à la page
        $response = $this->controller->createStep1($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test de l'étape 2 de création DOM
     */
    public function testCreateStep2(): void
    {
        // Simulation d'une requête GET
        $request = new Request();
        $request->setSession($this->session);

        // Simulation d'un utilisateur connecté
        $this->session->set('user_id', 1);

        // Simulation des données de l'étape 1
        $form1Data = [
            'sousTypeDocument' => 'FORMATION',
            'salarier' => 'PERMANENT'
        ];
        $this->session->set('form1Data', $form1Data);

        // Test de l'accès à la page
        $response = $this->controller->createStep2($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test de validation de matricule via API
     */
    public function testApiValidateMatricule(): void
    {
        $request = new Request();
        $request->request->set('matricule', 'EMP001');

        $response = $this->controller->apiValidateMatricule($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('valid', $data);
    }

    /**
     * Test de vérification de chevauchement de dates via API
     */
    public function testApiCheckDateOverlap(): void
    {
        $request = new Request();
        $request->request->set('matricule', 'EMP001');
        $request->request->set('dateDebut', '2024-01-01');
        $request->request->set('dateFin', '2024-01-05');

        $response = $this->controller->apiCheckDateOverlap($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('hasOverlap', $data);
    }

    /**
     * Test de calcul des indemnités via API
     */
    public function testApiCalculateIndemnities(): void
    {
        $request = new Request();
        $request->request->set('typeMissionId', 1);
        $request->request->set('categorieId', 1);
        $request->request->set('siteId', 1);
        $request->request->set('nombreJours', 2);

        $response = $this->controller->apiCalculateIndemnities($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $data);
    }

    /**
     * Test de duplication de DOM
     */
    public function testDuplicateDom(): void
    {
        $request = new Request();
        $request->setSession($this->session);

        // Simulation d'un utilisateur connecté
        $this->session->set('user_id', 1);

        $response = $this->controller->duplicateDom(1, $request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test de création de trop-perçu
     */
    public function testCreateTropPercu(): void
    {
        $request = new Request();
        $request->setSession($this->session);

        // Simulation d'un utilisateur connecté
        $this->session->set('user_id', 1);

        $response = $this->controller->createTropPercu(1, $request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test d'accès sans session utilisateur
     */
    public function testAccessWithoutSession(): void
    {
        $request = new Request();
        $request->setSession($this->session);

        // Pas de session utilisateur
        $this->session->remove('user_id');

        // Le contrôleur devrait rediriger ou retourner une erreur
        $response = $this->controller->createStep1($request);

        // Vérifier que l'accès est refusé
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    /**
     * Test de validation avec données manquantes
     */
    public function testApiValidateMatriculeMissingData(): void
    {
        $request = new Request();
        // Pas de matricule fourni

        $response = $this->controller->apiValidateMatricule($request);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['valid']);
        $this->assertStringContainsString('requis', $data['message']);
    }

    /**
     * Test de calcul d'indemnités avec paramètres manquants
     */
    public function testApiCalculateIndemnitiesMissingParams(): void
    {
        $request = new Request();
        $request->request->set('typeMissionId', 1);
        // Paramètres manquants

        $response = $this->controller->apiCalculateIndemnities($request);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Paramètres manquants', $data['message']);
    }
}
