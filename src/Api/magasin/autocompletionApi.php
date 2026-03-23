<?php

namespace App\Api\magasin;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Model\magasin\lcfnp\ListeCdeFrnNonPlacerModel;


class AutocompletionApi extends Controller
{
    /**
     * @Route("/api/designation-fetch/{designation}", name="api_designation_fetch")
     *
     * @return void
     */
    public function autocompletionDesignation($designation)
    {

        if (!empty($designation)) {
            $magasinModel = new MagasinListeOrATraiterModel;
            $designations = $magasinModel->recupereAutocompletionDesignation($designation);
        } else {
            $designations = [];
        }

        header("Content-type:application/json; charset=utf-8");
        echo json_encode($designations, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }


    /**
     * @Route("/api/refpiece-fetch/{refPiece}", name="api_refpiece_fetch")
     *
     * @return void
     */
    public function autocompletionRefPiece($refPiece)
    {
        if (!empty($refPiece)) {
            $magasinModel = new MagasinListeOrATraiterModel;
            $refPieces = $magasinModel->recuperAutocompletionRefPiece($refPiece);
        } else {
            $refPieces = [];
        }


        header("Content-type:application/json; charset=utf-8");
        echo json_encode($refPieces, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }
    /**
     * @Route("/api/frs-non-place-fetch", name="api_frs_non_place_fetch")
     *
     * @return void
     */
    public function autocompletionFrs()
    {
        $frsNonPlace = new ListeCdeFrnNonPlacerModel();
        $data = $frsNonPlace->fournisseurIrum();

        header("Content-type:application/json; charset=utf-8");
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * @Route("/api/code-client-fetch", name="api_code_client_fetch")
     *
     * @return void
     */
    public function autocompletionCodeClient()
    {
        try {
            $listeDevisMagasinModel = new ListeDevisMagasinModel();
            $data = $listeDevisMagasinModel->getCodeLibelleClient();

            // Vérifier que les données sont valides
            if (!is_array($data)) {
                throw new \Exception("Les données retournées ne sont pas un tableau valide");
            }

            // Nettoyer les données avant l'encodage JSON
            $cleanedData = $this->cleanDataForJson($data);

            header("Content-type:application/json; charset=utf-8");
            echo json_encode($cleanedData, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner un tableau vide avec un message d'erreur
            header("Content-type:application/json; charset=utf-8");
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Erreur lors du chargement des données: ' . $e->getMessage(),
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Nettoie les données pour l'encodage JSON
     */
    private function cleanDataForJson($data)
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->cleanDataForJson($value);
            }
            return $cleaned;
        } elseif (is_string($data)) {
            // Nettoyer la chaîne pour éviter les problèmes d'encodage
            $cleaned = mb_convert_encoding($data, 'UTF-8', 'auto');
            // Supprimer les caractères de contrôle non imprimables
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
            return $cleaned;
        }
        return $data;
    }
}
