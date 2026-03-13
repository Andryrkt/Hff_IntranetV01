<?php

namespace App\Api\da\CmdFrn;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MesDaATraiter
{
    /**
     * @Route("/api/da/mes-da-a-traiter", name="da_mes_da_a_traiter", methods={"POST"})
     */
    public function mesDaATraiter(Request $request)
    {
        try {
            // $this->verifierSessionUtilisateur();
            $data = json_decode($request->getContent(), true);
            $codeAgenceServiceUser = $data['codeAgenceServiceUser'] ?? [];
            $codeAgenceUser = $codeAgenceServiceUser[0];
            $codeServiceUser = $codeAgenceServiceUser[1];


            if (empty($codeAgenceUser) || empty($codeServiceUser)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Aucun code agence ou code service fourni.',
                ], 400);
            }


            if()

            return new JsonResponse([
                'success' => true,
                'message' => " Les DA à traiter pour l'utilisateur : $codeAgenceUser - $codeServiceUser sont affiché avec succès"
            ]);
        } catch (\Throwable $e) {
            if (ob_get_length() > 0) {
                ob_clean();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la transmission des demandes BAP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
