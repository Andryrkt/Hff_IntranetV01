<?php

namespace App\Controller\sso;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\security\JwtService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SsoController extends Controller
{
    /**
     * @Route("/sso/annuaire", name="sso_annuaire", methods={"GET"})
     */
    public function redirectAnnuaire(Request $request)
    {
        // 1. Récupérer les informations de session de l'utilisateur connecté
        $userInfo = $this->getSessionService()->get('user_info');

        // Sécurité : si la session a expiré, on redirige vers l'écran de login PHP normal
        if (!$userInfo) {
            return $this->redirectToRoute('security_signin');
        }

        // On peut appeler le security service (déjà disponible globalement via le container) pour récupérer les droits
        global $container;
        $securityService = $container->get('security.service');
        
        // Obtenir toutes les permissions globales ou relatives à l'utilisateur ciblé si possible
        $permissions = clone $securityService->getPermissions();

        // 2. Préparer le payload du token JWT (tout ce que React doit savoir)
        $payload = [
            'id'                   => $userInfo['id'] ?? null,
            'username'             => $userInfo['username'] ?? null,
            'email'                => $userInfo['email'] ?? null,
            'fullname'             => $userInfo['fullname'] ?? null,
            'roles'                => $userInfo['roles'] ?? [],
            'profil_id'            => $userInfo['profil_id'] ?? null,
            'societe_code'         => $userInfo['societe_code'] ?? null,
            'default_agence_code'  => $userInfo['default_agence_code'] ?? null,
            'default_service_code' => $userInfo['default_service_code'] ?? null,
            // Les permissions existantes dans l'intranet:
            'permissions'          => $permissions
        ];

        // 3. Génération du JWT via notre JwtService nouvellement crée
        $jwtService = new JwtService();
        // Le token est valable 1 heure (3600 secondes)
        // Le front React a 1 heure pour faire ses requêtes, s'il a besoin que ça dure, 
        // vous pourrez augmenter cette valeur à 8 heures (28800) par exemple.
        $token = $jwtService->encode($payload, 3600); 

        // 4. Déterminer l'URL de votre application React
        // Idéalement à mettre dans dans votre fichier .env 
        // ex: REACT_ANNUAIRE_URL="http://votre-serveur-react.fr"
        $reactAppUrl = $_ENV['REACT_ANNUAIRE_URL'] ?? 'http://localhost:3000';
        
        // 5. Ordre de redirection immédiate (Status 302 HTTP)
        $redirectUrl = $reactAppUrl . '?token=' . urlencode($token);

        return new RedirectResponse($redirectUrl);
    }
}
