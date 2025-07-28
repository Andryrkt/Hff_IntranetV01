<?php

namespace App\Controller\security;

use App\Controller\AbstractController;
use App\Entity\admin\utilisateur\User;
use App\Service\ldap\MyLdapService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private MyLdapService $ldapService;

    private SessionInterface $session;

    private LoggerInterface $logger;

    private EntityManagerInterface $em;

    public function __construct(
        ContainerInterface $container,
        MyLdapService $ldapService,
        SessionInterface $session,
        LoggerInterface $logger,
        EntityManagerInterface $em
    ) {
        parent::__construct($container);
        $this->ldapService = $ldapService;
        $this->session = $session;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * @Route("/", name="security_login_form")
     */
    public function loginForm(): Response
    {
        $notification = $this->session->get('notification');
        $this->session->remove('notification');
        // $this->getUserLogger()->logUserVisit('security_login');

        return new Response($this->render('security/login.html.twig', [
            'notification' => $notification,
        ]));
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        // Si l'un des champs est vide
        if (! $username || ! $password) {
            $this->logger->warning("Tentative de connexion avec des champs vides.", ['ip' => $request->getClientIp()]);
            $this->session->set('notification', 'Veuillez remplir tous les champs.');
            $this->getUserLogger()->logUserVisit('security_login'); // historisation du page visité par l'utilisateur

            return $this->redirectToRoute('security_login_form');
        }

        // Si l'authentification échoue
        if (! $this->ldapService->authenticate($username, $password, '@fraise.hff.mg')) {
            $this->logger->warning("Échec de connexion", [ 'username' => $username, 'ip' => $request->getClientIp()]);
            $this->session->set('notification', 'Vérifier les informations de connexion, veuillez saisir le nom d\'utilisateur et le mot de passe de votre session Windows');
            $this->getUserLogger()->logUserVisit('security_login'); // historisation du page visité par l'utilisateur

            return $this->redirectToRoute('security_login_form');
        }

        // Si c'est OK
        $user = $this->em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $username]);

        $userId = ($user) ? $user->getId() : '-';

        $this->session->set('user_id', $userId);
        // $this->session->set('notification', 'Authentification réussie !');
        $this->logger->info("Connexion réussie pour l'utilisateur : $username");

        return $this->redirectToRoute('home_home');
    }

    /**
    * @Route("/logout", name="security_deconnexion")
    *
    * @return void
    */
    public function deconnexion()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        return $this->getSessionService()->destroySession();
    }
}
