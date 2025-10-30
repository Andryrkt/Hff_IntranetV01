<?php

namespace App\Controller\bdc;

use App\Controller\Controller;
use App\Dto\bdc\BonDeCaisseDto;
use App\Entity\bdc\BonDeCaisse;
use App\Entity\admin\Application;
use App\Form\bdc\BonDeCaisseType;
use App\Entity\admin\AgenceServiceIrium;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\bdc\BonDeCaisseListeTrait; // Ajouter cette ligne à la place
use App\Factory\bdc\BonDeCaisseFactory;

/**
 * @Route("/compta/demande-de-paiement")
 */
class BonDeCaisseController extends Controller
{
    use ConversionTrait;
    use BonDeCaisseListeTrait;
    use FormatageTrait;
    use AutorisationTrait;

    /**
     * Affiche la liste des bons de caisse
     * @Route("/bon-caisse-liste", name="bon_caisse_liste")
     */
    public function listeBonCaisse(Request $request)
    {
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_BCS);

        $bonCaisseSearch = new \App\Dto\bdc\BonDeCaisseDto();

        $hasGetParams = !empty($request->query->all());
        if (!$hasGetParams) {
            $this->sessionService->remove('bon_caisse_search_criteria');
        } else {
            $sessionCriteria = $this->sessionService->get('bon_caisse_search_criteria', []);
            if (!empty($sessionCriteria)) {
                foreach ($sessionCriteria as $key => $value) {
                    if (property_exists($bonCaisseSearch, $key)) {
                        $bonCaisseSearch->$key = $value;
                    }
                }
            }
        }

        $form = $this->getFormFactory()->createBuilder(BonDeCaisseType::class, $bonCaisseSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        $form->handleRequest($request);

        $options = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $bonCaisseSearch = $form->getData();

            $emetteurData = $form->get('emetteur')->getData();
            if ($emetteurData) {
                $bonCaisseSearch->agenceEmetteur = isset($emetteurData['agence']) ? $emetteurData['agence']->getCodeAgence() : null;
                $bonCaisseSearch->serviceEmetteur = isset($emetteurData['service']) ? $emetteurData['service']->getCodeService() : null;
            }

            $debiteurData = $form->get('debiteur')->getData();
            if ($debiteurData) {
                $bonCaisseSearch->agenceDebiteur = isset($debiteurData['agence']) ? $debiteurData['agence']->getCodeAgence() : null;
                $bonCaisseSearch->serviceDebiteur = isset($debiteurData['service']) ? $debiteurData['service']->getCodeService() : null;
            }

            $dateDemande = $form->get('dateDemande')->getData();
            if($dateDemande) {
                $bonCaisseSearch->dateDemande = $dateDemande['debut'];
                $bonCaisseSearch->dateDemandeFin = $dateDemande['fin'];
            }

        }

        $bonCaisseSearch->dateDemandeFin = $form->has('dateDemandeFin') ? $form->get('dateDemandeFin')->getData() : null;

        $criteria = $bonCaisseSearch->toArray();
        $this->sessionService->set('bon_caisse_search_criteria', $criteria);

        $bonCaisseEntitySearch = new BonDeCaisse();
        $bonCaisseEntitySearch->setNumeroDemande($bonCaisseSearch->numeroDemande);
        $bonCaisseEntitySearch->setDateDemande($bonCaisseSearch->dateDemande);
        $bonCaisseEntitySearch->setDateDemandeFin($bonCaisseSearch->dateDemandeFin);
        $bonCaisseEntitySearch->setAgenceDebiteur($bonCaisseSearch->agenceDebiteur);
        $bonCaisseEntitySearch->setServiceDebiteur($bonCaisseSearch->serviceDebiteur);
        $bonCaisseEntitySearch->setAgenceEmetteur($bonCaisseSearch->agenceEmetteur);
        $bonCaisseEntitySearch->setServiceEmetteur($bonCaisseSearch->serviceEmetteur);
        $bonCaisseEntitySearch->setStatutDemande($bonCaisseSearch->statutDemande);
        $bonCaisseEntitySearch->setCaisseRetrait($bonCaisseSearch->caisseRetrait);
        $bonCaisseEntitySearch->setTypePaiement($bonCaisseSearch->typePaiement);
        $bonCaisseEntitySearch->setRetraitLie($bonCaisseSearch->retraitLie);



        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $repository = $this->getEntityManager()->getRepository(BonDeCaisse::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $bonCaisseEntitySearch);

