<?php

namespace Tests\Dom\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use App\Controller\dom\DomController;
use App\Controller\dom\DomApiController;
use App\Service\dom\DomValidationService;
use App\Service\dom\DomBusinessLogicService;
use App\Service\dom\DomNotificationService;
use App\Entity\dom\Dom;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\Application;

/**
 * Tests d'intégration pour le module DOM
 */
class DomIntegrationTest extends TestCase
{
    private DomController $domController;
    private DomApiController $apiController;
    private Session $session;

    protected function setUp(): void
    {
        $this->domController = new DomController();
        $this->apiController = new DomApiController();
        $this->session = new Session(new MockArraySessionStorage());
    }

    /**
     * Test d'intégration complet du flux de création DOM
     */
    public function testCompleteDomCreationFlow(): void
    {
        // Étape 1: Accès au formulaire étape 1
        $request = new Request();
        $request->setSession($this->session);
        $this->session->set('user_id', 1);

        $response = $this->domController->createStep1($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Étape 2: Accès au formulaire étape 2
        $form1Data = [
            'sousTypeDocument' => 'FORMATION',
            'salarier' => 'PERMANENT'
        ];
        $this->session->set('form1Data', $form1Data);

        $response = $this->domController->createStep2($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Étape 3: Validation du matricule via API
        $request->request->set('matricule', 'EMP001');
        $response = $this->apiController->validateMatricule($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Étape 4: Vérification des chevauchements de dates
        $request->request->set('matricule', 'EMP001');
        $request->request->set('dateDebut', '2024-01-01');
        $request->request->set('dateFin', '2024-01-05');
        $response = $this->domController->apiCheckDateOverlap($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Étape 5: Récupération des catégories
        $response = $this->apiController->getCategories(1);
        $this->assertEquals(200, $response->getStatusCode());

        // Étape 6: Récupération des sites
        $response = $this->apiController->getSites(1);
        $this->assertEquals(200, $response->getStatusCode());

        // Étape 7: Calcul des indemnités
        $request->request->set('typeMissionId', 1);
        $request->request->set('categorieId', 1);
        $request->request->set('siteId', 1);
        $request->request->set('nombreJours', 2);
        $response = $this->apiController->calculateIndemnities($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test d'intégration des services DOM
     */
    public function testDomServicesIntegration(): void
    {
        // Test de l'intégration entre DomValidationService et DomBusinessLogicService
        $validationService = new DomValidationService($this->createMock(\Doctrine\ORM\EntityManagerInterface::class));
        $businessLogicService = new DomBusinessLogicService(
            $this->createMock(\Doctrine\ORM\EntityManagerInterface::class),
            $validationService
        );

        // Vérifier que les services sont correctement liés
        $this->assertInstanceOf(DomValidationService::class, $validationService);
        $this->assertInstanceOf(DomBusinessLogicService::class, $businessLogicService);
    }

    /**
     * Test d'intégration des notifications
     */
    public function testNotificationServiceIntegration(): void
    {
        $sessionService = $this->createMock(\App\Service\SessionManagerService::class);
        $twig = $this->createMock(\Twig\Environment::class);

        $notificationService = new DomNotificationService($sessionService, $twig);

        $this->assertInstanceOf(DomNotificationService::class, $notificationService);
    }

    /**
     * Test d'intégration des formulaires
     */
    public function testFormIntegration(): void
    {
        // Test de l'intégration des formulaires avec les contrôleurs
        $request = new Request();
        $request->setSession($this->session);
        $this->session->set('user_id', 1);

        // Test du formulaire étape 1
        $response = $this->domController->createStep1($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Test du formulaire étape 2
        $form1Data = [
            'sousTypeDocument' => 'FORMATION',
            'salarier' => 'PERMANENT'
        ];
        $this->session->set('form1Data', $form1Data);

        $response = $this->domController->createStep2($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test d'intégration des APIs
     */
    public function testApiIntegration(): void
    {
        // Test de l'intégration des APIs avec les contrôleurs
        $request = new Request();

        // Test API validation matricule
        $request->request->set('matricule', 'EMP001');
        $response = $this->apiController->validateMatricule($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Test API catégories
        $response = $this->apiController->getCategories(1);
        $this->assertEquals(200, $response->getStatusCode());

        // Test API sites
        $response = $this->apiController->getSites(1);
        $this->assertEquals(200, $response->getStatusCode());

        // Test API calcul indemnités
        $request->request->set('typeMissionId', 1);
        $request->request->set('categorieId', 1);
        $request->request->set('siteId', 1);
        $request->request->set('nombreJours', 2);
        $response = $this->apiController->calculateIndemnities($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test d'intégration des autorisations
     */
    public function testAuthorizationIntegration(): void
    {
        $request = new Request();
        $request->setSession($this->session);

        // Test sans session utilisateur
        $this->session->remove('user_id');
        $response = $this->domController->createStep1($request);
        $this->assertNotEquals(200, $response->getStatusCode());

        // Test avec session utilisateur
        $this->session->set('user_id', 1);
        $response = $this->domController->createStep1($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test d'intégration des erreurs
     */
    public function testErrorHandlingIntegration(): void
    {
        $request = new Request();

        // Test avec données manquantes
        $response = $this->apiController->validateMatricule($request);
        $this->assertEquals(400, $response->getStatusCode());

        // Test avec paramètres manquants
        $response = $this->apiController->calculateIndemnities($request);
        $this->assertEquals(400, $response->getStatusCode());

        // Test avec ID inexistant
        $response = $this->apiController->getSites(999);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test d'intégration des sessions
     */
    public function testSessionIntegration(): void
    {
        $request = new Request();
        $request->setSession($this->session);

        // Test de persistance des données entre étapes
        $form1Data = [
            'sousTypeDocument' => 'FORMATION',
            'salarier' => 'PERMANENT'
        ];
        $this->session->set('form1Data', $form1Data);

        $response = $this->domController->createStep2($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Vérifier que les données sont conservées
        $this->assertEquals($form1Data, $this->session->get('form1Data'));
    }

    /**
     * Test d'intégration des réponses JSON
     */
    public function testJsonResponseIntegration(): void
    {
        $request = new Request();

        // Test que toutes les APIs retournent du JSON
        $apis = [
            'validateMatricule' => ['matricule' => 'EMP001'],
            'getCategories' => [1],
            'getSites' => [1],
            'getServices' => [1],
            'getEmployeeInfo' => ['EMP001']
        ];

        foreach ($apis as $method => $params) {
            if ($method === 'validateMatricule') {
                $request->request->set('matricule', $params[0]);
                $response = $this->apiController->$method($request);
            } else {
                $response = $this->apiController->$method(...$params);
            }

            $this->assertJson($response->getContent());
            $data = json_decode($response->getContent(), true);
            $this->assertIsArray($data);
        }
    }
}
