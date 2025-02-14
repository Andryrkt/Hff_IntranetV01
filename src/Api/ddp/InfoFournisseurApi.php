<?php

namespace App\Api\ddp;

use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/api/num-cde-frn/{numeroFournisseur}", name="api_num_cde_frn")
     */
    public function numeroCommandeFournisseur($numeroFournisseur)
    {
        
        $nbrLigne = $this->demandePaiementRepository->CompteNbrligne($numeroFournisseur);
        
        if ($nbrLigne <= 0) {
            $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur);
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
            
            $listeGcot = $this->demandePaiementModel->findListeGcot($numeroFournisseur, $numCdesString);

            $data = [
                'numCdes' => $numCdes,
                'listeGcot' => $listeGcot
            ];
            header("Content-type:application/json");
            echo json_encode($data);
        } else {
            header("Content-type:application/json");
            echo json_encode(
                [
                    'succes' => false,
                    'message' => 'une demande de paiement a été déjà envoyer pour validation pour ce numero fournisseur'
                ]
            );
        }
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
     * @Route("/api/recuperer-fichier/{nomFichier}", name="api_recuper_fichier_nom_fichier")
     */
    public function recupererFichier($nomFichier)
    {
        $nomFichier = basename($nomFichier);
        $cheminFichier = '\\\\192.168.0.15\\GCOT_DATA\\TRANSIT\\DD1297A24\\' . $nomFichier;
        dd(file_exists($cheminFichier));
        if (!file_exists($cheminFichier)) {
            http_response_code(404);
            echo "Fichier introuvable.";
            exit;
        }

        // Détermine le type MIME pour l'envoi du fichier
        $mimeType = mime_content_type($cheminFichier);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($cheminFichier) . '"');
        header('Content-Length: ' . filesize($cheminFichier));

        // Lire le fichier et l'envoyer
        readfile($cheminFichier);
        exit;
        
    }

}