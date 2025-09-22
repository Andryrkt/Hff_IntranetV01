<?php

namespace App\Controller\magasin\devis;

use App\Entity\admin\Application;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Repository\admin\AgenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Controller;
use App\Service\ServiceContainer;
use App\Bootstrap\ServiceContainerBootstrap;

/**
 * @Route("/magasin/dematerialisation")
 */
class ListeDevisMagasinController extends Controller
{
    use AutorisationTrait;

    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private DevisMagasinRepository $devisMagasinRepository;
    private AgenceRepository $agenceRepository;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupère le modèle ListeDevisMagasinModel
     */
    private function getListeDevisMagasinModel(): ListeDevisMagasinModel
    {
        if ($this->listeDevisMagasinModel === null) {
            try {
                // Initialiser le service container si nécessaire
                if (!ServiceContainer::has('App\Model\magasin\devis\ListeDevisMagasinModel')) {
                    ServiceContainerBootstrap::initialize();
                }
                $this->listeDevisMagasinModel = ServiceContainer::get('App\Model\magasin\devis\ListeDevisMagasinModel');
            } catch (\Exception $e) {
                // Fallback : créer une instance directement
                $this->listeDevisMagasinModel = new ListeDevisMagasinModel(
                    $this->getConnexion(),
                    $this->getConnect(),
                    $this->getConnexion04(),
                    $this->getConnexion04Gcot()
                );
            }
        }
        return $this->listeDevisMagasinModel;
    }

    /**
     * Récupère l'EntityManager
     */
    private function getEntityManagerService(): EntityManagerInterface
    {
        if ($this->entityManager === null) {
            try {
                // Initialiser le service container si nécessaire
                if (!ServiceContainer::has('doctrine.orm.default_entity_manager')) {
                    ServiceContainerBootstrap::initialize();
                }
                $this->entityManager = ServiceContainer::get('doctrine.orm.default_entity_manager');
            } catch (\Exception $e) {
                // Fallback : utiliser l'EntityManager de la classe parente
                $this->entityManager = $this->getEntityManager();
            }
        }
        return $this->entityManager;
    }

    /**
     * Récupère le repository DevisMagasinRepository
     */
    private function getDevisMagasinRepository(): DevisMagasinRepository
    {
        if ($this->devisMagasinRepository === null) {
            $this->devisMagasinRepository = $this->getEntityManagerService()->getRepository(DevisMagasin::class);
        }
        return $this->devisMagasinRepository;
    }

    /**
     * Récupère le repository AgenceRepository
     */
    private function getAgenceRepository(): AgenceRepository
    {
        if ($this->agenceRepository === null) {
            $this->agenceRepository = $this->getEntityManagerService()->getRepository(\App\Entity\admin\Agence::class);
        }
        return $this->agenceRepository;
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
        $form = $this->getFormFactory()->createBuilder(DevisMagasinSearchType::class, null, [
            'em' => $this->getEntityManagerService()
        ])->getForm();

        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $criteria['dateCreation'] = $form->get('dateCreation')->getData();
        }

        $this->getSessionService()->set('criteria_for_excel_liste_devis_magasin', $criteria);

        $listeDevisFactory = $this->recuperationDonner($criteria);

        // affichage de la liste des devis magasin
        return $this->render('magasin/devis/listeDevisMagasin.html.twig', [
            'listeDevis' => $listeDevisFactory,
            'form' => $form->createView(),
            'styleStatutDw' => $this->styleStatutDw
        ]);
    }

    public function recuperationDonner(array $criteria = []): array
    {
        // recupération de la liste des devis magasin dans IPS
        $devisIps = $this->getListeDevisMagasinModel()->getDevis($criteria);

        $listeDevisFactory = [];
        foreach ($devisIps as  $devisIp) {
            //recupération des information de devis soumission à validation neg
            $numeroVersionMax = $this->getDevisMagasinRepository()->getNumeroVersionMax($devisIp['numero_devis']);
            $devisSoumi = $this->getDevisMagasinRepository()->findOneBy(['numeroDevis' => $devisIp['numero_devis'], 'numeroVersion' => $numeroVersionMax]);
            //ajout des informations manquantes
            $devisIp['statut_dw'] = $devisSoumi ? $devisSoumi->getStatutDw() : '';
            $devisIp['operateur'] = $devisSoumi ? $devisSoumi->getUtilisateur() : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';

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
            $dateCreation = new \DateTime($devisIp['date_creation']);
            $dateDebut = $criteria['dateCreation']['debut'];
            if ($dateCreation < $dateDebut) {
                return false;
            }
        }

        // Filtre par date de création (fin)
        if (!empty($criteria['dateCreation']['fin'])) {
            $dateCreation = new \DateTime($devisIp['date_creation']);
            $dateFin = $criteria['dateCreation']['fin'];
            if ($dateCreation > $dateFin) {
                return false;
            }
        }

        return true;
    }
}
