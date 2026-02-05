<?php

namespace App\Controller\magasin\devis;

use App\Constants\Magasin\Devis\PointageRelanceStatutConstant;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dw\DwBcClientNegoce;
use App\Entity\magasin\bc\BcMagasin;
use App\Service\TableauEnStringService;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\magasin\devis\PointageRelance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dw\DwBcClientNegoceRepository;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;

/**
 * @Route("/magasin/dematerialisation")
 */
class ListeDevisMagasinController extends Controller
{
    use AutorisationTrait;

    private $styleStatutDw = [];
    private $styleStatutBc = [];
    private $statutIPS = [];
    private $styleStatutPR = [];

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

        $this->styleStatutPR = [
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //formulaire de recherhce
        $form = $this->getFormFactory()->createBuilder(DevisMagasinSearchType::class, $this->initialisationCriteria(), [
            'em' => $this->getEntityManager(),
            'method' => 'GET'
        ])->getForm();

        /** @var array */
        $criteria = $this->traitementFormulaireRecherche($request, $form);

        // Normalisation des critères avant de les stocker en session
        $criteriaForSession = $criteria;
        if (isset($criteriaForSession['emetteur']['agence']) && is_object($criteriaForSession['emetteur']['agence'])) {
            $criteriaForSession['emetteur']['agence'] = $criteriaForSession['emetteur']['agence']->getId();
        }
        if (isset($criteriaForSession['emetteur']['service']) && is_object($criteriaForSession['emetteur']['service'])) {
            $criteriaForSession['emetteur']['service'] = $criteriaForSession['emetteur']['service']->getId();
        }
        $this->getSessionService()->set('criteria_for_excel_liste_devis_magasin', $criteriaForSession);

        $listeDevisFactory = $this->recuperationDonner($criteria);
        $preparedDatas     = $this->prepareDatasForView($listeDevisFactory, $this->styleStatutDw, $this->styleStatutBc, $this->statutIPS, $this->styleStatutPR);


        // affichage de la liste des devis magasin
        return $this->render('magasin/devis/listeDevisMagasin.html.twig', [
            'datas' => $preparedDatas,
            'form'  => $form->createView(),
        ]);
    }

