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
use App\Service\TableauEnStringService;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\da\DaModel;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;

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

    public function __construct()
    {
        parent::__construct();
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
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

        $datas =  $this->daListeCdeFrnModel->getInfoCdeFrn($criteria, $numDitString, $numOrValideZstString);

        $datas = $this->ajouterNumDa($datas);
        $datas = $this->ajoutStatutBc($datas);
        $datas = $this->ajouterNbrJoursDispo($datas);

        return $datas;
    }

    private function ajouterNumDa(array $datas)
    {
        foreach ($datas as $key => $data) {
            $numDa = $this->demandeApproRepository->getNumDa($data['num_dit']);
            $datas[$key]['num_da'] = $numDa;
        }
        return $datas;
    }

    private function ajoutStatutBc(array $datas)
    {
        foreach ($datas as $key => $data) {

            $statutBc = $this->statutBc($data['reference'], $data['num_dit'], $data['num_cde']);
            $data[$key]['statut_bc'] = $statutBc;
        }
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

        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        // modification de statut dal
        $dal = $this->demandeApproLRepository->findOneBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        if ($dal) {
            $dal->setStatutDal(self::STATUT_ENVOYE_FOURNISSEUR);
            self::$em->persist($dal);
            self::$em->flush();
        }

        // modification de statut da
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        if ($da) {
            $da->setStatutDal(self::STATUT_ENVOYE_FOURNISSEUR);
            self::$em->persist($da);
            self::$em->flush();
        }

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
