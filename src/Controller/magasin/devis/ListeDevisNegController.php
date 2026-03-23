<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Dto\Magasin\Devis\DevisSearchDto;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Entity\magasin\devis\DevisMagasin;
use App\Form\magasin\devis\DevisNegSearchType;
use App\Mapper\Magasin\Devis\DevisNegMapper;
use App\Model\magasin\devis\DevisNegModel;
use App\Service\TableauEnStringService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ListeDevisNegController extends Controller
{
    use AutorisationTrait;

    private DevisNegModel $listeDevisNegModel;
    private DevisNegMapper $devisNegMapper;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisNegModel = new DevisNegModel();
        $this->devisNegMapper = new DevisNegMapper();
    }

    /**
     * @Route("/liste-devis-neg", name="liste_devis_neg")
     */
    public function listeDevisNeg(Request $request)
    {
        // Traitement du formulaire de recherche
        [$form, $criteria] = $this->creationEtTraitementformulaireDeRecherche($request);

        return $this->render('magasin/devis/liste_devis_neg.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/api/devis-neg/data", name="api_devis_neg_data")
     */
    public function getApiData(Request $request)
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 50);

            // On utilise la même méthode que pour l'affichage initial pour extraire les critères
            [, $criteria] = $this->creationEtTraitementformulaireDeRecherche($request);

            $devisNeg = $this->getDataDevisNegEnDto($page, $limit, $criteria);

            return new JsonResponse([
                'success' => true,
                'data' => $devisNeg,
            ]);
        } catch (\Throwable $e) {
            if (ob_get_length() > 0) {
                ob_clean();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des données.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function creationEtTraitementformulaireDeRecherche($request): array
    {
        $form = $this->getFormFactory()->createBuilder(DevisNegSearchType::class, null, [
            'em' => $this->getEntityManager(),
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        return [$form, $criteria];
    }

    private function getDataDevisNegEnDto(int $page = 1, int $limit = 50, $criteria = [])
    {
        if ($criteria instanceof DevisSearchDto) {
            $criteria = (array) $criteria;
        }

        $codeAgenceAutoriserString = TableauEnStringService::orEnString($this->getUser()->getAgenceAutoriserCode());
        $vignette = 'magasin';
        $adminMutli = in_array(1, $this->getUser()->getRoleIds()) || in_array(6, $this->getUser()->getRoleIds());

        // Utilisation du cache de session pour la liste d'exclusion
        $session = $this->getSessionService();
        $numDeviAExclure = $session->get('devis_neg_exclure_cache');

        if (!$numDeviAExclure) {
            $rawExclusions = $this->listeDevisNegModel->getNumeroDevisExclure();
            $numDeviAExclure = TableauEnStringService::simpleNumeric(array_map('intval', $rawExclusions));
            // Si la liste est vide, on met une valeur bidon pour éviter une erreur SQL
            if (empty($numDeviAExclure)) $numDeviAExclure = "'0'";
            $session->set('devis_neg_exclure_cache', $numDeviAExclure);
        }

        $urlGenerator = function ($dto) {
            $numeroDevis = $dto->numeroDevis;
            $emetteur = $dto->emetteur;

            $url = [
                "verificationPrix" => $this->getUrlGenerator()->generate('devis_magasin_soumission_verification_prix', ['numeroDevis' => $numeroDevis]),
                "validationDevis"  => $this->getUrlGenerator()->generate('devis_magasin_soumission_validation_devis', ['numeroDevis' => $numeroDevis, 'codeAgenceService' => $emetteur]),
                "soumissionBC"     => $this->getUrlGenerator()->generate('bc_magasin_soumission', ['numeroDevis' => $numeroDevis]),
            ];

            $pointageDevis = in_array($dto->statutDw, [DevisMagasin::STATUT_PRIX_VALIDER_TANA, DevisMagasin::STATUT_PRIX_MODIFIER_TANA, DevisMagasin::STATUT_VALIDE_AGENCE]);
            if ($pointageDevis) {
                $url["pointageDevis"] = $this->getUrlGenerator()->generate("devis_magasin_envoyer_au_client", ["numeroDevis" => $numeroDevis]);
            }

            $dto->pointagedevis = $pointageDevis;
            $dto->relanceClient = ($dto->statutDw === DevisMagasin::STATUT_ENVOYER_CLIENT && $dto->statutBc === BcMagasin::STATUT_EN_ATTENTE_BC);

            return $url;
        };

        $devisNeg = $this->listeDevisNegModel->getDevisNeg($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure, $page, $limit);
        $devisNeg = $this->devisNegMapper->map($devisNeg, $urlGenerator);

        return $devisNeg;
    }
}
