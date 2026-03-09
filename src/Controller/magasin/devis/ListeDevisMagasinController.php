<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\dw\DwBcClientNegoce;
use App\Entity\magasin\bc\BcMagasin;
use App\Model\Traits\ConversionModel;
use App\Service\TableauEnStringService;
use App\Entity\magasin\devis\DevisMagasin;
use App\Constants\admin\ApplicationConstant;
use App\Entity\magasin\devis\PointageRelance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dw\DwBcClientNegoceRepository;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\magasin\devis\PointageRelanceRepository;
use App\Constants\Magasin\Devis\PointageRelanceStatutConstant;

/**
 * @Route("/magasin/dematerialisation")
 */
class ListeDevisMagasinController extends Controller
{
    use ConversionModel;

    private $styleStatutDw = [];
    private $styleStatutBc = [];
    private $statutIPS = [];
    private $styleStatutPR1 = [];
    private $styleStatutPR2 = [];
    private $styleStatutPR3 = [];

    private ListeDevisMagasinModel $listeDevisMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();

        $this->styleStatutDw = [
            DevisMagasin::STATUT_A_TRAITER             => 'bg-a-traiter',
            DevisMagasin::STATUT_PRIX_A_CONFIRMER      => 'bg-prix-a-confirmer',
            DevisMagasin::STATUT_PRIX_VALIDER_TANA     => 'bg-prix-valider-tana',
            DevisMagasin::STATUT_PRIX_VALIDER_AGENCE   => 'bg-prix-valider-agence',
            DevisMagasin::STATUT_PRIX_MODIFIER_TANA    => 'bg-prix-modifier-magasin',
            DevisMagasin::STATUT_PRIX_MODIFIER_AGENCE  => 'bg-prix-modifier-agence',
            DevisMagasin::STATUT_DEMANDE_REFUSE_PAR_PM => 'bg-demande-refuse-par-pm',
            DevisMagasin::STATUT_A_VALIDER_CHEF_AGENCE => 'bg-a-valider-chef-agence',
            DevisMagasin::STATUT_VALIDE_AGENCE         => 'bg-valide-agence',
            DevisMagasin::STATUT_ENVOYER_CLIENT        => 'bg-envoyer-client',
            DevisMagasin::STATUT_CLOTURER_A_MODIFIER   => 'bg-cloturer-a-modifier',
        ];

        $this->styleStatutBc = [
            BcMagasin::STATUT_SOUMIS_VALIDATION => 'bg-bc-soumis-validation',
            BcMagasin::STATUT_EN_ATTENTE_BC => 'bg-bc-en-attente',
            BcMagasin::STATUT_VALIDER => 'bg-bc-valide'
        ];

        $this->styleStatutPR1 = [
            PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
            PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_RELANCE => 'bg-warning'
        ];
        $this->styleStatutPR2 = [
            PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
            PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_RELANCE => 'bg-warning'
        ];

        $this->styleStatutPR3 = [
            PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_A_RELANCER => 'bg-danger text-white',
            PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_RELANCE => 'bg-warning'
        ];

