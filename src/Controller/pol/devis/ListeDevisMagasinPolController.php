<?php

namespace App\Controller\pol\devis;

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Entity\magasin\bc\BcMagasin;
use App\Service\TableauEnStringService;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;

/**
 * @Route("/pol")
 */
class ListeDevisMagasinPolController extends Controller
{
    private $styleStatutDw = [];
    private $styleStatutBc = [];
    private $statutIPS = [];

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

        $this->statutIPS = [
            "--"  => "En cours",
            "AC"  => "Accepté",
            "DE"  => "Edité",
            "RE"  => "Refusé",
            "TR"  => "Transferé",
        ];
    }

    /**
     * @Route("/liste-devis-magasin-pol", name="devis_magasin_pol_liste")
     */
    public function listeDevisMagasin(Request $request)
    {
        // Agences Services autorisés sur le DVM
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DVM);

        //formulaire de recherhce
        $form = $this->getFormFactory()->createBuilder(DevisMagasinSearchType::class, $this->initialisationCriteria(), [
            'em' => $this->getEntityManager(),
            'method' => 'GET',
            'agenceServiceAutorises' => $agenceServiceAutorises
        ])->getForm();

        /** @var array */
        $criteria = $this->traitementFormulaireRecherche($request, $form, $agenceServiceAutorises);

        $this->getSessionService()->set('criteria_for_excel_liste_devis_magasin', $criteria);

        $listeDevisFactory = $this->recuperationDonner($criteria, $agenceServiceAutorises);

        // affichage de la liste des devis magasin
        return $this->render('magasin/devis/listeDevisMagasin.html.twig', [
            'listeDevis' => $listeDevisFactory,
            'form' => $form->createView(),
            'styleStatutDw' => $this->styleStatutDw,
            'styleStatutBc' => $this->styleStatutBc,
            'statutIPS' => $this->statutIPS,
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

    public function recuperationDonner(array $criteria = [], array $agenceServiceAutorises): array
    {
        $vignette = 'pneumatique';
        $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $this->listeDevisMagasinModel->getNumeroDevisExclure()));
        $devisIps = $this->listeDevisMagasinModel->getDevis($criteria, $vignette, $agenceServiceAutorises, $numDeviAExclure);

        $listeDevisFactory = [];
        $dejaVu = []; // Tableau pour mémoriser les numéros de devis déjà traités

        foreach ($devisIps as $devisIp) {
            $numeroDevis = $devisIp['numero_devis'] ?? null;

            // Si on a déjà traité ce numéro de devis → on ignore
            if ($numeroDevis === null || in_array($numeroDevis, $dejaVu, true)) {
                continue;
            }

            $dejaVu[] = $numeroDevis; // On le marque comme vu

            $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);

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
            if ($devisIp['statut_dw'] === DevisMagasin::STATUT_A_TRAITER && $devisIp['statut_ips'] === 'TR') {
                continue;
            }

            // Application des filtres critères
            if (!empty($criteria) && !$this->matchesCriteria($devisIp, $criteria)) {
                continue;
            }

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

        return true;
    }
}
