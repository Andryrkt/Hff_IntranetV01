<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\admin\AgenceRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\magasin\bc\BcMagasin;

/**
 * @Route("/magasin/dematerialisation")
 */
class ListeDevisMagasinController extends Controller
{
    use AutorisationTrait;

    private $styleStatutDw = [];

    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private DevisMagasinRepository $devisMagasinRepository;
    private AgenceRepository $agenceRepository;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
        $this->devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
        $this->agenceRepository = $this->getEntityManager()->getRepository(\App\Entity\admin\Agence::class);

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
            'em' => $this->getEntityManager()
        ])->getForm();

        /** @var array */
        $criteria = $this->traitementFormulaireRecherche($request, $form);

        $this->getSessionService()->set('criteria_for_excel_liste_devis_magasin', $criteria);

        $listeDevisFactory = $this->recuperationDonner($criteria);

        // affichage de la liste des devis magasin
        return $this->render('magasin/devis/listeDevisMagasin.html.twig', [
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
        $criteriaTab = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');

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
            //recupération des information de devis soumission à validation neg
            $numeroVersionMax = $this->devisMagasinRepository->getNumeroVersionMax($devisIp['numero_devis']);
            $devisSoumi = $this->devisMagasinRepository->findOneBy(['numeroDevis' => $devisIp['numero_devis'], 'numeroVersion' => $numeroVersionMax]);
            //ajout des informations manquantes
            $devisIp['statut_dw'] = $devisSoumi ? $devisSoumi->getStatutDw() : '';
            $devisIp['operateur'] = $devisSoumi ? $devisSoumi->getUtilisateur() : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';
            $devisIp['utilisateur_createur_devis'] = $this->listeDevisMagasinModel->getUtilisateurCreateurDevis($devisIp['numero_devis']) ?? '';
            $devisIp['statut_bc'] = $this->getEntityManager()->getRepository(BcMagasin::class)->findLatestStatusByIdentifier($devisIp['numero_devis']);
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
            stripos($devisIp['code_client'] ?? '', $criteria['codeClient']) === false
        ) {
            return false;
        }

        // Filtre par opérateur
        if (
            !empty($criteria['Operateur']) &&
            stripos($devisIp['operateur'] ?? '', $criteria['Operateur']) === false
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
