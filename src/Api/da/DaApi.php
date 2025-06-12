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
        $daModel = new DaModel;
        $data = $daModel->getTheSousFamille($code);

        $result = [];
        foreach ($data as $sfm) {
            $result[] = [
                'value' => $sfm['code'],
                'text' => $sfm['libelle'],
            ];
        }

        header("Content-type:application/json");

        echo json_encode($result);
    }

    /**
     * @Route("/demande-appro/autocomplete/all-designation/{famille}/{sousfamille}", name="autocomplete_all_designation")
     *
     * @return void
     */
    public function autocompleteAllDesignation($famille, $sousfamille)
    {
        $daModel = new DaModel;
        $data = $daModel->getAllDesignation($famille, $sousfamille);

        header("Content-type:application/json");

        echo json_encode($data);
    }

    /**
     * @Route("/demande-appro/autocomplete/all-fournisseur", name="autocomplete_all_fournisseur")
     *
     * @return void
     */
    public function autocompleteAllFournisseur()
    {
        $daModel = new DaModel;
        $data = $daModel->getAllFournisseur();

        header("Content-type:application/json");

        echo json_encode($data);
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

            $dit = self::$em->getRepository(DemandeIntervention::class)->find($data['id']);
            if (!$dit) {
                echo json_encode(['error' => 'DemandeIntervention non trouvée']);
                exit;
            }

            $statut = self::$em->getRepository(DemandeAppro::class)
                ->getStatut($dit->getNumeroDemandeIntervention());

            if ($statut === null) {
                echo json_encode(['statut' => null, 'message' => 'Aucun statut trouvé']);
            } else {
                echo json_encode(['statut' => $statut]);
            }

            exit;
        }
    }
}
