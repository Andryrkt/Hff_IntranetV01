<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Form\da\CdeFrnListType;
use App\Form\da\DaSoumissionType;
use App\Model\da\DaListeCdeFrnModel;
use App\Repository\da\DaSoumissionBcRepository;
use App\Service\TableauEnStringService;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListCdeFrnController extends Controller
{
    private DaListeCdeFrnModel $daListeCdeFrnModel;
    private DemandeApproRepository $demandeApproRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
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
        $datas = $this->ajouterNumDa($datas);
        $datas = $this->ajoutStatutBc($datas);
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
        return $this->daListeCdeFrnModel->getInfoCdeFrn($numDitString, $criteria);
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
            $statutBc = $this->daSoumissionBcRepository->getStatut($data['num_cde']);
            $datas[$key]['statut_bc'] = $statutBc;
        }
        return $datas;
    }

    private function traitementFormulaireSoumission(Request $request, $formSoumission): void
    {
        $formSoumission->handleRequest($request);

        if ($formSoumission->isSubmitted() && $formSoumission->isValid()) {
            $soumission = $formSoumission->getData();

            if ($soumission['soumission'] === true) {
                $this->redirectToRoute("da_soumission_bc", ['numCde' => $soumission['commande_id']]);
            } else {
                $this->redirectToRoute("da_soumission_bc", ['numCde' => $soumission['commande_id']]);
            }
        }
    }
}
