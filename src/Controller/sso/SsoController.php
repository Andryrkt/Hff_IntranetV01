<?php

namespace App\Controller\sso;

use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Service\security\JwtService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SsoController extends Controller
{
    use lienGenerique;

    /**
     * @Route("/sso/connect", name="sso_connect", methods={"GET"})
     */
    public function connectSSO(Request $request)
    {
        // 1. Récupérer les informations de session de l'utilisateur connecté
        $userInfo = $this->getSessionService()->get('user_info');


        // Sécurité : si la session a expiré, on redirige vers l'écran de login PHP normal
        if (!$userInfo) {
            return $this->redirectToRoute('security_signin');
        }

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
        ];

        $jwtService = new JwtService();
        $token = $jwtService->encode($payload, 3600);

        $sessionService = $this->getSessionService();

        $ssoId = "sso_" . $userInfo['id'] . "_" . time();
        $sessionService->set($ssoId, $token);

        header("Content-type: application/json");
        echo json_encode([
            'token' => $token,
            'logout_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/logout"),
        ]);
    }

    /**
     * @Route("/sso/logout", name="sso_logout", methods={"GET"})
     */
    public function logoutSSO()
    {
        $sessionService = $this->getSessionService();
        $sessionService->clear();
    }

    /**
     * @Route("/sso/validate", name="sso_validate", methods={"POST"})
     */
    public function validateSSO(Request $request): JsonResponse
    {
        $token = $request->request->get('token');

        if (!$token) {
            return new JsonResponse(["error" => "Token manquant"], Response::HTTP_UNAUTHORIZED);
        }

        $jwtService = new JwtService();
        $payload = $jwtService->decode($token);

        if (!$payload) {
            return new JsonResponse(["error" => "Invalid token"], Response::HTTP_UNAUTHORIZED);
        }

        $payload = [
            'id'                   => $payload['id'] ?? null,
            'username'             => $payload['username'] ?? null,
            'email'                => $payload['email'] ?? null,
            'fullname'             => $payload['fullname'] ?? null,
            'roles'                => $payload['roles'] ?? [],
            'profil_id'            => $payload['profil_id'] ?? null,
            'societe_code'         => $payload['societe_code'] ?? null,
            'default_agence_code'  => $payload['default_agence_code'] ?? null,
            'default_service_code' => $payload['default_service_code'] ?? null,
        ];

        return new JsonResponse($payload);
    }

    /**
     * @Route("/sso/annuaire", name="sso_annuaire", methods={"GET"})
     */
    public function redirectAnnuaire(Request $request)
    {
        $userInfo = $this->getSessionService()->get('user_info');

        if (!$userInfo)
            return $this->redirectToRoute('security_signin');

        global $container;
        $securityService = $container->get('security.service');

        $permissions = $securityService->getPermissions();

        $payload = [
            'id'                    => $userInfo['id'] ?? null,
            'username'              => $userInfo['username'] ?? null,
            'email'                 => $userInfo['email'] ?? null,
            'fullname'              => $userInfo['fullname'] ?? null,
            'roles'                 => $userInfo['roles'] ?? [],
            'profil_id'             => $userInfo['profil_id'] ?? null,
            'societe_code'          => $userInfo['societe_code'] ?? null,
            'default_agence_code'   => $userInfo['default_agence_code'] ?? null,
            'default_service_code'  => $userInfo['default_service_code'] ?? null,
            'permissions'           => $permissions,
            'url_logout'            => $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/logout")
        ];

        $jwtService = new JwtService();
        $token = $jwtService->encode($payload, 3600);

        $reactAppUrl = $_ENV['REACT_ANNUAIRE_URL'];

        $redirectUrl = $reactAppUrl . '?token=' . urlencode($token);

        return new RedirectResponse($redirectUrl);
    }
}
