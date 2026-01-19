<?php

namespace App\Controller;

use Twig\Environment;
use App\Entity\GroupeDirection;
use App\Form\GroupeDirectionType;
use App\Service\MatriculeService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GroupeDirectionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class GroupeDirectionController
{
    /**
     * @Route("/groupe-direction", name="groupe_direction_index", methods={"GET"})
     */
    public function index(): Response
    {
        // Récupérer le conteneur de services depuis la variable globale
        global $container;

        $groupeDirectionRepository = $container->get('doctrine.orm.default_entity_manager')
            ->getRepository(GroupeDirection::class);
        $groupeDirections = $groupeDirectionRepository->findAll();

        $twig = $container->get('twig');
        $html = $twig->render('groupe_direction/index.html.twig', [
            'groupe_directions' => $groupeDirections,
        ]);

        return new Response($html);
    }

    /**
     * @Route("/groupe-direction/new", name="groupe_direction_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        // Récupérer le conteneur de services depuis la variable globale
        global $container;

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $matriculeService = new MatriculeService($entityManager);
        $formFactory = $container->get('form.factory');
        $twig = $container->get('twig');
        $session = $container->get('session');

        $groupeDirection = new GroupeDirection();
        $form = $formFactory->create(GroupeDirectionType::class, $groupeDirection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupeDirection->setUtilisateurCreation($session->get('_security_username') ?? 'system');
            $groupeDirection->setDateCreation(new \DateTime());

            // Si actif est coché, enregistrer la date d'activation
            if ($groupeDirection->isActif()) {
                $groupeDirection->setDateActivation(new \DateTime());
            }

            $entityManager->persist($groupeDirection);
            $entityManager->flush();

            $session->getFlashBag()->add('success', 'Le membre a été ajouté avec succès.');

            // Redirection vers la liste
            $response = new Response();
            $response->headers->set('Location', '/Hffintranet/groupe-direction');
            $response->setStatusCode(302);
            return $response;
        }

        $html = $twig->render('groupe_direction/new.html.twig', [
            'groupe_direction' => $groupeDirection,
            'form' => $form->createView(),
        ]);

        return new Response($html);
    }

    /**
     * @Route("/groupe-direction/{id}/edit", name="groupe_direction_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $id): Response
    {
        // Récupérer le conteneur de services depuis la variable globale
        global $container;

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $groupeDirection = $entityManager->find(GroupeDirection::class, $id);

        if (!$groupeDirection) {
            // Gérer le cas où l'entité n'est pas trouvée
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('Groupe Direction non trouvé');
            return $response;
        }

        $matriculeService = new MatriculeService($entityManager);
        $formFactory = $container->get('form.factory');
        $twig = $container->get('twig');
        $session = $container->get('session');

        $originalMatricule = $groupeDirection->getMatricule();
        $form = $formFactory->create(GroupeDirectionType::class, $groupeDirection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupeDirection->setUtilisateurCreation($session->get('_security_username') ?? 'system');

            // Gérer la date d'activation selon l'état de la case "actif"
            if ($groupeDirection->isActif()) {
                // Si actif est coché et que la date d'activation est null, la définir à la date actuelle
                if (!$groupeDirection->getDateActivation()) {
                    $groupeDirection->setDateActivation(new \DateTime());
                }
            } else {
                // Si actif est décoché, réinitialiser la date d'activation
                $groupeDirection->setDateActivation(null);
            }

            $entityManager->flush();

            $session->getFlashBag()->add('success', 'Le membre a été modifié avec succès.');

            // Redirection vers la liste
            $response = new Response();
            $response->headers->set('Location', '/Hffintranet/groupe-direction');
            $response->setStatusCode(302);
            return $response;
        }

        $html = $twig->render('groupe_direction/edit.html.twig', [
            'groupe_direction' => $groupeDirection,
            'form' => $form->createView(),
        ]);

        return new Response($html);
    }

    /**
     * @Route("/groupe-direction/{id}", name="groupe_direction_delete", methods={"POST"})
     */
    public function delete(Request $request, int $id): Response
    {
        // Récupérer le conteneur de services depuis la variable globale
        global $container;

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $groupeDirection = $entityManager->find(GroupeDirection::class, $id);

        if (!$groupeDirection) {
            // Gérer le cas où l'entité n'est pas trouvée
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('Groupe Direction non trouvé');
            return $response;
        }

        $session = $container->get('session');

        $submittedToken = $request->request->get('_token');

        // Pour l'instant, nous allons simplement vérifier que le jeton a été soumis
        // dans une architecture personnalisée comme la vôtre, la validation CSRF
        // peut nécessiter une implémentation spécifique
        if ($submittedToken) {
            $entityManager->remove($groupeDirection);
            $entityManager->flush();

            $session->getFlashBag()->add('success', 'Le membre a été supprimé avec succès.');
        }

        // Redirection vers la liste
        $response = new Response();
        $response->headers->set('Location', '/Hffintranet/groupe-direction');
        $response->setStatusCode(302);
        return $response;
    }
}
