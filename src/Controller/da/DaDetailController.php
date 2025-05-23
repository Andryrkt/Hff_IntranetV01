<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaDetailController extends Controller
{
    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;
    private DaObservationRepository $daObservationRepository;
    private DemandeApproLRepository $daLRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
        $this->daLRepository = self::$em->getRepository(DemandeApproL::class);
    }

    /**
     * @Route("/detail/{id}", name="da_detail")
     */
    public function deatil(int $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $dit = $this->ditRepository->find($id); // recupération du DIT
        $demandeAppro = $this->daRepository->findOneBy(['numeroDemandeDit' => $dit->getNumeroDemandeIntervention()]); // recupération de la DA associée au DIT
        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max


        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();


        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        self::$twig->display('da/detail.html.twig', [
            'form'              => $form->createView(),
            'observations'      => $observations,
            'idDit'             => $id,
            'numeroVersionMax'  => $numeroVersionMax,
            'numDa'             => $numDa,
            'nomFichierRefZst' => $demandeAppro->getNonFichierRefZst()
        ]);
    }

    private function filtreDal($demandeAppro, $dit, int $numeroVersionMax): DemandeAppro
    {
        $demandeAppro->setDit($dit); // association de la DA avec le DIT

        // filtre une collection de versions selon le numero de version max

        $dernieresVersions = $demandeAppro->getDAL()->filter(function ($item) use ($numeroVersionMax) {
            return $item->getNumeroVersion() == $numeroVersionMax;
        });
        $demandeAppro->setDAL($dernieresVersions); // on remplace la collection de versions par la collection filtrée

        return $demandeAppro;
    }
}
