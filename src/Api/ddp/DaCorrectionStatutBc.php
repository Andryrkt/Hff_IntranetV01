<?php

namespace App\Controller\Api;

use App\Controller\Controller;
use App\Entity\da\DaAfficher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DaCorrectionStatutBc extends Controller
{
    /**
     * @Route("/api/mettre-a-jour-statut-bc-da", name="api_mettre_a_ajour_statut_bc_da")
     */
    public function correctionStatutBc(Request $request)
    {
        try {
            // $this->verifierSessionUtilisateur();
            $data = json_decode($request->getContent(), true);
            $numCde = $data['id'];

            // recupération des lignes de DA Afficher
            $daAffichers = $this->getEntityManager()->getRepository(DaAfficher::class)->findBy(['numeroCde' => $numCde]);

            foreach ($daAffichers as $daAfficher) {
                $daAfficher->setEstCorrectionStatutBc(true);
                $this->getEntityManager()->persist($daAfficher);
            }
            $this->getEntityManager()->flush();
            return new JsonResponse([
                'success' => true,
                'message' =>  " table daAfficher mis à jour",
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la changement statut du BC',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
