<?php

namespace App\Controller\da;

use App\Model\da\DaModel;
use App\Entity\da\DaValider;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeApproLR;
use App\Model\da\DaListeCdeFrnModel;
use App\Controller\Traits\da\DaTrait;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Form\da\daCdeFrn\CdeFrnListType;
use App\Form\da\daCdeFrn\DaCdeEnvoyerType;
use App\Form\da\daCdeFrn\DaSoumissionType;
use App\Repository\da\DaValiderRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

class ListCdeFrnController extends Controller
{
    use DaTrait;

    private const STATUT_ENVOYE_FOURNISSEUR = 'BC envoyé au fournisseur';

    private DaListeCdeFrnModel $daListeCdeFrnModel;
    private DemandeApproRepository $daRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DitRepository $ditRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DaValiderRepository $daValiderRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->daValiderRepository = self::$em->getRepository(DaValider::class);
    }

    /** 
     * @Route(path="/demande-appro/liste-commande-fournisseurs", name="list_cde_frn") 
     **/
    public function listCdeFrn(Request $request)
    {
        $this->verifierSessionUtilisateur();

        /** Formulaire pour la recherche */
        $form = self::$validator->createBuilder(CdeFrnListType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $criteria = $this->traitementFormulaireRecherche($request, $form);

        /** Les données à afficher */
        $datas = $this->recuperationDonner($criteria);
        // dd($datas);

        /** Formulaire pour l'envoie de BC et FAC + Bl */
        $formSoumission = self::$validator->createBuilder(DaSoumissionType::class, null, [
            'method' => 'GET',
        ])->getForm();
        $this->traitementFormulaireSoumission($request, $formSoumission);

        self::$twig->display('da/list-cde-frn.html.twig', [
            'data' => $datas,
            'form' => $form->createView(),
            'formSoumission' => $formSoumission->createView(),
        ]);
    }

    private function traitementFormulaireRecherche(Request $request, $form): array
    {
        $form->handleRequest($request);
        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        return $criteria;
    }

    private function recuperationDonner(array $criteria): array
    {
        $numDits = $this->daRepository->getNumDit();
        $numDitString = TableauEnStringService::TableauEnString(',', $numDits);

        $numOrValide = $this->ditOrsSoumisAValidationRepository->findNumOrValide();
        $numOrString = TableauEnStringService::TableauEnString(',', $numOrValide);
        $numOrValideZst = $this->daListeCdeFrnModel->getNumOrValideZst($numOrString);
        $numOrValideZstString = TableauEnStringService::TableauEnString(',', $numOrValideZst);

        //recupération des données dans IPS
        $datas =  $this->daListeCdeFrnModel->getInfoCdeFrn($criteria, $numDitString, $numOrValideZstString);

        //ajout des données utile
        $datas = $this->ajoutDonnerUtile($datas, $criteria);

        //filtre des données ajouter
        $datas = $this->filtreDonnee($datas, $criteria);

        // dd($datas);
        return $datas;
    }

    private function ajoutDonnerUtile(array $datas, array $criteria): array
    {
        foreach ($datas as $key => $data) {
            $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMaxDit($data['num_dit']);
            $daValider = $this->daValiderRepository->getDaValider(
                $numeroVersionMax,
                $data['num_dit'],
                $data['reference'],
                $data['designation'],
                $criteria
            );
            if ($daValider) {
                //modification statut bc ou cde dans la table da_valider
                $this->modificationStatutDaValider($daValider, $data['num_cde']);

                //ajout du numero demande appro
                $datas[$key]['num_da'] = $daValider->getNumeroDemandeAppro();

                //ajout du niveau d'urgence
                $datas[$key]['niv_urg'] = $daValider->getNiveauUrgence();

                //ajout de la date fin souhaité
                $datas[$key]['date_fin_souhaite'] = $daValider->getDateFinSouhaite();

                //ajout du statut BC
                $datas[$key]['statut_bc'] = $daValider->getStatutCde();

                //ajout du nombre de jours dispo
                $datas[$key]['jours_dispo'] = $daValider->getJoursDispo();

                //ajout date livraison prévu
                $datas[$key]['date_livraison_prevue'] = $daValider->getDateLivraisonPrevue();

                //ajout demandeur de demande appro
                $datas[$key]['demandeur'] = $daValider->getDemandeur();

                //ajout de l'id de la DIT
                $datas[$key]['id_dit'] = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $daValider->getNumeroDemandeDit()])->getId();
            }
        }

        return $datas;
    }

    private function modificationStatutDaValider(DaValider $daValider, ?string $numCde)
    {
        $numCde ? $numCde : '';
        $statutBc = $this->statutBc($daValider->getArtRefp(), $daValider->getNumeroDemandeDit(), $daValider->getArtDesi());
        $daValider
            ->setStatutCde($statutBc)
            ->setNumeroCde($numCde)
        ;
        self::$em->persist($daValider);
        self::$em->flush();
    }

    private function filtreDonnee(array $datas, array $criteria = [])
    {
        //filtre du niceau d'urgence
        if (!empty($criteria['niveauUrgence'])) {
            $filtreNivUrg = $criteria['niveauUrgence']->getDescription();

            $datas = array_values(array_filter($datas, function ($item) use ($filtreNivUrg) {
                return isset($item['niv_urg']) && $item['niv_urg'] === $filtreNivUrg;
            }));
        }

        //filtres sur le numero demande appro
        if (!empty($criteria['numDa'])) {
            $filtreNumDa = $criteria['numDa'];

            $datas = array_values(array_filter($datas, function ($item) use ($filtreNumDa) {
                return isset($item['num_da']) && $item['num_da'] === $filtreNumDa;
            }));
        }

        //Filtre sur le stattu BC
        if (!empty($criteria['statutBc'])) {
            $filtreStatutBc = $criteria['statutBc'];

            $datas = array_values(array_filter($datas, function ($item) use ($filtreStatutBc) {
                return isset($item['statut_bc']) && $item['statut_bc'] === $filtreStatutBc;
            }));
        }

        //Filtre sur la date de debut date fin souhaité
        if (!empty($criteria['dateDebutDAL'])) {
            $filtreDateDebutFinSouhaite = $criteria['dateDebutDAL']->format('Y-m-d');

            $datas = array_values(array_filter($datas, function ($item) use ($filtreDateDebutFinSouhaite) {
                return isset($item['date_fin_souhaite']) && $item['date_fin_souhaite'] <= $filtreDateDebutFinSouhaite;
            }));
        }
        //Filtre sur la date de fin date fin souhaité
        if (!empty($criteria['dateFinDAL'])) {
            $filtreDateFinFinSouhaite = $criteria['dateFinDAL']->format('Y-m-d');

            $datas = array_values(array_filter($datas, function ($item) use ($filtreDateFinFinSouhaite) {
                return isset($item['date_fin_souhaite']) && $item['date_fin_souhaite'] <= $filtreDateFinFinSouhaite;
            }));
        }

        return $datas;
    }


    private function traitementFormulaireSoumission(Request $request, $formSoumission): void
    {
        $formSoumission->handleRequest($request);

        if ($formSoumission->isSubmitted() && $formSoumission->isValid()) {
            $soumission = $formSoumission->getData();

            if ($soumission['soumission'] === true) {
                $this->redirectToRoute("da_soumission_bc", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']]);
            } else {
                $this->redirectToRoute("da_soumission_FacBl", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id'], 'numOr' => $soumission['num_or']]);
            }
        }
    }

    /**
     * @Route(path="/demande-appro/changement-statuts-envoyer-fournisseur/{numCde}/{datePrevue}/{estEnvoyer}", name="changement_statut_envoyer_fournisseur")
     *
     * @return void
     */
    public function changementStatutEnvoyerFournisseur(string $numCde = '', string $datePrevue = '', bool $estEnvoyer = false)
    {
        $this->verifierSessionUtilisateur();

        if ($estEnvoyer) {
            // modification de statut dans la soumission bc
            $numVersionMaxSoumissionBc = $this->daSoumissionBcRepository->getNumeroVersionMax($numCde);
            $soumissionBc = $this->daSoumissionBcRepository->findOneBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxSoumissionBc]);
            if ($soumissionBc) {
                $soumissionBc->setStatut(self::STATUT_ENVOYE_FOURNISSEUR);
                self::$em->persist($soumissionBc);
                self::$em->flush();
            }

            //modification dans la table da_valider
            $numVersionMaxDaValider = $this->daValiderRepository->getNumeroVersionMaxCde($numCde);
            $daValider = $this->daValiderRepository->findBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMaxDaValider]);
            foreach ($daValider as $valider) {
                $valider->setStatutCde(self::STATUT_ENVOYE_FOURNISSEUR)
                    ->setDateLivraisonPrevue(new \DateTime($datePrevue))
                ;
                self::$em->persist($valider);
            }

            // envoyer une notification de succès
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'statut modifié avec succès.']);
            $this->redirectToRoute("list_cde_frn");
        } else {
            $this->sessionService->set('notification', ['type' => 'error', 'message' => 'Erreur lors de la modification du statut... vous n\'avez pas cocher la cage à cocher.']);
            $this->redirectToRoute("list_cde_frn");
        }
    }
}