        $bonDeCaisseFactory = new BonDeCaisseFactory();
        return $this->render(
            'bdc/bon_caisse_list.html.twig',
            [
                'form' => $form->createView(),
                'data' => $bonDeCaisseFactory->createFromEntities($paginationData['data']),
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'criteria' => $criteria,
            ]
        );
    }

    /**
     * @Route("/export-bon-caisse-excel", name="export_bon_caisse_excel")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        // Récupère les critères dans la session
        $criteria = $this->sessionService->get('bon_caisse_search_criteria', []);
        $option = $this->sessionService->get('bon_caisse_search_option', []);

        $bonCaisseSearch = new BonDeCaisse();
        $bonCaisseSearch->setTypeDemande($criteria['typeDemande'] ?? null)
            ->setNumeroDemande($criteria['numeroDemande'] ?? null)
            ->setDateDemande($criteria['dateDemande'] ?? null)
            ->setCaisseRetrait($criteria['caisseRetrait'] ?? null)
            ->setTypePaiement($criteria['typePaiement'] ?? null)
            ->setAgenceDebiteur($criteria['agenceDebiteur'] ?? null)
            ->setServiceDebiteur($criteria['serviceDebiteur'] ?? null)
            ->setRetraitLie($criteria['retraitLie'] ?? null)
            ->setMatricule($criteria['matricule'] ?? null)
            ->setAdresseMailDemandeur($criteria['adresseMailDemandeur'] ?? null)
            ->setMotifDemande($criteria['motifDemande'] ?? null)
            ->setMontantPayer($criteria['montantPayer'] ?? null)
            ->setDevise($criteria['devise'] ?? null)
            ->setStatutDemande($criteria['statutDemande'] ?? null)
            ->setDateStatut($criteria['dateStatut'] ?? null);

        // Récupère les entités filtrées
        $entities = $this->getEntityManager()->getRepository(BonDeCaisse::class)->findAndFilteredExcel($bonCaisseSearch, $option);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "Numéro demande",
            "Date demande",
            "Type de paiement",
            "Caisse de retrait",
            "Retrait lié à",
            "Agence/Service",
            "Adresse mail demandeur",
            "Montant",
            "Devise",
            "Motif"
        ];

        foreach ($entities as $entity) {
            // Récupérer les informations d'agence et service pour l'affichage
            $agenceService = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->findOneBy([
                'agence_ips' => $entity->getAgenceDebiteur(),
                'service_ips' => $entity->getServiceDebiteur()
            ]);

            $agenceServiceLibelle = '';
            if ($agenceService) {
                $agenceServiceLibelle = $agenceService->getNomagencei100() . ' - ' . $agenceService->getLibelleserviceips();
            }

            $data[] = [
                $entity->getStatutDemande(),
                $entity->getNumeroDemande(),
                $entity->getDateDemande() ? $entity->getDateDemande()->format('d/m/Y') : '',
                $entity->getTypePaiement(),
                $entity->getCaisseRetrait(),
                $entity->getRetraitLie(),
                $agenceServiceLibelle,
                $entity->getAdresseMailDemandeur(),
                $entity->getMontantPayer(),
                $entity->getDevise(),
                $entity->getMotifDemande()
            ];
        }

        // Crée le fichier Excel
        $this->excelService->createSpreadsheet($data);
    }
}
