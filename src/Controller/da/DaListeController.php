<?php

namespace App\Controller\da;

use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
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
    private DemandeApproLRepository $daLRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daLRepository = self::$em->getRepository(DemandeApproL::class);
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

        $this->sessionService->remove('firstCharge');
        
        $das = $this->daRepository->findDaData($criteria);
        $this->deleteDal($das);


        $this->ajoutInfoDit($das);
        $dataFiltered  = $this->filtreDal($das);


        self::$twig->display('da/list.html.twig', [
            'data' => $dataFiltered,
            'form' => $form->createView(),
            'serviceAtelier' => $this->estUserDansServiceAtelier(),
            'serviceAppro' => $this->estUserDansServiceAppro(),
        ]);
    }

    /**
     * supprime les ligne de DAl qui est dupliquer mais pas modifier (l'utilisateur ne clique pas sur le bouton modifier)
     *
     * @param array $das
     * @return void
     */
    private function deleteDal(array $das): void
    {
        foreach ($das as $da) {
            foreach ($da->getDAL() as $dal) {
                if ($dal->getEdit() !== 3 && $dal->getEdit() !== 0 && !is_null($dal->getEdit())) {
                    $demandeAppro = $dal->getDemandeAppro();
                    $demandeAppro->removeDAL($dal); // supprime le lien
                    self::$em->remove($dal); // supprime l'entité

                    self::$em->flush();
                }
            }
        }
    }

    private function filtreDal(array $das): array
    {
        foreach ($das as $da) {
            $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($da->getNumeroDemandeAppro());
            // filtre une collection de versions selon le numero de version max
            $dernieresVersions = $da->getDAL()->filter(function ($item) use ($numeroVersionMax) {
                return $item->getNumeroVersion() == $numeroVersionMax;
            });
            $da->setDAL($dernieresVersions);
        }


        return $das;
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
