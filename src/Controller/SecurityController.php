<?php

namespace App\Controller;

use App\Service\ldap\MyLdapService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class SecurityController extends AbstractController
{
    private MyLdapService $ldapService;
    private SessionInterface $session;

    public function __construct(
        MyLdapService $ldapService,
        SessionInterface $session,
        Environment $twig
    ) {
        parent::__construct($twig);
        $this->ldapService = $ldapService;
        $this->session = $session;
    }

    /**
     * @Route("/", name="security_login_form")
     */
    public function loginForm(): Response
    {
        return new Response($this->render('security/login.html.twig'));
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(Request $request): Response
    {
        
        $username = $request->request->get('username');
        $password = $request->request->get('password');
dd($username, $password);
        if ($this->ldapService->authenticate($username, $password, '@fraise.hff.mg')) {
            $this->session->set('user', $username);
            return new Response("Authentification réussie !");
        }

        return new Response("Identifiants incorrects.", 403);
    }
}

