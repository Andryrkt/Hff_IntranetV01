<?php

namespace App\Api\da;

use App\Model\da\DaModel;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DaApi extends Controller
{
    use FormatageTrait;

    /**
     * @Route("/api/demande-appro/sous-famille/{code}", name="fetch_sous_famille", methods={"GET"})
     *
     * @return void
     */
    public function fetchSousFamille($code)
    {
        try {
            $daModel = new DaModel;
            $data = $daModel->getTheSousFamille($code);

            $result = [];
            foreach ($data as $sfm) {
                $result[] = [
                    'value' => $sfm['code'],
                    'text' => $sfm['libelle'],
                ];
            }

            // Nettoyer les données avant l'encodage JSON
            $cleanedData = $this->cleanDataForJson($result);

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
     * @Route("/demande-appro/autocomplete/all-designation", name="autocomplete_all_designation_default")
     *
     * @return void
     */
    public function autocompleteAllDesignationDefault()
    {
        try {
            $daModel = new DaModel;
            // Utiliser des valeurs par défaut ou récupérer toutes les désignations
            $data = $daModel->getAllDesignationZST('-', '-');

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
     * @Route("/demande-appro/autocomplete/all-designation-zst/{famille}/{sousfamille}", name="autocomplete_all_designation_zst")
     *
     * @return void
     */
    public function autocompleteAllDesignationZST($famille, $sousfamille)
    {
        try {
            $daModel = new DaModel;

            // Gérer le cas spécial où les paramètres sont "-"
            if ($famille === '-' && $sousfamille === '-') {
                // Rediriger vers la route par défaut ou gérer différemment
                $this->autocompleteAllDesignationDefault();
                return;
            }

            $data = $daModel->getAllDesignationZST($famille, $sousfamille);

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
     * @Route("/demande-appro/autocomplete/all-designation-zdi", name="autocomplete_all_designation_zdi")
     *
     * @return void
     */
    public function autocompleteAllDesignationZDI()
    {
        try {
            $daModel = new DaModel;
            $data = $daModel->getAllDesignationZDI();

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
     * @Route("/demande-appro/autocomplete/all-fournisseur", name="autocomplete_all_fournisseur")
     *
     * @return void
     */
    public function autocompleteAllFournisseur()
    {
        try {
            $daModel = new DaModel;
            $data = $daModel->getAllFournisseur();

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
     * @Route("/api/recup-statut-da", name="api_recup_statut_da")
     *
     * @return void
     */
    public function recupStatutDaPourDitSelectionner(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $em = $this->getEntityManager();
            $dit = $em->getRepository(DemandeIntervention::class)->find($data['id']);
            if (!$dit) {
                echo json_encode(['error' => 'DemandeIntervention non trouvée']);
                exit;
            }

            $statut = $em->getRepository(DemandeAppro::class)
                ->getStatut($dit->getNumeroDemandeIntervention());

            if ($statut === null) {
                echo json_encode(['statut' => null, 'message' => 'Aucun statut trouvé']);
            } else {
                echo json_encode(['statut' => $statut]);
            }

            exit;
        }
    }

    /**
     * @Route("/api/recup-all-code-centrale", name="api_recup_all_code_centrale")
     *
     * @return void
     */
    public function recupAllCodeCentrale()
    {
        try {
            $daModel = new DaModel;
            $data = $daModel->getAllCodeCentrale();

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
