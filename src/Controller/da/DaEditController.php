<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLTemporaire;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLTemporaireRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaEditController extends Controller
{
    private const ID_ATELIER = 3;
    private const DA_STATUT = 'soumis à l’appro';
    private const EDIT_DELETE = 2;
    private const EDIT_MODIF = 3;
    private const EDIT_LOADED_PAGE = 1;

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
     * @Route("/edit/{id}", name="da_edit")
     */
    public function edit(int $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $dit = $this->ditRepository->find($id); // recupération du DIT
        $demandeAppro = $this->daRepository->findOneBy(['numeroDemandeDit' => $dit->getNumeroDemandeIntervention()]); // recupération de la DA associée au DIT
        $numDa = $demandeAppro->getNumeroDemandeAppro();

        if (!$this->sessionService->has('firstCharge')) {
            $this->sessionService->set('firstCharge', true);
            $this->duplicationDataDaL($numDa); // on duplique les lignes de la DA 
        }
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max


        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $demandeAppro);

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        self::$twig->display('da/edit.html.twig', [
            'form'              => $form->createView(),
            'observations'      => $observations,
            'peutModifier'      => $this->PeutModifier($demandeAppro),
            'idDit'             => $id,
            'numeroVersionMax'  => $numeroVersionMax,
            'numDa'             => $numDa,
        ]);
    }


    /**
     * @Route("/delete-edit-ligne/{ligne}/{idDit}/{numeroVersionMax}", name="da_edit_delete_ligne")
     */
    public function deleteLigne(int $ligne, int $idDit, int $numeroVersionMax)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $demandeApproLs = $this->daLRepository->findBy(['numeroLigne' => $ligne, 'numeroVersion' => $numeroVersionMax]); // recupération de la ligne à supprimer

        $this->modificationEdit($demandeApproLs, self::EDIT_DELETE);

        foreach ($demandeApproLs as $demandeApproL) {
            $demandeAppro = $demandeApproL->getDemandeAppro();
            $demandeAppro->removeDAL($demandeApproL); // supprime le lien
            self::$em->remove($demandeApproL); // supprime l'entité
        }

        self::$em->flush();

        return $this->redirectToRoute('da_edit', ['id' => $idDit]);
    }

    private function modificationEdit($demandeApproLs, $numero)
    {
        foreach ($demandeApproLs as $demandeApproL) {
            $demandeApproL->setEdit($numero); // Indiquer que c'est une version modifiée
            self::$em->persist($demandeApproL); // on persiste la DA
        }

        self::$em->flush(); // on enregistre les modifications
    }
    /**
     * Dupliquer les lignes de la table demande_appro_L
     *
     * @param array $refs
     * @param [type] $data
     * @return array
     */
    private function duplicationDataDaL($numDa): void
    {
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($numDa);
        $dals = $this->daLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);

        foreach ($dals as $dal) {
            // On clone l'entité (copie en mémoire)
            $newDal = clone $dal;
            $newDal->setNumeroVersion($this->autoIncrement($dal->getNumeroVersion())); // Incrémenter le numéro de version
            $newDal->setEdit(self::EDIT_LOADED_PAGE); // Indiquer que c'est une version modifiée

            // Doctrine crée un nouvel ID automatiquement (ne pas setter manuellement)
            self::$em->persist($newDal);
        }

        self::$em->flush();
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

    private function PeutModifier($demandeAppro)
    {
        return ($this->estUserDansServiceAtelier() && $demandeAppro->getStatutDal() == self::DA_STATUT);
    }

    private function estUserDansServiceAtelier()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }

    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro = $form->getData();
            $this->modificationDa($demandeAppro);

            //notification
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("da_list");
        }
    }

    private function modificationDa(DemandeAppro $demandeAppro): void
    {
        self::$em->persist($demandeAppro); // on persiste la DA
        $this->modificationDAL($demandeAppro);
        self::$em->flush(); // on enregistre les modifications
    }

    private function modificationDAL($demandeAppro)
    {
        $demandeApproLs = $demandeAppro->getDAL();
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        foreach ($demandeApproLs as $ligne => $demandeApproL) {
            $demandeApproL
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setNumeroLigne($ligne + 1)
                ->setStatutDal(self::DA_STATUT)
                ->setEdit(self::EDIT_MODIF) // Indiquer que c'est une version modifiée
                ->setNumeroVersion($numeroVersionMax)
            ; // Incrémenter le numéro de version
            self::$em->persist($demandeApproL); // on persiste la DA
        }
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }
}
