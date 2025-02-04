<?php

namespace App\Controller;

use App\Model\LdapModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityController extends AbstractController
{
    private LdapModel $ldapModel;
    private SessionInterface $session;

    // Grâce à l'autowiring, le conteneur injectera automatiquement LdapModel et SessionInterface
    public function __construct(LdapModel $ldapModel, SessionInterface $session, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->ldapModel = $ldapModel;
        $this->session = $session;
        parent::__construct($container);
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function loginForm(): Response
    {
        // Vous pouvez créer un template Twig pour le formulaire de connexion
        return new Response($this->twig->render('security/login.html.twig'));
    }

    /**
     * Traite la soumission du formulaire de connexion
     */
    public function login(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        // Utilisation de votre service LDAP pour vérifier les identifiants
        if ($this->ldapModel->userConnect($username, $password)) {
            // En cas de succès, stockez par exemple le nom d'utilisateur dans la session
            $this->session->set('user', $username);
            return new Response("Authentification réussie !");
        } else {
            return new Response("Identifiants incorrects.", 403);
        }
    }
}
