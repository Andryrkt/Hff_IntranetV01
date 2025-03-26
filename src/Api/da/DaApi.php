<?php

namespace App\Api\da;

use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\dom\Rmq;
use App\Controller\Controller;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\Personnel;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\mutation\Mutation;
use App\Model\da\DaModel;
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
     * @Route("/demande-appro/autocomplete/all-designation-sans", name="autocomplete_all_designation_sans")
     *
     * @return void
     */
    public function autocompleteAllDesignationSans()
    {
        $daModel = new DaModel;
        $data = $daModel->getAllDesignationSans();

        header("Content-type:application/json");

        echo json_encode($data);
    }
}
