<?php

namespace App\Controller\da;

use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/demande-appro")
 */
class DaListeController extends Controller
{
    private const ID_ATELIER = 3;
    private const ID_APPRO = 16;

    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/list", name="da_list")
     */
    public function listeDA(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(DaSearchType::class, null, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $das = $this->daRepository->findDaData($criteria);

        $this->ajoutInfoDit($das);

        $dataFiltered = $this->filtrerDalParNumVersion($das);

        self::$twig->display('da/list.html.twig', [
            'data' => $dataFiltered,
            'form' => $form->createView(),
            'serviceAtelier' => $this->estUserDansServiceAtelier(),
            'serviceAppro' => $this->estUserDansServiceAppro(),
        ]);
    }

    private function filtrerDalParNumVersion($das): array
    {
        /** @var DemandeAppro[] $datas */
        $dataFiltered = [];

        foreach ($das as $da) {
            $grouped = [];
            foreach ($da->getDAL() as $dal) {
                $numLigne = $dal->getNumeroLigne();
                $version = $dal->getNumeroVersion();

                if (!isset($grouped[$numLigne]) || $version > $grouped[$numLigne]->getNumeroVersion()) {
                    $grouped[$numLigne] = $dal;
                }
            }
            // Nouvelle entrée structurée : l'objet DemandeAppro + ses DAL filtrés
            $dataFiltered[] = [
                'da' => $da,
                'DAL' => array_values($grouped),
            ];
        }

        return $dataFiltered;
    }


    private function ajoutInfoDit(array $datas): void
    {
        foreach ($datas as $data) {
            $data->setDit($this->ditRepository->findOneBy(['numeroDemandeIntervention' => $data->getNumeroDemandeDit()]));
        }
    }


    private function estUserDansServiceAtelier()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }

    private function estUserDansServiceAppro()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_APPRO, $serviceIds);
    }
}
