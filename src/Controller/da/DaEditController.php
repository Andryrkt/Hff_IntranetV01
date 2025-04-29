<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaEditController extends Controller
{
    private const ID_ATELIER = 3;

    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;
    private DaObservationRepository $daObservationRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
    }

    /**
     * @Route("/edit/{id}", name="da_edit")
     */
    public function edit($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $dit = $this->ditRepository->find($id);
        $demandeAppro = $this->daRepository->findOneBy(['numeroDemandeDit' => $dit->getNumeroDemandeIntervention()]);
        $demandeAppro->setDit($dit);

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->numeroDemandeAppro()], ['dateCreation' => 'DESC']);

        self::$twig->display('da/edit.html.twig', [
            'form' => $form->createView(),
            'observations' => $observations,
            'peutModifier' => $this->PeutModifier($demandeAppro)
        ]);
    }

    private function PeutModifier($demandeAppro)
    {
        return ($this->estUserDansServiceAtelier() && $demandeAppro->getStatutDal() == 'soumis à l’appro');
    }

    private function estUserDansServiceAtelier()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }
}
