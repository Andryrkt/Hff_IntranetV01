<?php

namespace App\Controller\bdc;

use App\Constants\admin\ApplicationConstant;
use App\Controller\Controller;
use App\Dto\bdc\BonDeCaisseDto;
use App\Entity\bdc\BonDeCaisse;
use App\Form\bdc\BonDeCaisseType;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\ConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\bdc\BonDeCaisseListeTrait;
use App\Factory\bdc\BonDeCaisseFactory;
use App\Repository\bdc\BonDeCaisseRepository;
use App\Service\ExcelService;
use App\Service\security\SecurityService;

/**
 * @Route("/compta/demande-de-paiement")
 */
class BonDeCaisseController extends Controller
{
    use ConversionTrait;
    use BonDeCaisseListeTrait;
    use FormatageTrait;
    /**
     * Affiche la liste des bons de caisse
     * @Route("/bon-caisse-liste", name="bon_caisse_liste")
     */
    public function listeBonCaisse(Request $request)
    {
        $bonCaisseSearch = new BonDeCaisseDto();

        $hasGetParams = !empty($request->query->all());
        if (!$hasGetParams) {
            $this->getSessionService()->remove('bon_caisse_search_criteria');
        } else {
            $sessionCriteria = $this->getSessionService()->get('bon_caisse_search_criteria', []);
            if (!empty($sessionCriteria)) {
                foreach ($sessionCriteria as $key => $value) {
                    if (property_exists($bonCaisseSearch, $key)) {
                        $bonCaisseSearch->$key = $value;
                    }
                }
            }
        }

        // Agences Services autorisés sur le Bon de Caisse
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_BON_DE_CAISSE);
        $allAgenceServices = $this->getSecurityService()->getAllAgenceServices();

        $form = $this->getFormFactory()->createBuilder(BonDeCaisseType::class, $bonCaisseSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager(),
            'allAgenceServices' => $allAgenceServices
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bonCaisseSearch = $form->getData();

            $dateDemande = $form->get('dateDemande')->getData();
            if ($dateDemande) {
                $bonCaisseSearch->dateDemandeDebut = $dateDemande['debut'];
                $bonCaisseSearch->dateDemandeFin = $dateDemande['fin'];
            }
        }

        $this->gererAgenceService($bonCaisseSearch, $allAgenceServices);

        $criteria = $bonCaisseSearch->toArray();
        $this->getSessionService()->set('bon_caisse_search_criteria', $criteria);

        $bonCaisseEntitySearch = new BonDeCaisse();
        $bonCaisseEntitySearch->setNumeroDemande($bonCaisseSearch->numeroDemande);
        $bonCaisseEntitySearch->setDateDemande($bonCaisseSearch->dateDemandeDebut);
        $bonCaisseEntitySearch->setDateDemandeFin($bonCaisseSearch->dateDemandeFin);
        $bonCaisseEntitySearch->setAgenceDebiteur($bonCaisseSearch->agenceDebiteur);
        $bonCaisseEntitySearch->setServiceDebiteur($bonCaisseSearch->serviceDebiteur);
        $bonCaisseEntitySearch->setAgenceEmetteur($bonCaisseSearch->agenceEmetteur);
        $bonCaisseEntitySearch->setServiceEmetteur($bonCaisseSearch->serviceEmetteur);
        $bonCaisseEntitySearch->setStatutDemande($bonCaisseSearch->statutDemande);
        $bonCaisseEntitySearch->setCaisseRetrait($bonCaisseSearch->caisseRetrait);
        $bonCaisseEntitySearch->setTypePaiement($bonCaisseSearch->typePaiement);
        $bonCaisseEntitySearch->setRetraitLie($bonCaisseSearch->retraitLie);
        $bonCaisseEntitySearch->setNomValidateurFinal($bonCaisseSearch->nomValidateurFinal);

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Agence et service par défaut
        $agenceCodeUser = $this->getSecurityService()->getCodeAgenceUser();
        $serviceCodeUser = $this->getSecurityService()->getCodeServiceUser();

