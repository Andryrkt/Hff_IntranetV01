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

        $bonCaisseSearch = new BonDeCaisseDto();

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
            if ($dateDemande) {
                $bonCaisseSearch->dateDemande = $dateDemande['debut'];
                $bonCaisseSearch->dateDemandeFin = $dateDemande['fin'];
            }
        }


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
        $bonCaisseEntitySearch->setNomValidateurFinal($bonCaisseSearch->nomValidateurFinal);



        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $repository = $this->getEntityManager()->getRepository(BonDeCaisse::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $bonCaisseEntitySearch, $this->getUser());

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

        /** Récupère les critères dans la session @var array $criteira*/
        $criteria = $this->sessionService->get('bon_caisse_search_criteria', []);

        $bonCaisseSearch = new BonDeCaisseDto();
        $bonCaisseSearch->toObject($criteria);

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
        $bonCaisseEntitySearch->setNomValidateurFinal($bonCaisseSearch->nomValidateurFinal);

        // Récupère les entités filtrées
        $entities = $this->getEntityManager()->getRepository(BonDeCaisse::class)->findAndFilteredExcel($bonCaisseEntitySearch, $this->getUser());

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = [
            "Statut",
            "Numéro demande",
            "Date demande",
            "Type de paiement",
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
                $entity->getTypePaiement(),
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
        $this->getExcelService()->createSpreadsheet($data);
    }
}
