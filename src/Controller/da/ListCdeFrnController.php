<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Form\da\CdeFrnListType;
use App\Model\da\DaListeCdeFrnModel;
use App\Service\TableauEnStringService;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListCdeFrnController extends Controller
{
    private DaListeCdeFrnModel $daListeCdeFrnModel;
    private DemandeApproRepository $demandeApproRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daListeCdeFrnModel = new DaListeCdeFrnModel();
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
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

        $form->handleRequest($request);
        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        $numDits = $this->demandeApproRepository->getNumDit();
        $numDitString = TableauEnStringService::TableauEnString(',', $numDits);
        // dd($numDitString);
        $datas = $this->daListeCdeFrnModel->getInfoCdeFrn($numDitString, $criteria);
        foreach ($datas as $data) {
            $numDa = $this->demandeApproRepository->getNumDa($data['num_dit']);
            ['num_da' => $numDa] + $data;
        }
        // dd($datas);

        self::$twig->display('da/list-cde-frn.html.twig', [
            'data' => $datas,
            'form' => $form->createView(),
        ]);
    }
}
