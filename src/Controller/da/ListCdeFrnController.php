<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Form\da\CdeFrnListType;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Form\da\DaSoumissionType;
use App\Model\da\DaListeCdeFrnModel;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeApproLR;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\da\DaModel;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\dit\DitRepository;

class ListCdeFrnController extends Controller
{
    use DaTrait;

    private const STATUT_ENVOYE_FOURNISSEUR = 'BC envoyé au fournisseur';

    private DaListeCdeFrnModel $daListeCdeFrnModel;
    private DemandeApproRepository $demandeApproRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DitRepository $ditRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
    }

    /** 
     * @Route(path="/demande-appro/liste-commande-fournisseurs", name="list_cde_frn") 
     **/
    public function listCdeFrn(Request $request)
    {
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(CdeFrnListType::class, null, [
            'method' => 'GET',
        ])->getForm();

        $criteria = $this->traitementFormulaireRecherche($request, $form);
        $datas = $this->recuperationDonner($criteria);
        // dd($datas);


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
        $numDits = $this->demandeApproRepository->getNumDit();
        $numDitString = TableauEnStringService::TableauEnString(',', $numDits);

        $numOrValide = $this->ditOrsSoumisAValidationRepository->findNumOrValide();
        $numOrString = TableauEnStringService::TableauEnString(',', $numOrValide);
        $numOrValideZst = $this->daListeCdeFrnModel->getNumOrValideZst($numOrString);
        $numOrValideZstString = TableauEnStringService::TableauEnString(',', $numOrValideZst);

        //recupération des données dans IPS
        $datas =  $this->daListeCdeFrnModel->getInfoCdeFrn($criteria, $numDitString, $numOrValideZstString);

        //ajout des données utile
        $datas = $this->ajouterNumDa($datas);
        $datas = $this->ajoutDateFinSouhaite($datas);
        $datas = $this->ajoutStatutBc($datas);
        $datas = $this->ajouterNbrJoursDispo($datas);
        $datas = $this->ajoutniveauUrgence($datas);

        //filtre des données ajouter
        $datas = $this->filtreDonnee($datas, $criteria);

        // dd($datas);
        return $datas;
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

    private function ajoutDateFinSouhaite(array $datas)
    {
        foreach ($datas as $key => $data) {
            $numDa = $data['num_da'];
            $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
            $donners = $this->recuperationRectificationDonnee($numDa, $numeroVersionMax);
            foreach ($donners as $donner) {
                $datas[$key]['date_fin_souhaite'] = $donner->getDateFinSouhaite();
            }
        }

        return $datas;
    }

    private function ajoutniveauUrgence(array $datas)
    {
        //ajout du niveau d'urgence
        foreach ($datas as $key => $data) {
            $nivUrg = $this->ditRepository->getNiveauUrgence($data['num_dit']);
            $datas[$key]['niv_urg'] = $nivUrg;
        }

        //return du nouveau donnée
        return $datas;
    }

    private function ajouterNumDa(array $datas)
    {
        //ajout du numero demande appro
        foreach ($datas as $key => $data) {
            $numDa = $this->demandeApproRepository->getNumDa($data['num_dit']);
            $datas[$key]['num_da'] = $numDa;
        }

        //return du nouveau donnée
        return $datas;
    }

    private function ajoutStatutBc(array $datas)
    {
        //ajout du statut BC
        foreach ($datas as $key => $data) {

            $statutBc = $this->statutBc($data['reference'], $data['num_dit'], $data['num_cde']);
            $datas[$key]['statut_bc'] = $statutBc;
        }

        //return du nouveau Donnée
        return $datas;
    }


    private function ajouterNbrJoursDispo(array $datas)
    {
        foreach ($datas as $key => $data) {
            $nbrJoursDispo = $this->demandeApproLRepository->getJoursDispo($data['num_da'], $data['reference']);
            $datas[$key]['jours_dispo'] = $nbrJoursDispo;
        }
        return $datas;
    }

    private function traitementFormulaireSoumission(Request $request, $formSoumission): void
    {
        $formSoumission->handleRequest($request);

        if ($formSoumission->isSubmitted() && $formSoumission->isValid()) {
            $soumission = $formSoumission->getData();

            if ($soumission['soumission'] === true) {
                $this->redirectToRoute("da_soumission_bc", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id']]);
            } else {
                $this->redirectToRoute("da_soumission_FacBl", ['numCde' => $soumission['commande_id'], 'numDa' => $soumission['da_id']]);
            }
        }
    }

    /**
     * @Route(path="/demande-appro/changement-statuts-envoyer-fournisseur/{numCde}/{numDa}", name="changement_statut_envoyer_fournisseur")
     *
     * @return void
     */
    public function changementStatutEnvoyerFournisseur(string $numCde = '', string $numDa = '')
    {
        $this->verifierSessionUtilisateur();

        // modification de statut soumission bc
        $numVersionMax = $this->daSoumissionBcRepository->getNumeroVersionMax($numCde);
        $soumissionBc = $this->daSoumissionBcRepository->findOneBy(['numeroCde' => $numCde, 'numeroVersion' => $numVersionMax]);
        if ($soumissionBc) {
            $soumissionBc->setStatut(self::STATUT_ENVOYE_FOURNISSEUR);
            self::$em->persist($soumissionBc);
            self::$em->flush();
        }

        $this->sessionService->set('notification', ['type' => 'success', 'message' => 'statut modifié avec succès.']);
        $this->redirectToRoute("list_cde_frn");
    }
}