    private function traitementFormulaireRecherche(Request $request, $form): array
    {
        $criteria = [];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteriaDto = $form->getData();
            $criteria = $criteriaDto->toArrayFilter();
        }
        return $criteria;
    }

    private function initialisationCriteria()
    {
        // recupération de la session pour le criteria
        $criteriaTab = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');

        // Dénormalisation : recharger les entités à partir des IDs
        if (!empty($criteriaTab['emetteur']['agence'])) {
            $agenceRepository = $this->getEntityManager()->getRepository(Agence::class);
            $agence = $agenceRepository->find($criteriaTab['emetteur']['agence']);
            $criteriaTab['emetteur']['agence'] = $agence;
        }

        if (!empty($criteriaTab['emetteur']['service'])) {
            $service = $this->getEntityManager()->getRepository(Service::class)->find($criteriaTab['emetteur']['service']);
            $criteriaTab['emetteur']['service'] = $service;
        }

        // transforme en objet
        $ListeDevisSearchDto = new ListeDevisSearchDto();
        return $ListeDevisSearchDto->toObject($criteriaTab);
    }

    public function recuperationDonner(array $criteria = []): array
    {
        // $codeAgenceUser = $this->getUser()->getCodeAgenceUser();
        $codeAgenceAutoriserString = TableauEnStringService::orEnString($this->getUser()->getAgenceAutoriserCode());
        // $vignette       = $codeAgenceUser === '01' ? 'magasin' : 'magasin_pol';
        $vignette = 'magasin';
        $adminMutli          = in_array(1, $this->getUser()->getRoleIds()) || in_array(6, $this->getUser()->getRoleIds());

        $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $this->listeDevisMagasinModel->getNumeroDevisExclure()));

        $devisIps = $this->listeDevisMagasinModel->getDevis($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure);

        $listeDevisFactory = [];
        $dejaVu = []; // Tableau pour mémoriser les numéros de devis déjà traités

        $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);

        /** @var DwBcClientNegoceRepository $dwBcClientNegoceRepository */
        $dwBcClientNegoceRepository = $this->getEntityManager()->getRepository(DwBcClientNegoce::class);

        $pointageRelanceRepository = $this->getEntityManager()->getRepository(PointageRelance::class);

        foreach ($devisIps as $devisIp) {
            $numeroDevis = $devisIp['numero_devis'] ?? null;

            // Si on a déjà traité ce numéro de devis → on ignore
            if ($numeroDevis === null || in_array($numeroDevis, $dejaVu, true)) continue;

            $dejaVu[] = $numeroDevis; // On le marque comme vu

            // Récupération de la version maximale
            $numeroVersionMax = $devisMagasinRepository->getNumeroVersionMax($numeroDevis);
            $devisSoumi       = $devisMagasinRepository->findOneBy([
                'numeroDevis'    => $numeroDevis,
                'numeroVersion'  => $numeroVersionMax
            ]);

            // Ajout des informations complémentaires
            $devisIp['statut_dw']                  = $devisSoumi ? $devisSoumi->getStatutDw()                  : DevisMagasin::STATUT_A_TRAITER;
            $devisIp['operateur']                  = $devisSoumi ? $devisSoumi->getUtilisateur()               : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';
            $devisIp['utilisateur_createur_devis'] = $this->listeDevisMagasinModel
                ->getUtilisateurCreateurDevis($numeroDevis) ?? '';
            $devisIp['statut_bc']                  = $devisSoumi ? $devisSoumi->getStatutBc()                  : '';

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
                $dateRelance = $pointageRelanceRepository->findDernierDateDeRelance($numeroDevis);
                $numeroRelance = $pointageRelanceRepository->findNumeroRelance($numeroDevis);
                $statutRelance = $this->listeDevisMagasinModel->getStatutRelance($numeroDevis);
                // $relances = $this->getEntityManager()->getRepository(PointageRelance::class)->findBy(['numeroDevis' => $numeroDevis], ['dateDeRelance' => 'DESC']);

                $devisIp['date_derniere_relance'] = $dateRelance;
                $devisIp['numero_relance'] = $numeroRelance;
                $devisIp['statut_relance'] = $statutRelance ?? null;
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
        if (!empty($criteria['emetteur']['agence'])) {
            // Récupérer les 2 premiers caractères de l'agence émetteur
            $agenceEmetteurCode = !empty($devisIp['emmeteur']) ? substr($devisIp['emmeteur'], 0, 2) : '';
            if ($agenceEmetteurCode !== $criteria['emetteur']['agence']->getCodeAgence()) {
                return false;
            }
        }

        // Filtre par service émetteur
        if (!empty($criteria['emetteur']['service'])) {
            // Récupérer les 3 derniers caractères du service émetteur
            $serviceEmetteurCode = !empty($devisIp['emmeteur']) ? substr($devisIp['emmeteur'], -3) : '';
            if ($serviceEmetteurCode !== $criteria['emetteur']['service']->getCodeService()) {
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
    private function prepareDatasForView(array $listeDevisFactory, array $styleStatutDw, array $styleStatutBc, array $statutIpsArray, array $styleStatutPR): array
    {
        $data = [];
        foreach ($listeDevisFactory as $devis) {
            // Données utiles
            $statutDw = $devis->getStatutDw();
            $statutBc = $devis->getStatutBc();
            $statutIps = $devis->getStatutIps();
            $emetteur = $devis->getSuccursaleServiceEmetteur();
            $numeroDevis = $devis->getNumeroDevis();

            $pointageDevis = in_array($statutDw, [DevisMagasin::STATUT_PRIX_VALIDER_TANA, DevisMagasin::STATUT_PRIX_MODIFIER_TANA, DevisMagasin::STATUT_VALIDE_AGENCE]);
            $relanceClient = $statutDw === DevisMagasin::STATUT_ENVOYER_CLIENT && strcasecmp($statutBc, BcMagasin::STATUT_EN_ATTENTE_BC) === 0;

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
                'statutRelance' => $devis->getStatutRelance(),
                'relances' => $devis->getRelances() ?? [],
                'styleStatutPR' => $styleStatutPR[$devis->getStatutRelance()]
            ];
        }

        return $data;
    }
}
