<?php

namespace App\Api\ddp;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use App\Controller\Controller;
use App\Controller\Traits\ddp\DdpTrait;
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
    use DdpTrait;

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
        // $numComandes = $this->demandePaiementRepository->getnumCde();
        //     $excludedCommands = $this->changeStringToArray($numComandes);
        //     $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur, $typeId, $excludedCommands);

        // $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        $numCdes = $this->demandePaiementModel->getCommandeReceptionnee($numeroFournisseur);


        $numCde = array_map(fn($el) => ['label' => $el, 'value' => $el], $numCdes);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

        $numFacs = $this->demandePaiementModel->getFactureNonReglee($numeroFournisseur);
        $numFacString = TableauEnStringService::TableauEnString(',', $numFacs);

        $listeGcot = $this->demandePaiementModel->findListeGcot($numeroFournisseur, $numCdesString, $numFacString);

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
     * @Route("/api/montant-facture/{numeroFournisseur}/{numFacture}/{typeId}", name="api_montant_factures")
     */
    public function montantFacture(string $numeroFournisseur, string $numFacture, int $typeId)
    {
        $factureArray = explode(',', $numFacture);
        // $numComandes = $this->demandePaiementRepository->getnumCde();
        //     $excludedCommands = $this->changeStringToArray($numComandes);
        //     $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur, $typeId, $excludedCommands);
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);

        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
        $numFacString = TableauEnStringService::TableauEnString(',', $factureArray);

        $montants = $this->demandePaiementModel->getMontantFacGcot($numeroFournisseur, $numCdesString, $numFacString);

        if ($montants[0] == null) {
            $montants[0] = 0.00;
        }
        // dd($montants);
        header("Content-type:application/json");
        echo json_encode($montants);
    }

    /**
     * @Route("/api/montant-commande/{numCde}", name="api_montant_commande")
     */
    public function montantCommande(string $numCde)
    {

        $numcdeArray = explode(',', $numCde);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numcdeArray);
        $montantCde = $this->demandePaiementModel->getMontantCdeAvance($numCdesString);

        if ($montantCde[0]['montantcde'] == null) {
            $montantCde[0]['montantcde'] = 0.00;
        }

        header("Content-type:application/json");
        echo json_encode($montantCde);
    }

    private function changeStringToArray(array $input): array
    {

        $resultCde = [];

        foreach ($input as $item) {
            $decoded = json_decode($item, true); // transforme la string en tableau
            if (is_array($decoded)) {
                $resultCde = array_merge($resultCde, $decoded);
            }
        }

        return $resultCde;
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


    /**
     * @Route("/api/numero-libelle-fournisseur", name="api_numero_libelle_fournisseur")
     */
    public function fournisseur()
    {
        $fournisseurs = $this->demandePaiementModel->getFournisseur();

        header("Content-type:application/json");
        echo json_encode($fournisseurs);
    }
}
