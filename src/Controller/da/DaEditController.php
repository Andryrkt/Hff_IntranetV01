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

    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;
    private DaObservationRepository $daObservationRepository;
    private DemandeApproLRepository $daLRepository;
    private DemandeApproLTemporaireRepository $daLTemporaireRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
        $this->daLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->daLTemporaireRepository = self::$em->getRepository(DemandeApproLTemporaire::class);
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
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max
        $this->transferetDansDalTemporaire($demandeAppro->getDAL());

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $demandeAppro);

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        self::$twig->display('da/edit.html.twig', [
            'form'          => $form->createView(),
            'observations'  => $observations,
            'peutModifier'  => $this->PeutModifier($demandeAppro),
            'numeroVersionMax' => $numeroVersionMax,
            'idDit' => $id,
        ]);
    }


    /**
     * @Route("/delete-edit-ligne/{ligne}/{numeroVersion}/{idDit}", name="da_edit_delete_ligne")
     */
    public function deleteLigne(int $ligne, int $numeroVersion, int $idDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $demandeApproLs = $this->daLRepository->findBy(['numeroLigne' => $ligne, 'numeroVersion' => $numeroVersion]); // recupération de la ligne à supprimer

        foreach ($demandeApproLs as $demandeApproL) {
            $demandeAppro = $demandeApproL->getDemandeAppro();
            $demandeAppro->removeDAL($demandeApproL); // supprime le lien
            self::$em->remove($demandeApproL); // supprime l'entité
        }

        self::$em->flush();

        return $this->redirectToRoute('da_edit', ['id' => $idDit]);
    }

    /**
     * Dupliquer les lignes de la table demande_appro_L
     *
     * @param array $refs
     * @param [type] $data
     * @return array
     */
    private function duplicationDataDaL($data): void
    {
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($data[0]->getNumeroDemandeAppro());
        $dals = $this->daLRepository->findBy(['numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);

        foreach ($dals as $dal) {
            // On clone l'entité (copie en mémoire)
            $newDal = clone $dal;
            $newDal->setNumeroVersion($this->autoIncrement($dal->getNumeroVersion())); // Incrémenter le numéro de version

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
            // $this->modificationDa($demandeAppro);


            $demandeApproLs = $demandeAppro->getDAL();

            foreach ($demandeApproLs as $demandeApproL) {
                $dal = new DemandeApproL();
                dump($demandeApproL);
            }
            die();
        }
    }

    private function modificationDa(DemandeAppro $demandeAppro): void
    {
        self::$em->persist($demandeAppro); // on persiste la DA
        self::$em->flush(); // on enregistre les modifications
    }

    /**
     * transferet Dans DalTemporaire
     * @param [type] $dals
     * @return void
     */
    private function transferetDansDalTemporaire($dals)
    {
        foreach ($dals as $dal) {
            $dalTemporaire = new DemandeApproLTemporaire();
            $dalTemporaire
                ->setNumeroDemandeAppro($dal->getNumeroDemandeAppro())
                ->setNumeroLigne($dal->getNumeroLigne())
                ->setArtRempl($dal->getArtRempl())
                ->setQteDem($dal->getQteDem())
                ->setQteDispo($dal->getQteDispo())
                ->setArtConstp($dal->getArtConstp())
                ->setArtRefp($dal->getArtRefp())
                ->setArtDesi($dal->getArtDesi())
                ->setArtFams1($dal->getArtFams1())
                ->setArtFams2($dal->getArtFams2())
                ->setCodeFams1($dal->getCodeFams1())
                ->setCodeFams2($dal->getCodeFams2())
                ->setNumeroFournisseur($dal->getNumeroFournisseur())
                ->setNomFournisseur($dal->getNomFournisseur())
                ->setDateFinSouhaite($dal->getDateFinSouhaite())
                ->setCommentaire($dal->getCommentaire())
                ->setStatutDal($dal->getStatutDal())
                ->setCatalogue($dal->getCatalogue())
                ->setDemandeAppro($dal->getDemandeAppro())
                ->setDemandeApproLR($dal->getDemandeApproLR())
                ->setEstValidee($dal->getEstValidee())
                ->setEstModifier($dal->getEstModifier())
                ->setDateCreation($dal->getDateCreation())
                ->setDateModification($dal->getDateModification())
                ->setNumeroVersion($this->autoIncrement($dal->getNumeroVersion()))
            ;
            self::$em->persist($dalTemporaire);
        }
        self::$em->flush();
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }
}
