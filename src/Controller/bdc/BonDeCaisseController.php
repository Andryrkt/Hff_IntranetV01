<?php

namespace App\Controller\bdc;

use App\Controller\Controller;
use App\Entity\admin\AgenceServiceIrium;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\bdc\BonDeCaisseListeTrait; // Ajouter cette ligne à la place
use App\Controller\Traits\FormatageTrait;
use App\Entity\bdc\BonDeCaisse;
use App\Form\bdc\BonDeCaisseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $autoriser = $this->autorisationRole($this->getEntityManager());
        $bonCaisseSearch = new BonDeCaisse();

        $agenceServiceIps = $this->agenceServiceIpsObjet();

        // Vérifier s'il y a des paramètres GET dans l'URL
        $hasGetParams = !empty($request->query->all());

        // Si aucun paramètre GET n'est présent, supprimer les sessions
        if (!$hasGetParams) {
            $this->sessionService->remove('bon_caisse_search_criteria');
            $this->sessionService->remove('bon_caisse_search_option');
        } else {
            // Si des paramètres GET sont présents, utiliser les valeurs de session
            $sessionCriteria = $this->sessionService->get('bon_caisse_search_criteria', []);
            $sessionOptions = $this->sessionService->get('bon_caisse_search_option', []);

            // Appliquer les critères de session à l'objet de recherche
            if (!empty($sessionCriteria)) {
                $bonCaisseSearch->setNumeroDemande($sessionCriteria['numeroDemande'] ?? null);
                $bonCaisseSearch->setDateDemande($sessionCriteria['dateDemande'] ?? null);
                $bonCaisseSearch->setAgenceDebiteur($sessionCriteria['agenceDebiteur'] ?? null);
                $bonCaisseSearch->setStatutDemande($sessionCriteria['statutDemande'] ?? null);
                $bonCaisseSearch->setCaisseRetrait($sessionCriteria['caisseRetrait'] ?? null);
                $bonCaisseSearch->setTypePaiement($sessionCriteria['typePaiement'] ?? null);
                $bonCaisseSearch->setRetraitLie($sessionCriteria['retraitLie'] ?? null);
            }
        }

        /** INITIALIASATION et REMPLISSAGE de RECHERCHE pendant la navigation pagination */
        $this->initialisation($bonCaisseSearch, $this->getEntityManager());

        // Création du formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(BonDeCaisseType::class, $bonCaisseSearch, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        $form->handleRequest($request);

        // Options pour le repository
        $options = [
            //'boolean' => $autoriser,
            //'idAgence' => $this->agenceIdAutoriser(self::$em)
        ];

        // Traitement du formulaire de recherche
        if ($form->isSubmitted() && $form->isValid()) {
            $bonCaisseSearch = $form->getData();

            // Récupérer la date de demande (début)
            $dateDemande = $bonCaisseSearch->getDateDemande();
            if ($dateDemande) {
                $options['dateDemande'] = $dateDemande;
            }

            // Récupérer la date de demande fin (non mappée)
            if ($form->has('dateDemandeFin')) {
                $dateDemandeFin = $form->get('dateDemandeFin')->getData();
                if ($dateDemandeFin) {
                    $options['dateDemandeFin'] = $dateDemandeFin;
                }
            }

            // Récupérer de l'agence  et le service directement depuis la requête au lieu du formulaire
            $agenceFromRequest = $request->query->get('bon_de_caisse')['agenceDebiteur'] ?? null;
            $serviceFromRequest = $request->query->get('bon_de_caisse')['service'] ?? null;
            if ($serviceFromRequest && $agenceFromRequest) {
                $options['agenceDebiteur'] = $agenceFromRequest;
                $options['service'] = $serviceFromRequest;
            }

        }


        $criteria = [];
        //transformer l'objet bonCaisseSearch en tableau
        $criteria = $bonCaisseSearch->toArray();

        // Ajouter les options non mappées aux critères
        if (isset($options['dateDemandeFin'])) {
            $criteria['dateDemandeFin'] = $options['dateDemandeFin'];
        }
        if (isset($options['service']) && isset($options['agenceDebiteur'])) {
            $criteria['agenceDebiteur'] = $options['agenceDebiteur'];
            $criteria['service'] = $options['service'];
        }
        // Récupérer les données paginées et filtrées
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $repository = $this->getEntityManager()->getRepository(BonDeCaisse::class);
        $paginationData = $repository->findPaginatedAndFiltered($page, $limit, $bonCaisseSearch, $options);

        //enregistre le critère dans la session
        $this->sessionService->set('bon_caisse_search_criteria', $criteria);
        $this->sessionService->set('bon_caisse_search_option', $options);

        $criteriaTab = $criteria;

        $criteriaTab['statutDemande'] = $criteria['statutDemande'] ?? null;
        $criteriaTab['dateDemande'] = $criteria['dateDemande'] ? $criteria['dateDemande']->format('d-m-Y') : null;
        $criteriaTab['dateDemandeFin'] = isset($criteria['dateDemandeFin']) && $criteria['dateDemandeFin'] ? $criteria['dateDemandeFin']->format('d-m-Y') : null;
        $criteriaTab['service'] = $criteria['service'] ?? null;

        // Filtrer les critères pour supprimer les valeurs "falsy"
        $filteredCriteria = array_filter($criteriaTab);

        return $this->render(
            'bdc/bon_caisse_list.html.twig',
            [
                'form' => $form->createView(),
                'data' => $paginationData['data'],
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
