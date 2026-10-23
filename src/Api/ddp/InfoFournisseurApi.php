<?php

namespace App\Api\ddp;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Controller\Traits\ddp\DdpTrait;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\ddp\DemandePaiement;
use App\Entity\dw\DwCommande;
use App\Entity\dw\DwBcClientNegoce;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoFournisseurApi extends Controller
{
    use DdpTrait;

    private $demandePaiementModel;
    private $cdeFnrRepository;
    private $demandePaiementRepository;

    public function __construct()
    {
        $this->cdeFnrRepository = $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class);
        $this->demandePaiementRepository  = $this->getEntityManager()->getRepository(DemandePaiement::class);
    }

    /**
     * Model créé à la première utilisation : son constructeur ouvre 4 connexions
     * (intranet, Informix, SQL Server 04, GCOT), inutiles pour les endpoints
     * qui ne servent qu'un fichier (ex: PDF de commande).
     */
    private function demandePaiementModel(): DemandePaiementModel
    {
        if ($this->demandePaiementModel === null) {
            $this->demandePaiementModel = new DemandePaiementModel();
        }
        return $this->demandePaiementModel;
    }

    /**
     * Libère le verrou de session PHP pour les endpoints en lecture seule :
     * sinon les requêtes AJAX d'un même utilisateur (documents, montant, PDF de
     * commande...) s'exécutent l'une après l'autre au lieu d'en parallèle.
     * $_SESSION reste lisible après l'appel.
     */
    private function libererSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    /**
     * @Route("/api/info-fournisseur-ddp", name="api_info_fournisseur_ddp")
     */
    public function fournisseurInfo()
    {
        $results = [];

        $infoFournisseur = $this->demandePaiementModel()->recupInfoFournissseur();

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
        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        // Libère le verrou de session PHP : la suite (requêtes IPS/GCOT) peut
        // durer plusieurs dizaines de secondes pour un gros fournisseur, et
        // bloquerait toutes les autres requêtes de l'utilisateur (PDF de
        // commande, documents, montant...) jusqu'à la fin.
        $this->libererSession();

        // {typeId} arrive de l'URL en chaîne ("1", "2") : conversion pour les comparaisons strictes
        $typeId = (int) $typeId;

        // Commandes fournisseur validées (dernière version, statut "Validée")
        $cdeFrnRepository = $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class);
        $numCdeValidesListe = array_values(array_unique(array_map(
            fn($el) => trim((string) $el->getNumCdeFournisseur()),
            $cdeFrnRepository->findValideesDerniereVersion($numeroFournisseur)
        )));
        $numCdeValides = implode(',', array_map(fn($el) => "'" . $el . "'", $numCdeValidesListe));

        // Commandes réceptionnées dans IPS (type 1 : limitées aux commandes validées)
        $numCdesAvecLivraison = $this->demandePaiementModel()->getCommandeReceptionnee($numeroFournisseur, $codeSociete, $typeId, $numCdeValides);

        if ($typeId === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            // Après arrivage : commandes réceptionnées
            $numCdes = $numCdesAvecLivraison;
        } else {
            // À l'avance : commandes validées qui ne sont PAS encore réceptionnées
            $numCdes = array_values(array_diff(
                $numCdeValidesListe,
                array_map(fn($el) => trim((string) $el), $numCdesAvecLivraison)
            ));
        }

        $numCde = array_map(fn($el) => ['label' => $el, 'value' => $el], $numCdes);

        // Liste GCOT (factures/dossiers de douane) : utile uniquement pour l'après
        // arrivage ; la requête peut durer plusieurs dizaines de secondes.
        $listeGcot = [];
        if ($typeId === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
            $numFacs = $this->demandePaiementModel()->getFactureNonReglee($numeroFournisseur);
            $numFacString = TableauEnStringService::TableauEnString(',', $numFacs);
            $listeGcot = $this->demandePaiementModel()->findListeGcot($numeroFournisseur, $numCdesString, $numFacString);
        }

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
        $this->libererSession();

        $factureArray = explode(',', $numFacture);
        $numFacString = TableauEnStringService::TableauEnString(',', $factureArray);

        // getMontantFacGcot() ne filtre que sur les factures : on ne calcule plus
        // la liste des commandes (recuperationCdeFacEtNonFac), qui faisait une
        // requête Informix par commande DW (~4 500) soit ~40 s, pour rien.
        $montants = $this->demandePaiementModel()->getMontantFacGcot($numeroFournisseur, '', $numFacString);

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
        $montantCde = $this->demandePaiementModel()->getMontantCdeAvance($numCdesString);

        if ($montantCde[0]['montantcde'] == null) {
            $montantCde[0]['montantcde'] = 0.00;
        }

        header("Content-type:application/json");
        echo json_encode($montantCde);
    }

    /**
     * Montant HT d'une commande fournisseur et plafond du montant à payer pour
     * une demande de paiement à l'avance (montant HT - demandes déjà validées),
     * avec l'historique des demandes de paiement de la commande.
     *
     * @Route("/api/montant-commande-ht/{numCde}", name="api_montant_commande_ht", requirements={"numCde"="\d+"})
     */
    public function montantCommandeHt(string $numCde)
    {
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $this->libererSession();

        $montantHt = (float) $this->demandePaiementModel()->getMontantCde($numCde, $codeSociete)['montant_total_cde_ht'];
        // Demandes de paiement déjà validées pour cette commande
        $montantDejaValide = $this->demandePaiementRepository->getSommeMontantDdpValideParCommande($numCde, $codeSociete);
        // % (montant à payer / montant total du commande) déjà validés, toutes demandes et à l'avance seules
        $ratioDejaValide = $this->demandePaiementRepository->getCumulRatioDdpValideParCommande($numCde, $codeSociete);
        $ratioAvanceValide = $this->demandePaiementRepository->getCumulRatioDdpValideParCommande($numCde, $codeSociete, TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE);

        header("Content-type:application/json");
        echo json_encode([
            'montantHt'         => $montantHt,
            'montantDejaValide' => $montantDejaValide,
            'montantRestant'    => $montantHt - $montantDejaValide,
            'ratioDejaValide'   => $ratioDejaValide,
            'commandeSoldee'    => $ratioAvanceValide >= 100 - 0.01,
            'historique'        => $this->historiqueDdpCommande($numCde),
        ]);
    }

    /**
     * Paiement après arrivage : situation des demandes de paiement à l'avance
     * d'une commande (ratio DDPA, solde avance) et type de DDP à générer :
     * BAP si 0 % DDPA, DDPR si 100 %, DDPL entre les deux.
     *
     * @Route("/api/situation-avance-commande/{numCde}", name="api_situation_avance_commande")
     */
    public function situationAvanceCommande(string $numCde)
    {
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        $this->libererSession();

        $situation = $this->demandePaiementRepository->getSituationAvanceParCommande($numCde, $codeSociete);
        $typeDemande = $this->getEntityManager()->getRepository(TypeDemande::class)->find($situation['typeDdpAGenerer']);

        header("Content-type:application/json");
        echo json_encode($situation + [
            'ddpAGenerer' => $typeDemande ? $typeDemande->getCode() : '',
        ]);
    }

    /**
     * Historique des demandes de paiement (hors refusées) d'une commande, avec
     * pour chacune son pourcentage (montant à payer / montant total du commande) et le cumul.
     */
    private function historiqueDdpCommande(string $numCde): array
    {
        $historique = [];
        $cumul = 0.0;

        /** @var DemandePaiement $ddp */
        foreach ($this->demandePaiementRepository->getDdpSelonNumCde($numCde) as $ddp) {
            $montant = (float) $ddp->getMontantAPayers();
            $ratio = $ddp->getRatioMontantTotalCommande();
            $cumul += $ratio;

            $historique[] = [
                'numeroDdp'    => $ddp->getNumeroDdp(),
                'dateCreation' => $ddp->getDateCreation() ? $ddp->getDateCreation()->format('d/m/Y') : '',
                'motif'        => $ddp->getMotif(),
                'montant'      => $montant,
                'ratio'        => $ratio,
                'cumul'        => $cumul,
                'statut'       => $ddp->getStatut(),
            ];
        }

        return $historique;
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
        $this->libererSession();

        $dossiers = $this->demandePaiementModel()->findListeDoc($numeroDossier);

        $response = new JsonResponse($dossiers);
        $response->send();
    }


    /**
     * @Route("/api/recuperer-fichier", name="api_recuperer_fichier")
     */
    public function recupererFichier(Request $request)
    {
        $this->libererSession();

        $basePath = '\\\\192.168.0.15\\GCOT_DATA\\TRANSIT';
        // Nom_Fichier (GCOT_Gestion_Document) est un chemin UNC complet ; on accepte aussi un chemin relatif
        $path = (string) $request->query->get('path', '');
        if (stripos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        $baseReel = realpath($basePath);
        $cheminReel = $baseReel !== false
            ? realpath($baseReel . DIRECTORY_SEPARATOR . ltrim($path, '/\\'))
            : false;

        // refus de tout chemin hors du dossier TRANSIT (../, autre partage...)
        if ($baseReel === false || $cheminReel === false || stripos($cheminReel, $baseReel) !== 0 || !is_file($cheminReel) || !is_readable($cheminReel)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Fichier introuvable']);
            exit;
        }

        header('Content-Type: ' . (mime_content_type($cheminReel) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($cheminReel));
        readfile($cheminReel);
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
        $fournisseurs = $this->demandePaiementModel()->getFournisseur();

        header("Content-type:application/json");
        echo json_encode($fournisseurs);
    }

    /**
     * Récupère les fichiers (colonne 'path' de DW_Commande) des commandes fournisseur choisies.
     *
     * @Route("/api/fichiers-commande-fournisseur/{numeroCommande}", name="api_fichiers_commande_fournisseur")
     */
    public function fichiersCommandeFournisseur(string $numeroCommande)
    {
        $this->libererSession();

        $dwCommandeRepository = $this->getEntityManager()->getRepository(DwCommande::class);

        $fichiers = [];
        foreach (explode(',', $numeroCommande) as $numCde) {
            $numCde = trim($numCde);
            if ($numCde === '') {
                continue;
            }

            foreach ($dwCommandeRepository->findPathByNumeroCde($numCde) as $result) {
                if (empty($result['path'])) {
                    continue;
                }

                $fichiers[] = [
                    'numeroCde'  => $result['numeroCde'],
                    'path'       => $result['path'],
                    'nomFichier' => basename(str_replace('\\', '/', $result['path'])),
                ];
            }
        }

        header("Content-type:application/json");
        echo json_encode($fichiers);
    }

    /**
     * Récupère les fichiers (colonne 'path' de DW_Ordre_De_Reparation) des OR liés
     * aux commandes fournisseur choisies.
     *
     * @Route("/api/fichiers-or-valide/{numeroCommande}", name="api_fichiers_or_valide")
     */
    public function fichiersOrValide(string $numeroCommande)
    {
        $this->libererSession();

        $numCdes = explode(',', $numeroCommande);
        $orsParCommande = $this->demandePaiementModel()->getNumerosOrParCommandes($numCdes);

        // OR => commandes liées
        $commandesParOr = [];
        foreach ($orsParCommande as $row) {
            $commandesParOr[trim($row['numero_or'])][] = trim($row['numero_cde']);
        }

        $fichiers = [];
        foreach ($this->demandePaiementModel()->getPathsDwOrdreReparation(array_keys($commandesParOr)) as $result) {
            if (empty($result['path'])) {
                continue;
            }

            $numeroOr = trim($result['numero_or']);
            $fichiers[] = [
                'numeroOr'   => $numeroOr,
                'numeroCdes' => array_values(array_unique($commandesParOr[$numeroOr] ?? [])),
                'path'       => $result['path'],
                'nomFichier' => basename(str_replace('\\', '/', $result['path'])),
            ];
        }

        header("Content-type:application/json");
        echo json_encode([
            'numerosOr' => array_map('strval', array_keys($commandesParOr)),
            'fichiers'  => $fichiers,
        ]);
    }

    /**
     * Récupère les fichiers (colonne 'path' de DW_BC_Client_Negoce) des BC client
     * rattachés aux commandes fournisseur choisies.
     *
     * @Route("/api/fichiers-bc-client/{numeroCommande}", name="api_fichiers_bc_client")
     */
    public function fichiersBcClient(string $numeroCommande)
    {
        $this->libererSession();

        $numCdes = array_values(array_filter(array_map('trim', explode(',', $numeroCommande))));

        $resultats = $this->getEntityManager()
            ->getRepository(DwBcClientNegoce::class)
            ->findPathsByNumeroCdes($numCdes);

        $fichiers = array_map(fn($result) => [
            'numeroCde'    => $result['numeroCde'],
            'numeroBccNeg' => $result['numeroBccNeg'],
            'path'         => $result['path'],
            'nomFichier'   => basename(str_replace('\\', '/', $result['path'])),
        ], $resultats);

        header("Content-type:application/json");
        echo json_encode($fichiers);
    }

    /**
     * Sert le contenu d'un fichier de commande DW_Commande (chemin relatif à BASE_PATH_FICHIER).
     *
     * @Route("/api/telecharger-fichier-commande-dw", name="api_telecharger_fichier_commande_dw")
     */
    public function telechargerFichierCommandeDw(Request $request)
    {
        $this->libererSession();

        $path = urldecode((string) $request->query->get('path', ''));

        $baseReel = realpath(rtrim($_ENV['BASE_PATH_FICHIER'], '/\\'));
        $cheminReel = $baseReel !== false
            ? realpath($baseReel . DIRECTORY_SEPARATOR . ltrim($path, '/\\'))
            : false;

        header('Content-Type: application/json');

        if ($baseReel === false || $cheminReel === false || strpos($cheminReel, $baseReel) !== 0 || !is_readable($cheminReel)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Fichier introuvable']);
            exit;
        }

        $mimeType = mime_content_type($cheminReel) ?: 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($cheminReel));
        readfile($cheminReel);
        exit;
    }
}
