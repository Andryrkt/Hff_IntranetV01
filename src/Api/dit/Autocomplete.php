<?php

namespace App\Api\dit;

use App\Controller\Controller;
use App\Model\dit\DitAutocompleteModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Autocomplete extends Controller
{

    private DitAutocompleteModel $ditAutocompleteModel;

    public function __construct()
    {
        parent::__construct();
        $this->ditAutocompleteModel = new DitAutocompleteModel();
    }

    /**
     * @Route("/api/autocomplete/all-client", name="api_autocomplete_all_client")
     */
    public function autocompleteAllClient()
    {
        $results = [];

        // Recherchez les clients par nom dans votre base de données
        $clients = $this->ditAutocompleteModel->recupAllClientExterne();

        $results = array_map(function ($client) {
            return [
                'num_client' => $client['cbse_numcli'],
                'nom_client' => $client['cbse_nomcli'],
            ];
        }, $clients);

        header("Content-type:application/json");

        echo json_encode($results);
    }

    // /**
    //  * @Route("/autocomplete/nom-client", name="autocomplete_nom_client")
    //  *
    //  * @param Request $request
    //  */
    // public function autocompleteNomClient(Request $request)
    // {
    //     $term = $request->query->get('term');
    //     $results = [];

    //     if ($term) {
    //         // Recherchez les clients par nom dans votre base de données
    //         $nomClients = $this->ditAutocompleteModel->recupNomClientExterne($term);
    //         // Recherchez les clients par numéro dans votre base de données
    //         $numClients = $this->ditAutocompleteModel->recupNumeroClientExterne($term);

    //         $results = array_map(function ($nomClient, $numClient) {
    //             return [
    //                 'value' => $nomClient['cbse_nomcli'],
    //                 'label' => $numClient['cbse_numcli'] .'-'. $nomClient['cbse_nomcli']
    //             ];
    //         }, $nomClients, $numClients);
    //     }

    //     header("Content-type:application/json");

    //     echo json_encode($results);
    // }


    // /**
    //  * @Route("/autocomplete/numero-client", name="autocomplete_numero_client")
    //  *
    //  * @param Request $request
    //  */
    // public function autocompleteNumeroClient(Request $request)
    // {
    //     $term = $request->query->get('term');
    //     $results = [];

    //     if ($term) {
    //         // Recherchez les clients par numéro dans votre base de données
    //         $numClients = $this->ditAutocompleteModel->recupNumeroClientExterne($term);

    //         $results = array_map(function ($client) {
    //             return [
    //                 'value' => $client['cbse_numcli'],
    //                 'label' => $client['cbse_numcli']
    //             ];
    //         }, $numClients);
    //     }

    //     header("Content-type:application/json");

    //     echo json_encode($results);
    // }
}
