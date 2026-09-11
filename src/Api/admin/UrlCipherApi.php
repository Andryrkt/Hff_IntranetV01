<?php

namespace App\Api\admin;

use App\Controller\Controller;
use App\Service\Admin\UrlIdCipher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UrlCipherApi extends Controller
{
    /**
     * @Route("/api/admin/decrypt-token/{token}", name="api_admin_decrypt_token", methods={"GET"})
     *
     * @return void
     */
    public function decryptToken(string $token)
    {
        try {
            if (!$this->estAdmin()) {
                return new JsonResponse([
                    'error'   => true,
                    'message' => 'Vous n\'avez pas les droits pour effectuer cette action.',
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            $decrypted = (new UrlIdCipher())->decrypt($token);

            if (empty($decrypted) && $decrypted !== 0) {
                return new JsonResponse([
                    'error'   => true,
                    'message' => 'Token invalide.',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'message' => 'Token décrypté avec succès',
                'data'    => $decrypted
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors du décryptage du token: ' . $e->getMessage(),
                'data'    => []
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
