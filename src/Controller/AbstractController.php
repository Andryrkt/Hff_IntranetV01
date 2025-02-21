<?php
namespace App\Controller;

use Twig\Environment;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\admin\historisation\pageConsultation\UserLogger;

abstract class AbstractController
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function render(string $template, array $parameters = []): string
    {
        return $this->container->get(Environment::class)->render($template, $parameters);
    }

    protected function redirectToRoute(string $route, array $parameters = []): RedirectResponse
    {
        $urlGenerator = $this->container->get(UrlGeneratorInterface::class);
        $url = $urlGenerator->generate($route, $parameters);

        return new RedirectResponse($url);
    }

    protected function logUserVisit(string $nomRoute, ?array $params = null): void
    {
        $session = $this->container->get(SessionInterface::class);
        $em = $this->container->get(EntityManagerInterface::class);

        $idUtilisateur = $session->get('user_id');
        $utilisateur = ($idUtilisateur !== '-') ? $em->getRepository(User::class)->find($idUtilisateur) : null;
        $utilisateurNom = $utilisateur ? $utilisateur->getNomUtilisateur() : '-';
        $page = $em->getRepository(PageHff::class)->findPageByRouteName($nomRoute);
        $machine = gethostbyaddr($_SERVER['REMOTE_ADDR']) ?? $_SERVER['REMOTE_ADDR'];

        if ($page) {
            $log = new UserLogger();
            $log->setUtilisateur($utilisateurNom);
            // $log->setNomPage($page->getNom());
            $log->setParams($params ?: null);
            $log->setUser($utilisateur);
            $log->setPage($page);
            $log->setMachineUser($machine);

            $em->persist($log);
            $em->flush();
        }
    }

    public function verifierSessionUtilisateur(): ?RedirectResponse
    {
        $session = $this->container->get(SessionInterface::class);

        if (!$session->has('user_id')) {
            return $this->redirectToRoute("security_login_form");
        }

        return null;
    }
    public function utilisateurConnecter(): ?User
    {
        $session = $this->container->get(SessionInterface::class);
        $em = $this->container->get(EntityManagerInterface::class);

        $idUtilisateur = $session->get('user_id');
        return $em->getRepository(User::class)->find($idUtilisateur);
    }

    protected function SessionDestroy()
    {
        // Commence la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Supprime l'utilisateur de la session
        unset($_SESSION['user']);

        // Détruit la session
        session_destroy();

        // Réinitialise toutes les variables de session
        session_unset();

        // Redirige vers la page d'accueil
        header("Location: /Hffintranet/");

        // Ferme l'écriture de la session pour éviter les problèmes de verrouillage
        session_write_close();

        // Arrête l'exécution du script pour s'assurer que rien d'autre ne se passe après la redirection
        exit();
    }
}