        $this->statutIPS = [
            "--"  => "En cours",
            "AC"  => "Accepté",
            "DE"  => "Edité",
            "RE"  => "Refusé",
            "TR"  => "Transferé",
        ];
    }

    /**
     * @Route("/liste-devis-magasin", name="devis_magasin_liste")
     */
    public function listeDevisMagasin(Request $request)
    {
        // Agences Services autorisés sur le DVM
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DVM);

        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();

        //formulaire de recherhce
        $form = $this->getFormFactory()->createBuilder(DevisMagasinSearchType::class, $this->initialisationCriteria(), [
            'em' => $this->getEntityManager(),
            'method' => 'GET',
            'agenceServiceAutorises' => $agenceServiceAutorises
        ])->getForm();

        /** @var array */
        $criteria = $this->traitementFormulaireRecherche($request, $form, $agenceServiceAutorises);

        $this->getSessionService()->set('criteria_for_excel_liste_devis_magasin', $criteria);

        $listeDevisFactory = $this->recuperationDonner($criteria, $agenceServiceAutorises, $codeSociete);
        $preparedDatas     = $this->prepareDatasForView($listeDevisFactory, $this->styleStatutDw, $this->styleStatutBc, $this->statutIPS, $this->styleStatutPR1, $this->styleStatutPR2, $this->styleStatutPR3);


        // affichage de la liste des devis magasin
        return $this->render('magasin/devis/listeDevisMagasin.html.twig', [
            'datas' => $preparedDatas,
            'form'  => $form->createView(),
        ]);
    }

    private function traitementFormulaireRecherche(Request $request, $form, array $agenceServiceAutorises): array
    {
        $criteria = [];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteriaDto = $form->getData();
            $criteria = $criteriaDto->toArrayFilter();
        }

        if (isset($criteria['serviceEmetteur'])) {
            $ligneId = $criteria['serviceEmetteur'];
            if ($ligneId && isset($agenceServiceAutorises[$ligneId])) {
                $criteria['serviceEmetteur'] = $agenceServiceAutorises[$ligneId]['service_code'];
            }
        }

        return $criteria;
    }

    private function initialisationCriteria()
    {
        // recupération de la session pour le criteria
        $criteriaTab = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');

        // transforme en objet
        $ListeDevisSearchDto = new ListeDevisSearchDto();
        return $ListeDevisSearchDto->toObject($criteriaTab);
    }

    public function recuperationDonner(array $criteria, array $agenceServiceAutorises, string $codeSociete): array
    {
        $vignette = 'magasin';
        $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $this->listeDevisMagasinModel->getNumeroDevisExclure()));
        $devisIps = $this->listeDevisMagasinModel->getDevis($criteria, $vignette, $agenceServiceAutorises, $numDeviAExclure, $codeSociete);

        $listeDevisFactory = [];
        $dejaVu = []; // Tableau pour mémoriser les numéros de devis déjà traités

        /** @var DevisMagasinRepository $devisMagasinRepository */
        $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);

        /** @var DwBcClientNegoceRepository $dwBcClientNegoceRepository */
        $dwBcClientNegoceRepository = $this->getEntityManager()->getRepository(DwBcClientNegoce::class);

        /** @var PointageRelanceRepository $pointageRelanceRepository */
        $pointageRelanceRepository = $this->getEntityManager()->getRepository(PointageRelance::class);

        foreach ($devisIps as $devisIp) {
            $numeroDevis = $devisIp['numero_devis'] ?? null;

            // Si on a déjà traité ce numéro de devis → on ignore
            if ($numeroDevis === null || in_array($numeroDevis, $dejaVu, true)) continue;

            $dejaVu[] = $numeroDevis; // On le marque comme vu

            // Récupération de la version maximale
            $numeroVersionMax = $devisMagasinRepository->getNumeroVersionMax($numeroDevis, $codeSociete);
            $devisSoumi       = $devisMagasinRepository->findOneBy([
                'numeroDevis'    => $numeroDevis,
                'numeroVersion'  => $numeroVersionMax,
                'codeSociete'    => $codeSociete
            ]);

            // Ajout des informations complémentaires
            $devisIp['statut_dw']                  = $devisSoumi ? $devisSoumi->getStatutDw()                  : DevisMagasin::STATUT_A_TRAITER;
            $devisIp['operateur']                  = $devisSoumi ? $devisSoumi->getUtilisateur()               : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';
            $devisIp['utilisateur_createur_devis'] = $this->listeDevisMagasinModel->getUtilisateurCreateurDevis($numeroDevis, $codeSociete) ?? '';
            $devisIp['statut_bc']                  = $devisSoumi ? $devisSoumi->getStatutBc()                  : '';
            $devisIp['stop_relance']               = $devisSoumi ? ($devisSoumi->getStopProgressionGlobal() ?? false) : false;
            $devisIp['motif_stop']                 = $devisSoumi ? $devisSoumi->getMotifStopGlobal() : null;

            // statut DW = A traiter et statut BC = TR
            if ($devisIp['statut_dw'] === DevisMagasin::STATUT_A_TRAITER && $devisIp['statut_ips'] === 'TR') continue;

            // initialisation
            $devisIp['numero_po'] = '';
            $devisIp['url_po'] = '';

            if ($numeroDevis) {
                $dwBcClientNegoce = $dwBcClientNegoceRepository->findLastValidatedBcc($numeroDevis);
                if ($dwBcClientNegoce) {
                    $devisIp['numero_po'] = $dwBcClientNegoce['numeroBccNeg'];
                    $devisIp['url_po'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $dwBcClientNegoce['path'];
                }
                $dateRelance = $pointageRelanceRepository->findDernierDateDeRelance($numeroDevis, $codeSociete);
                $numeroRelance = $pointageRelanceRepository->findNumeroRelance($numeroDevis, $codeSociete);
                $statutRelance = $this->listeDevisMagasinModel->getStatutRelance($numeroDevis, $codeSociete);
                // $relances = $this->getEntityManager()->getRepository(PointageRelance::class)->findBy(['numeroDevis' => $numeroDevis], ['dateDeRelance' => 'DESC']);

                $devisIp['date_derniere_relance'] = $dateRelance;
                $devisIp['numero_relance'] = $numeroRelance;
                $devisIp['statut_relance_1'] = $statutRelance['statut_relance_1'] ?? null;
                $devisIp['statut_relance_2'] = $statutRelance['statut_relance_2'] ?? null;
                $devisIp['statut_relance_3'] = $statutRelance['statut_relance_3'] ?? null;
                $devisIp['relances'] = [];
            }

            // Application des filtres critères
            if (!empty($criteria) && !$this->matchesCriteria($devisIp, $criteria)) continue;

            // Transformation via le factory
            $listeDevisFactory[] = (new ListeDevisMagasinFactory())->transformationEnObjet($devisIp);
        }

        return $listeDevisFactory;
    }

    private function matchesCriteria(array $devisIp, array $criteria): bool
    {
        // Filtre par numéro de devis
        if (
            !empty($criteria['numeroDevis']) &&
            stripos($devisIp['numero_devis'], $criteria['numeroDevis']) === false
        ) {
            return false;
        }

        // Filtre par code client
        if (
            !empty($criteria['codeClient']) &&
            stripos($devisIp['client'] ?? '', $criteria['codeClient']) === false
        ) {
            return false;
        }

        // Filtre par opérateur (utilisateur qui a soumis le devis)
        if (
            !empty($criteria['Operateur']) &&
            stripos($devisIp['operateur'] ?? '', $criteria['Operateur']) === false
        ) {
            return false;
        }

        // Filtre par utilisateur createur
        if (
            !empty($criteria['creePar']) &&
            stripos($devisIp['utilisateur_createur_devis'] ?? '', $criteria['creePar']) === false
        ) {
            return false;
        }

        // Filtre par statut DW
        if (
            !empty($criteria['statutDw']) &&
            $devisIp['statut_dw'] !== $criteria['statutDw']
        ) {
            return false;
        }

        // Filtre par statut IPS
        if (
            !empty($criteria['statutIps']) &&
            $devisIp['statut_ips'] !== $criteria['statutIps']
        ) {
            return false;
        }

        //Filtre par statut BC
        if (
            !empty($criteria['statutBc']) &&
            $devisIp['statut_bc'] !== $criteria['statutBc']
        ) {
            return false;
        }

        //Filtre par numero PO
        if (
            !empty($criteria['numeroPO']) &&
            $devisIp['numero_po'] !== $criteria['numeroPO']
        ) {
            return false;
        }

        // Filtre par agence émetteur
        if (!empty($criteria['agenceEmetteur'])) {
            // Récupérer les 2 premiers caractères de l'agence émetteur
            $agenceEmetteurCode = !empty($devisIp['emmeteur']) ? substr($devisIp['emmeteur'], 0, 2) : '';
            if ($agenceEmetteurCode !== $criteria['agenceEmetteur']) {
                return false;
            }
        }

        // Filtre par service émetteur
        if (!empty($criteria['serviceEmetteur'])) {
            // Récupérer les 3 derniers caractères du service émetteur
            $serviceEmetteurCode = !empty($devisIp['emmeteur']) ? substr($devisIp['emmeteur'], -3) : '';
            if ($serviceEmetteurCode !== $criteria['serviceEmetteur']) {
                return false;
            }
        }

        // Filtre par date de création (début)
        if (!empty($criteria['dateCreation']['debut'])) {
            try {
                $dateCreation = new \DateTime($devisIp['date_creation']);
                $dateDebut = $criteria['dateCreation']['debut'];
                // Comparer seulement la partie date (sans l'heure)
                if ($dateCreation->format('Y-m-d') < $dateDebut->format('Y-m-d')) {
                    return false;
                }
            } catch (\Exception $e) {
                // Si la date n'est pas valide, ignorer ce filtre
                return true;
            }
        }

        // Filtre par date de création (fin)
        if (!empty($criteria['dateCreation']['fin'])) {
            try {
                $dateCreation = new \DateTime($devisIp['date_creation']);
                $dateFin = $criteria['dateCreation']['fin'];
                // Comparer seulement la partie date (sans l'heure)
                if ($dateCreation->format('Y-m-d') > $dateFin->format('Y-m-d')) {
                    return false;
                }
            } catch (\Exception $e) {
                // Si la date n'est pas valide, ignorer ce filtre
                return true;
            }
        }

        // Filtre par statut de relance (complexe)
        if (!empty($criteria['filterRelance'])) {
            $filter = $criteria['filterRelance'];
            $r1 = $devisIp['statut_relance_1'] ?? null;
            $r2 = $devisIp['statut_relance_2'] ?? null;
            $r3 = $devisIp['statut_relance_3'] ?? null;
            $isStopped = (bool)($devisIp['stop_relance'] ?? false);

            switch ($filter) {
                case 'A_RELANCER':
                    return ($r1 === 'A relancer' || $r2 === 'A relancer' || $r3 === 'A relancer');

                case '3_RELANCES_OK':
                    // 3ème relance faite (date) et non stoppé
                    return ($r3 !== null && $r3 !== 'A relancer' && !$isStopped);

                case '3_RELANCES_STOP':
                    // 3ème relance faite (date) et stoppé
                    return ($r3 !== null && $r3 !== 'A relancer' && $isStopped);

                case 'STOP_AVANT_R1':
                    // Stoppé alors qu'aucune relance n'a été faite
                    return ($isStopped && $r1 === null);

                case 'STOP_R1':
                    // Stoppé après la relance 1 (R1 est une date, R2 est null ou pas encore A relancer)
                    return ($isStopped && $r1 !== null && $r1 !== 'A relancer' && ($r2 === null || $r2 === 'A relancer'));

                case 'STOP_R2':
                    // Stoppé après la relance 2
                    return ($isStopped && $r2 !== null && $r2 !== 'A relancer' && ($r3 === null || $r3 === 'A relancer'));

                case 'R1_EN_COURS':
                    // Relance 1 effectuée (date) mais R2 pas encore faite
                    return ($r1 !== null && $r1 !== 'A relancer' && ($r2 === null || $r2 === 'A relancer'));

                case 'R2_EN_COURS':
                    // Relance 2 effectuée (date) mais R3 pas encore faite
                    return ($r2 !== null && $r2 !== 'A relancer' && ($r3 === null || $r3 === 'A relancer'));

                case 'R3_EN_COURS':
                    // Relance 3 effectuée (date)
                    return ($r3 !== null && $r3 !== 'A relancer');
            }
        }

        return true;
    }

    /**
     * Prépare les données pour la vue
     *
     * @param ListeDevisMagasinFactory[] $listeDevisFactory
     * @param array $styleStatutDw
     * @param array $styleStatutBc
     * @param array $statutIpsArray
     * 
     * @return array
     */
    private function prepareDatasForView(array $listeDevisFactory, array $styleStatutDw, array $styleStatutBc, array $statutIpsArray, array $styleStatutPR1, array $styleStatutPR2, array $styleStatutPR3): array
    {
        $data = [];
        foreach ($listeDevisFactory as $devis) {
            // Données utiles
            $statutDw = $devis->getStatutDw();
            $statutBc = $devis->getStatutBc();
            $statutIps = $devis->getStatutIps();
            $statutRelance1 = $devis->statutRelance1;
            $statutRelance2 = $devis->statutRelance2;
            $statutRelance3 = $devis->statutRelance3;
            $emetteur = $devis->getSuccursaleServiceEmetteur();
            $numeroDevis = $devis->getNumeroDevis();

            $pointageDevis = in_array($statutDw, [DevisMagasin::STATUT_PRIX_VALIDER_TANA, DevisMagasin::STATUT_PRIX_MODIFIER_TANA, DevisMagasin::STATUT_VALIDE_AGENCE]);
            $relanceClient = $statutDw === DevisMagasin::STATUT_ENVOYER_CLIENT && $statutBc ===  BcMagasin::STATUT_EN_ATTENTE_BC && in_array(PointageRelanceStatutConstant::STATUT_POINTAGE_RELANCE_A_RELANCER, [$statutRelance1, $statutRelance2, $statutRelance3]) && !$devis->getStopRelance();

            // Création d'url
            $url = [
                "verificationPrix" => $this->getUrlGenerator()->generate('devis_magasin_soumission_verification_prix', ['numeroDevis' => $numeroDevis]),
                "validationDevis"  => $this->getUrlGenerator()->generate('devis_magasin_soumission_validation_devis', ['numeroDevis' => $numeroDevis, 'codeAgenceService' => $emetteur]),
                "soumissionBC"     => $this->getUrlGenerator()->generate('bc_magasin_soumission', ['numeroDevis' => $numeroDevis]),
            ];

            if ($pointageDevis) $url["pointageDevis"] = $this->getUrlGenerator()->generate("devis_magasin_envoyer_au_client", ["numeroDevis" => $numeroDevis]);

            $data[] = [
                'url'             => $url,
                'pointageDevis'   => $pointageDevis,
                'statutDw'        => $statutDw,
                'statutBc'        => $statutBc,
                'styleStatutDw'   => $styleStatutDw[$statutDw] ?? '',
                'styleStatutBc'   => $styleStatutBc[$statutBc] ?? '',
                'numeroDevis'     => $numeroDevis,
                'dateCreation'    => $devis->getDateCreation(),
                'emetteur'        => $emetteur,
                'client'          => $devis->getCodeClientLibelleClient(),
                'libelle'         => $devis->getReferenceCLient(),
                'montant'         => number_format($devis->getMontant(), 2, ',', '.'),
                'dateEnvoiClient' => $devis->getDateDenvoiDevisAuClient(),
                'titreStatutIps'  => $statutIpsArray[$statutIps] ?? '',
                'statutIps'       => $statutIps,
                'creePar'         => $devis->getCreePar(),
                'operateur'       => $devis->getOperateur(),
                'numeroPO'        => $devis->getNumeroPO(),
                'urlPO'           => $devis->getUrlPO(),
                'relanceClient'   => $relanceClient,
                'dateDerniereRelance' => $devis->getDateDerniereRelance(),
                'numeroRelance' => $devis->getNombreDeRelance(),
                'statutRelance1' => $statutRelance1,
                'statutRelance2' => $statutRelance2,
                'statutRelance3' => $statutRelance3,
                'relances' => $devis->getRelances() ?? [],
                'styleStatutPR1' => $styleStatutPR1[$statutRelance1] ?? '',
                'styleStatutPR2' => $styleStatutPR2[$statutRelance2] ?? '',
                'styleStatutPR3' => $styleStatutPR3[$statutRelance3] ?? '',
                'stopRelance' => $devis->getStopRelance(),
                'motifStop' => $devis->motifStop
            ];
        }

        return $data;
    }
}
