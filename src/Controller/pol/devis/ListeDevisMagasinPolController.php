<?php

namespace App\Controller\pol\devis;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Entity\magasin\devis\DevisMagasin;
use App\Repository\admin\AgenceRepository;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Repository\magasin\devis\DevisMagasinRepository;

/**
 * @Route("/pol")
 */
class ListeDevisMagasinPolController extends Controller
{
    use AutorisationTrait;

    private $styleStatutDw = [];
    private $styleStatutBc = [];

    private ListeDevisMagasinModel $listeDevisMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();

        $this->styleStatutDw = [
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
    }

    /**
     * @Route("/liste-devis-magasin-pol", name="devis_magasin_pol_liste")
     */
    public function listeDevisMagasin(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //formulaire de recherhce
        $form = $this->getFormFactory()->createBuilder(DevisMagasinSearchType::class, $this->initialisationCriteria(), [
            'em' => $this->getEntityManager()
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
        $this->getSessionService()->set('criteria_for_excel_liste_devis_magasin_pol', $criteriaForSession);

        $listeDevisFactory = $this->recuperationDonner($criteria);

        // affichage de la liste des devis magasin
        return $this->render('pol/devis/listeDevisMagasin.html.twig', [
            'listeDevis' => $listeDevisFactory,
            'form' => $form->createView(),
            'styleStatutDw' => $this->styleStatutDw,
            'styleStatutBc' => $this->styleStatutBc
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
        $criteriaTab = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin_pol');

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
        // recupération de la liste des devis magasin dans IPS
        $devisIps = $this->listeDevisMagasinModel->getDevis($criteria);

        $listeDevisFactory = [];
        foreach ($devisIps as  $devisIp) {

            $devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
            //recupération des information de devis soumission à validation neg
            $numeroVersionMax = $devisMagasinRepository->getNumeroVersionMax($devisIp['numero_devis']);
            $devisSoumi = $devisMagasinRepository->findOneBy(['numeroDevis' => $devisIp['numero_devis'], 'numeroVersion' => $numeroVersionMax]);
            //ajout des informations manquantes
            $devisIp['statut_dw'] = $devisSoumi ? $devisSoumi->getStatutDw() : '';
            $devisIp['operateur'] = $devisSoumi ? $devisSoumi->getUtilisateur() : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';
            $devisIp['utilisateur_createur_devis'] = $this->listeDevisMagasinModel->getUtilisateurCreateurDevis($devisIp['numero_devis']) ?? '';
            $devisIp['statut_bc'] = $devisSoumi ? $devisSoumi->getStatutBc() : '';

            // Appliquer les filtres si des critères sont fournis
            if (!empty($criteria) && !$this->matchesCriteria($devisIp, $criteria)) {
                continue; // Ignorer cet élément s'il ne correspond pas aux critères
            }

            //transformation par le factory
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
}
