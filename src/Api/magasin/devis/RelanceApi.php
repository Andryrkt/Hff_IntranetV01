<?php

namespace App\Api\magasin\devis;

use App\Controller\Controller;
use App\Entity\magasin\devis\PointageRelance;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RelanceApi extends Controller
{
    /**
     * @Route("/api/devis/{numeroDevis}/relances")
     *
     * @param integer $numeroDevis
     * @return void
     */
    public function relance(int $numeroDevis)
    {
        $relances = $this->getEntityManager()->getRepository(PointageRelance::class)->findBy(['numeroDevis' => $numeroDevis], ['dateDeRelance' => 'DESC']);
        $response = [];
        foreach ($relances as $relance) {
            $response[] = [
                'numeroRelance' => $relance->getNumeroRelance(),
                'dateRelance' => $relance->getDateDeRelance()->format('d/m/Y'),
                'societe' => $relance->getSociete(),
                'agence' => $relance->getAgence(),
                'utilisateur' => $relance->getUtilisateur(),
                'numeroDevis' => $relance->getNumeroDevis()
            ];
        }
        echo json_encode($response);
        exit;
    }

    /**
     * @Route("/api/stop-relance/{numeroDevis}", name="devis_magasin_stop_relance", methods={"POST"})
     */
    public function stopRelance(string $numeroDevis)
    {
        try {
            $listeDevisMagasinModel = new ListeDevisMagasinModel();
            $success = $listeDevisMagasinModel->stopRelance($numeroDevis);
            return new JsonResponse([
                'success' => $success,
                'message' => "la relance du devis n°$numeroDevis a été stopé avec succés"
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
