<?php

namespace App\Api\ddp;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InfoFournisseurApi extends Controller
{
    private $demandePaiementModel;
    private $cdeFnrRepository;
    private $demandePaiementRepository;

    public function __construct()
    {
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->cdeFnrRepository = self::$em->getRepository(CdefnrSoumisAValidation::class);
        $this->demandePaiementRepository  = self::$em->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/api/info-fournisseur-ddp", name="api_info_fournisseur_ddp")
     */
    public function fournisseurInfo()
    {
        $results = [];

        $infoFournisseur = $this->demandePaiementModel->recupInfoFournissseur();

        $results = array_map(function ($fournisseur) {
            return [
                'num_fournisseur' => $fournisseur['num_fournisseur'],
                'nom_fournisseur' => $fournisseur['nom_fournisseur'],
                'devise' => $fournisseur['devise'],
                'mode_paiement' => $fournisseur['mode_paiement'],
                'rib' => $fournisseur['rib']
            ];
        }, $infoFournisseur);

        header("Content-type:application/json");

        echo json_encode($results);
    }

    /**
     * @Route("/api/num-cde-frn/{numeroFournisseur}/{typeId}", name="api_num_cde_frn")
     */
    public function numeroCommandeFournisseur($numeroFournisseur, $typeId)
    {
        
        // $nbrLigne = $this->demandePaiementRepository->CompteNbrligne($numeroFournisseur);
        
        // if ($nbrLigne <= 0) {
            $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur, $typeId);
            if($typeId == 1) {
                
            }
            $numCde = array_map(fn($el) => ['label' => $el, 'value' => $el], $numCdes);
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
            
            $listeGcot = $this->demandePaiementModel->findListeGcot($numeroFournisseur, $numCdesString);

            $data = [
                'numCdes' => $numCde,
                'listeGcot' => $listeGcot
            ];
            header("Content-type:application/json");
            echo json_encode($data);
        // } else {
        //     header("Content-type:application/json");
        //     echo json_encode(
        //         [
        //             'succes' => false,
        //             'message' => 'une demande de paiement a été déjà envoyer pour validation pour ce numero fournisseur'
        //         ]
        //     );
        // }
    }

    /**
     * @Route("/api/liste-doc/{numeroDossier}", name="api_liste_doc")
     *
     * @param string $numeroDossier
     * @return void
     */
    public function listeDoc(string $numeroDossier)
    {
        $dossiers = $this->demandePaiementModel->findListeDoc($numeroDossier);

        $response = new JsonResponse($dossiers);
        $response->send();
    }


/**
 * @Route("/api/recuperer-fichier", name="api_recuperer_fichier")
 */
public function recupererFichier(Request $request)
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $path = urldecode($request->query->get('path'));
    $basePath = '\\\\192.168.0.15\\GCOT_DATA\\TRANSIT';
    $chemin = $basePath . DIRECTORY_SEPARATOR . $path;

    header('Content-Type: application/json');

    if (!file_exists($chemin)) {
        echo json_encode([
            'success' => false,
            'message' => "❌ Fichier introuvable : $chemin"
        ]);
        exit;
    }

    if (!is_readable($chemin)) {
        echo json_encode([
            'success' => false,
            'message' => "🚫 Fichier non lisible : $chemin"
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => "✅ Fichier accessible",
        'chemin' => $chemin
    ]);
    exit;
}




// Pour éviter les injections de chemin
private function sanitize(string $filename): string
{
    return basename($filename); // Supprime les ../ ou chemins absolus
}


}