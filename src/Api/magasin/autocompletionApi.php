<?php

namespace App\Api\magasin;

use App\Controller\Controller;
use App\Model\magasin\lcfnp\ListeCdeFrnNonPlacerModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;


class AutocompletionApi extends Controller
{
      /**
     * @Route("/designation-fetch/{designation}")
     *
     * @return void
     */
    public function autocompletionDesignation($designation)
    {

        if(!empty($designation)){
            $magasinModel = new MagasinListeOrATraiterModel;
            $designations = $magasinModel->recupereAutocompletionDesignation($designation);
        } else {
            $designations = [];
        }

        header("Content-type:application/json");

        echo json_encode($designations);
    }


    /**
     * @Route("/refpiece-fetch/{refPiece}")
     *
     * @return void
     */
    public function autocompletionRefPiece($refPiece)
    {
        if(!empty($refPiece)){
            $magasinModel = new MagasinListeOrATraiterModel;
            $refPieces = $magasinModel->recuperAutocompletionRefPiece($refPiece);
        } else {
            $refPieces = [];
        }


        header("Content-type:application/json");

        echo json_encode($refPieces);
    }
    /**
     * @Route("/frs-non-place-fetch")
     *
     * @return void
     */
    public function autocompletionFrs()
    {
        $frsNonPlace = new ListeCdeFrnNonPlacerModel();
        $data = $frsNonPlace->fournisseurIrum();

        header("Content-type:application/json");

        echo json_encode($data);
    }
}