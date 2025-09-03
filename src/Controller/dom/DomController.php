<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Entity\dom\Dom;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use App\Service\dom\DomValidationService;
use App\Service\dom\DomBusinessLogicService;
use App\Service\dom\DomNotificationService;
use App\Form\dom\DomForm1Type;
use App\Form\dom\DomForm2Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur principal pour les DOM (Dossiers de Mission)
 * @Route("/rh/ordre-de-mission")
 */
class DomController extends Controller
{
    private DomValidationService $validationService;
    private DomBusinessLogicService $businessLogicService;
    private DomNotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->validationService = new DomValidationService(
            $this->getEntityManager(),
            $this->getValidator()
        );
        $this->businessLogicService = new DomBusinessLogicService(
            $this->getEntityManager(),
            $this->validationService
        );
        $this->notificationService = new DomNotificationService(
            $this->getSessionService(),
            $this->getMailer(),
            $this->getTwig()
        );
    }

    /**
     * Formulaire de création DOM - Étape 1
     * @Route("/create/step1", name="dom_create_step1")
     */
    public function createStep1(Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DOM);

        $dom = new Dom();
        $this->initializeDomStep1($dom);

        $form = $this->getFormFactory()->createBuilder(DomForm1Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData()->toArray();
            $this->getSessionService()->set('dom_step1_data', $formData);
            
            return $this->redirectToRoute('dom_create_step2');
        }

        $this->logUserVisit('dom_create_step1');

        return $this->render('doms/create/step1.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Formulaire de création DOM - Étape 2
     * @Route("/create/step2", name="dom_create_step2")
     */
    public function createStep2(Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DOM);

        $step1Data = $this->getSessionService()->get('dom_step1_data', []);
        if (empty($step1Data)) {
            $this->notificationService->addError('Veuillez d\'abord remplir l\'étape 1');
            return $this->redirectToRoute('dom_create_step1');
        }

        $dom = new Dom();
        $this->initializeDomStep2($dom, $step1Data);

        $form = $this->getFormFactory()->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->processDomCreation($dom, $step1Data);
            
            if ($result->isSuccess()) {
                $this->notificationService->notifyDomCreation($result->getDom(), $this->getUser());
                $this->getSessionService()->remove('dom_step1_data');
                return $this->redirectToRoute('doms_liste');
            } else {
                $this->notificationService->notifyValidationError($result->getMessages());
            }
        }

        $this->logUserVisit('dom_create_step2');

        return $this->render('doms/create/step2.html.twig', [
            'form' => $form->createView(),
            'step1Data' => $step1Data,
        ]);
    }

    /**
     * API pour valider un matricule
     * @Route("/api/validate-matricule", methods={"POST"})
     */
    public function apiValidateMatricule(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $matricule = $data['matricule'] ?? '';

        $result = $this->validationService->validateMatricule($matricule);

        return $this->json($result);
    }

    /**
     * API pour vérifier les chevauchements de dates
     * @Route("/api/check-date-overlap", methods={"POST"})
     */
    public function apiCheckDateOverlap(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $matricule = $data['matricule'] ?? '';
        $dateDebut = $data['dateDebut'] ?? null;
        $dateFin = $data['dateFin'] ?? null;
        $excludeId = $data['excludeId'] ?? null;

        if (!$matricule || !$dateDebut || !$dateFin) {
            return $this->json([
                'success' => false,
                'message' => 'Paramètres manquants'
            ], 400);
        }

        try {
            $dateDebutObj = new \DateTime($dateDebut);
            $dateFinObj = new \DateTime($dateFin);
            $excludeDomId = $excludeId ? (int)$excludeId : null;

            $hasOverlap = $this->validationService->checkDateOverlap(
                $matricule,
                $dateDebutObj,
                $dateFinObj,
                $excludeDomId
            );

            return $this->json([
                'success' => true,
                'hasOverlap' => $hasOverlap,
                'message' => $hasOverlap ? 
                    'Un chevauchement de dates a été détecté' : 
                    'Aucun chevauchement détecté'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des dates'
            ], 500);
        }
    }

    /**
     * API pour calculer les indemnités
     * @Route("/api/calculate-indemnities", methods={"POST"})
     */
    public function apiCalculateIndemnities(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        try {
            $calculations = $this->businessLogicService->calculateIndemnities(
                $this->createDomFromApiData($data)
            );

            return $this->json([
                'success' => true,
                'data' => $calculations
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des indemnités'
            ], 500);
        }
    }

    /**
     * Duplique un DOM existant
     * @Route("/duplicate/{id}", name="dom_duplicate")
     */
    public function duplicateDom(int $id, Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DOM);

        $originalDom = $this->getEntityManager()->getRepository(Dom::class)->find($id);
        if (!$originalDom) {
            $this->notificationService->addError('DOM non trouvé');
            return $this->redirectToRoute('doms_liste');
        }

        $newDom = $this->businessLogicService->duplicateDom($originalDom, $this->getUser());
        
        $form = $this->getFormFactory()->createBuilder(DomForm2Type::class, $newDom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->businessLogicService->processDomCreation($newDom, $this->getUser());
            
            if ($result->isSuccess()) {
                $this->notificationService->notifyDomCreation($result->getDom(), $this->getUser());
                return $this->redirectToRoute('doms_liste');
            } else {
                $this->notificationService->notifyValidationError($result->getMessages());
            }
        }

        return $this->render('doms/duplicate.html.twig', [
            'form' => $form->createView(),
            'originalDom' => $originalDom,
        ]);
    }

    /**
     * Crée un DOM "trop perçu"
     * @Route("/trop-percu/{id}", name="dom_trop_percu")
     */
    public function createTropPercu(int $id, Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_DOM);

        $originalDom = $this->getEntityManager()->getRepository(Dom::class)->find($id);
        if (!$originalDom) {
            $this->notificationService->addError('DOM non trouvé');
            return $this->redirectToRoute('doms_liste');
        }

        if (!$this->businessLogicService->canCreateTropPercu($originalDom)) {
            $this->notificationService->addError('Ce DOM ne peut pas avoir de trop perçu');
            return $this->redirectToRoute('doms_liste');
        }

        $tropPercuDom = new Dom();
        // Initialiser le DOM trop perçu avec les données de l'original
        
        $form = $this->getFormFactory()->createBuilder(DomForm2Type::class, $tropPercuDom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->businessLogicService->processTropPercu(
                $originalDom, 
                $tropPercuDom, 
                $this->getUser()
            );
            
            if ($result->isSuccess()) {
                $this->notificationService->addSuccess('DOM trop perçu créé avec succès');
                return $this->redirectToRoute('doms_liste');
            } else {
                $this->notificationService->notifyValidationError($result->getMessages());
            }
        }

        return $this->render('doms/trop-percu.html.twig', [
            'form' => $form->createView(),
            'originalDom' => $originalDom,
        ]);
    }

    /**
     * Initialise le DOM pour l'étape 1
     */
    private function initializeDomStep1(Dom $dom): void
    {
        $user = $this->getUser();
        $agenceServiceIps = $this->agenceServiceIpsString();
        
        $dom->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSalarier('PERMANENT')
            ->setCodeAgenceAutoriser($this->getUserAgenceCodes($user))
            ->setCodeServiceAutoriser($this->getUserServiceCodes($user));
    }

    /**
     * Initialise le DOM pour l'étape 2
     */
    private function initializeDomStep2(Dom $dom, array $step1Data): void
    {
        // Initialiser avec les données de l'étape 1
        if (isset($step1Data['agenceEmetteur'])) {
            $dom->setAgenceEmetteur($step1Data['agenceEmetteur']);
        }
        if (isset($step1Data['serviceEmetteur'])) {
            $dom->setServiceEmetteur($step1Data['serviceEmetteur']);
        }
        if (isset($step1Data['sousTypeDocument'])) {
            $dom->setSousTypeDocument($step1Data['sousTypeDocument']);
        }
        if (isset($step1Data['salarier'])) {
            $dom->setSalarier($step1Data['salarier']);
        }
    }

    /**
     * Traite la création d'un DOM
     */
    private function processDomCreation(Dom $dom, array $step1Data): \App\Service\dom\ProcessResult
    {
        // Appliquer les données de l'étape 1
        $this->applyStep1Data($dom, $step1Data);
        
        // Calculer les indemnités
        $this->businessLogicService->calculateIndemnities($dom);
        
        // Traiter la création
        return $this->businessLogicService->processDomCreation($dom, $this->getUser());
    }

    /**
     * Applique les données de l'étape 1 au DOM
     */
    private function applyStep1Data(Dom $dom, array $step1Data): void
    {
        foreach ($step1Data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($dom, $method)) {
                $dom->$method($value);
            }
        }
    }

    /**
     * Crée un DOM temporaire à partir des données API
     */
    private function createDomFromApiData(array $data): Dom
    {
        $dom = new Dom();
        
        // Remplir les propriétés nécessaires pour le calcul
        // À adapter selon votre structure
        
        return $dom;
    }

    /**
     * Récupère les codes d'agence autorisés pour l'utilisateur
     */
    private function getUserAgenceCodes(User $user): array
    {
        $codes = [];
        foreach ($user->getAgenceAutoriserIds() as $agenceId) {
            $agence = $this->getEntityManager()->getRepository(\App\Entity\admin\Agence::class)->find($agenceId);
            if ($agence) {
                $codes[] = $agence->getCodeAgence();
            }
        }
        return $codes;
    }

    /**
     * Récupère les codes de service autorisés pour l'utilisateur
     */
    private function getUserServiceCodes(User $user): array
    {
        $codes = [];
        foreach ($user->getServiceAutoriserIds() as $serviceId) {
            $service = $this->getEntityManager()->getRepository(\App\Entity\admin\Service::class)->find($serviceId);
            if ($service) {
                $codes[] = $service->getCodeService();
            }
        }
        return $codes;
    }
}