        // Vérifier la permission de voir tous les données
        $multisuccursale = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);

        // Vérifier le permission de voir liste avec débiteur sur la page courante
        $peutVoirListeAvecDebiteur = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_AUTH_2);

        /** @var BonDeCaisseRepository $repository */
        $repository = $this->getEntityManager()->getRepository(BonDeCaisse::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $bonCaisseEntitySearch, $agenceCodeUser, $serviceCodeUser, $agenceServiceAutorises, $peutVoirListeAvecDebiteur, $multisuccursale);
        $data = $paginationData['data'];

        // Récupère tous les chemins PDF en une seule requête
        $cheminsPdf = $this->getCheminsPdfAllBcs($data);

        $bonDeCaisseFactory = new BonDeCaisseFactory();
        return $this->render(
            'bdc/bon_caisse_list.html.twig',
            [
                'form'        => $form->createView(),
                'data'        => $bonDeCaisseFactory->createFromEntities($data, $cheminsPdf),
                'currentPage' => $paginationData['currentPage'],
                'lastPage'    => $paginationData['lastPage'],
                'resultat'    => $paginationData['totalItems'],
                'criteria'    => $criteria,
            ]
        );
    }

    /**
     * @Route("/export-bon-caisse-excel", name="export_bon_caisse_excel")
     */
    public function exportExcel()
    {
        /** Récupère les critères dans la session @var array $criteira*/
        $criteria = $this->getSessionService()->get('bon_caisse_search_criteria', []);

        $bonCaisseSearch = new BonDeCaisseDto();
        $bonCaisseSearch->toObject($criteria);

        $bonCaisseEntitySearch = new BonDeCaisse();
        $bonCaisseEntitySearch->setNumeroDemande($bonCaisseSearch->numeroDemande);
        $bonCaisseEntitySearch->setDateDemande($bonCaisseSearch->dateDemandeDebut);
        $bonCaisseEntitySearch->setDateDemandeFin($bonCaisseSearch->dateDemandeFin);
        $bonCaisseEntitySearch->setAgenceDebiteur($bonCaisseSearch->agenceDebiteur);
        $bonCaisseEntitySearch->setServiceDebiteur($bonCaisseSearch->serviceDebiteur);
        $bonCaisseEntitySearch->setAgenceEmetteur($bonCaisseSearch->agenceEmetteur);
        $bonCaisseEntitySearch->setServiceEmetteur($bonCaisseSearch->serviceEmetteur);
        $bonCaisseEntitySearch->setStatutDemande($bonCaisseSearch->statutDemande);
        $bonCaisseEntitySearch->setCaisseRetrait($bonCaisseSearch->caisseRetrait);
        $bonCaisseEntitySearch->setTypePaiement($bonCaisseSearch->typePaiement);
        $bonCaisseEntitySearch->setRetraitLie($bonCaisseSearch->retraitLie);
        $bonCaisseEntitySearch->setNomValidateurFinal($bonCaisseSearch->nomValidateurFinal);

        // Agences Services autorisés sur le Bon de Caisse
        $agenceServiceAutorises = $this->getSecurityService()->getAgenceServices(ApplicationConstant::CODE_BON_DE_CAISSE);

        // Agence et service par défaut
        $agenceCodeUser = $this->getSecurityService()->getCodeAgenceUser();
        $serviceCodeUser = $this->getSecurityService()->getCodeServiceUser();

        // Vérifier la permission de voir tous les données
        $multisuccursale = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);

        // Vérifier le permission de voir liste avec débiteur sur la page courante
        $peutVoirListeAvecDebiteur = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_AUTH_2, "bon_caisse_liste");

        /** @var BonDeCaisseRepository $repository */
        $repository = $this->getEntityManager()->getRepository(BonDeCaisse::class);
        $entities = $repository->findAndFilteredExcel($bonCaisseEntitySearch, $agenceCodeUser, $serviceCodeUser,  $agenceServiceAutorises, $peutVoirListeAvecDebiteur, $multisuccursale);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "Numéro demande",
            "Date demande",
            "Caisse de retrait",
            "Retrait lié à",
            "Agence/Service émetteur",
            "Agence/Service débiteur",
            "Adresse mail demandeur",
            "Montant",
            "Devise",
            "Motif",
            "Nom validateur final"
        ];

        foreach ($entities as $entity) {

            $data[] = [
                $entity->getStatutDemande(),
                $entity->getNumeroDemande(),
                $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
                $entity->getCaisseRetrait(),
                $entity->getRetraitLie(),
                $entity->getAgenceEmetteur() . ' - ' . $entity->getServiceEmetteur(),
                $entity->getAgenceDebiteur() . ' - ' . $entity->getServiceDebiteur(),
                $entity->getAdresseMailDemandeur(),
                $entity->getMontantPayer(),
                $entity->getDevise(),
                $entity->getMotifDemande(),
                $entity->getNomValidateurFinal()
            ];
        }

        // Crée le fichier Excel
        (new ExcelService())->createSpreadsheet($data);
    }

    private function gererAgenceService(BonDeCaisseDto $bonDeCaisseDto, array $allAgenceServices): void
    {
        // Changer le serviceEmetteur
        if ($bonDeCaisseDto->serviceEmetteur) {
            $ligneId = $bonDeCaisseDto->serviceEmetteur;
            if ($ligneId && isset($allAgenceServices[$ligneId])) {
                $bonDeCaisseDto->serviceEmetteur = $allAgenceServices[$ligneId]['service_code'];
            }
        }

        // Changer le serviceDebiteur
        if ($bonDeCaisseDto->serviceDebiteur) {
            $ligneId = $bonDeCaisseDto->serviceDebiteur;
            if ($ligneId && isset($allAgenceServices[$ligneId])) {
                $bonDeCaisseDto->serviceDebiteur = $allAgenceServices[$ligneId]['service_code'];
            }
        }
    }
}
