<?php

namespace App\Api\magasin\devis;

use App\Controller\Controller;
use App\Entity\magasin\devis\PointageRelance;
use App\Entity\magasin\bc\BcMagasin;
use App\Entity\magasin\devis\DevisMagasin;
use App\Constants\Magasin\Devis\PointageRelanceStatutConstant;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Form\magasin\devis\MotifStopRelanceType;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/api/devis/motif-stop-form", name="api_devis_motif_stop_form", methods={"GET"})
     */
    public function renderMotifForm()
    {
        $form = $this->getFormFactory()->create(MotifStopRelanceType::class);

        return new JsonResponse([
            'html' => $this->getTwig()->render('magasin/devis/shared/_motif_stop_modal.html.twig', [
                'form' => $form->createView(),
            ])
        ]);
    }

    /**
     * @Route("/api/stop-relance/{numeroDevis}", name="devis_magasin_stop_relance", methods={"POST"})
     */
    public function stopRelance(Request $request, string $numeroDevis)
    {
        try {
            $body = json_decode($request->getContent(), true);
            $motif = $body['motif'] ?? null;
            $listeDevisMagasinModel = new ListeDevisMagasinModel();
            $success = $listeDevisMagasinModel->stopRelance($numeroDevis, $motif);

            $newStatuts = [];
            $relanceClient = false;

            if ($success) {
                $newStatuts = $listeDevisMagasinModel->getStatutRelance($numeroDevis);

                // On récupère les infos du devis pour recalculer les droits d'affichage
                $sql = "SELECT statut_dw, statut_bc, stop_progression_global 
                        FROM devis_soumis_a_validation_neg 
                        WHERE numero_devis = '$numeroDevis' 
                        AND numero_version = (SELECT MAX(numero_version) FROM devis_soumis_a_validation_neg WHERE numero_devis = '$numeroDevis')";
                $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
                $res = $stmt->executeQuery();
                $devisData = $res->fetchAssociative();

                if ($devisData) {
                    $hasARelancer = in_array(PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_A_RELANCER, [
                        $newStatuts['statut_relance_1'] ?? null,
                        $newStatuts['statut_relance_2'] ?? null,
                        $newStatuts['statut_relance_3'] ?? null
                    ]);

                    $relanceClient = ($devisData['statut_dw'] === DevisMagasin::STATUT_ENVOYER_CLIENT
                        && $devisData['statut_bc'] === BcMagasin::STATUT_EN_ATTENTE_BC
                        && $hasARelancer
                        && !(bool)$devisData['stop_progression_global']);
                }
            }

            return new JsonResponse([
                'success' => $success,
                'message' => $success ? "L'opération sur le devis n°$numeroDevis a été effectuée avec succès" : "Erreur lors de l'opération",
                'statuts' => $newStatuts,
                'relanceClient' => $relanceClient
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
