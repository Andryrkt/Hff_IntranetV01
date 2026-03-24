<?php

namespace App\Controller\magasin\devis;

use App\Constants\admin\ApplicationConstant;
use App\Constants\Magasin\Devis\TypeSoumissionConstant;
use App\Controller\Controller;
use App\Dto\Magasin\Devis\DevisSearchDto;
use App\Entity\magasin\bc\BcMagasin;
use App\Entity\magasin\devis\DevisMagasin;
use App\Form\magasin\devis\DevisNegSearchType;
use App\Mapper\Magasin\Devis\DevisNegMapper;
use App\Model\magasin\devis\DevisNegModel;
use App\Service\TableauEnStringService;
use App\Utils\PerfLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Route("/magasin/dematerialisation")
 */
class ListeDevisNegController extends Controller
{

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


        $response = $this->render('magasin/devis/liste_devis_neg.html.twig', [
            'form' => $form->createView(),
            'urlBases' => [
                'verificationPrix' => $this->getUrlGenerator()->generate('devis_neg_soumission_verification_prix', ['typeSoumission' => TypeSoumissionConstant::VERIFICATION_PRIX, 'numeroDevis' => 'PLACEHOLDER']),
                'validationDevis'  => $this->getUrlGenerator()->generate('devis_magasin_soumission_validation_devis', ['numeroDevis' => 'PLACEHOLDER_NUM', 'codeAgenceService' => 'PLACEHOLDER_AG']),
                'soumissionBC'     => $this->getUrlGenerator()->generate('bc_magasin_soumission', ['numeroDevis' => 'PLACEHOLDER']),
                'pointageDevis'    => $this->getUrlGenerator()->generate('devis_magasin_envoyer_au_client', ['numeroDevis' => 'PLACEHOLDER']),
            ]
        ]);

        return $response;
    }

    /**
     * @Route("/api/devis-neg/data", name="api_devis_neg_data")
     */
    public function getApiData(Request $request)
    {
        // ob_start() capture TOUT output PHP (warnings, notices ODBC, etc.)
        // pour éviter qu'un warning HTML ne corrompe la réponse JSON.
        ob_start();

        try {

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 25);
            [, $criteria] = $this->creationEtTraitementformulaireDeRecherche($request);

            $devisNeg = $this->getDataDevisNegEnDto($page, $limit, $criteria);

            ob_end_clean(); // Supprime tout output parasite avant d'envoyer le JSON
            return new JsonResponse([
                'success' => true,
                'data' => $devisNeg,
            ]);
        } catch (\Throwable $e) {
            ob_end_clean(); // Supprime tout output parasite (warnings HTML, etc.)
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

        // Code Société de l'utilisateur
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        // code agence autoriser
        $codeAgenceAutoriserString = TableauEnStringService::orEnString(array_column($this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_DVM), 'agence_code'));

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
            $dto->pointagedevis = in_array($dto->statutDw, [DevisMagasin::STATUT_PRIX_VALIDER_TANA, DevisMagasin::STATUT_PRIX_MODIFIER_TANA, DevisMagasin::STATUT_VALIDE_AGENCE]);
            $dto->relanceClient = ($dto->statutDw === DevisMagasin::STATUT_ENVOYER_CLIENT && $dto->statutBc === BcMagasin::STATUT_EN_ATTENTE_BC);

            return [];
        };

        $devisNeg = $this->listeDevisNegModel->getDevisNeg($criteria, $codeAgenceAutoriserString, $numDeviAExclure, $codeSociete, $page, $limit);
        $devisNeg = $this->devisNegMapper->map($devisNeg, $urlGenerator);

        return $devisNeg;
    }
}
