<?php

namespace App\Controller\da;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DemandeApproTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproFormType;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaEditController extends Controller
{
    use DemandeApproTrait;
    use lienGenerique;

    private const ID_ATELIER = 3;
    private const DA_STATUT = 'Demande d’achats';
    private const DA_STATUT_VALIDE = 'Bon d’achats validé';
    private const EDIT_DELETE = 2;
    private const EDIT_MODIF = 3;
    private const EDIT_LOADED_PAGE = 1;

    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;
    private DaObservationRepository $daObservationRepository;
    private DemandeApproLRepository $daLRepository;
    private DemandeApproLRRepository $daLRRepository;
    private DaObservation $daObservation;

    public function __construct()
    {
        parent::__construct();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
        $this->daLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->daLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->daObservation = new DaObservation();
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

        if (!$this->sessionService->has('firstCharge') && !$this->PeutModifier($demandeAppro)) {
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

        $demandeApproLs = self::$em->getRepository(DemandeApproL::class)->findBy(['numeroLigne' => $ligne, 'numeroVersion' => $numeroVersionMax]); // recupération de la ligne à supprimer

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
            return $item->getNumeroVersion() == $numeroVersionMax && $item->getDeleted() == 0;
        });
        $demandeAppro->setDAL($dernieresVersions); // on remplace la collection de versions par la collection filtrée

        return $demandeAppro;
    }

    private function PeutModifier($demandeAppro)
    {
        return ($this->estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == self::DA_STATUT || $demandeAppro->getStatutDal() == self::DA_STATUT_VALIDE));
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
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro);
            }


            /** ENVOIE MAIL */
            $this->mailPourAppro($demandeAppro, $demandeAppro->getObservation());

            //notification
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("da_list");
        }
    }

    private function mailPourAppro($demandeAppro, $observation): void
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($numDa);
        $numeroVersionMaxAvant = $numeroVersionMax - 1;
        $dalNouveau = $this->daLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        $dalAncien = $this->daLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMaxAvant]);
        /** NOTIFICATION MAIL */
        $this->envoyerMailAuxAppro([
            'id'            => $demandeAppro->getId(),
            'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
            'objet'         => $demandeAppro->getObjetDal(),
            'detail'        => $demandeAppro->getDetailDal(),
            'dalAncien'     => $dalAncien,
            'dalNouveau'    => $dalNouveau,
            'observation'   => $observation,
            'service'       => 'atelier',
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);
    }

    private function insertionObservation(DemandeAppro $demandeAppro): void
    {
        $daObservation = $this->recupDonnerDaObservation($demandeAppro);

        self::$em->persist($daObservation);

        self::$em->flush();
    }

    private function recupDonnerDaObservation(DemandeAppro $demandeAppro): DaObservation
    {
        return $this->daObservation
            ->setNumDa($demandeAppro->getNumeroDemandeAppro())
            ->setUtilisateur($demandeAppro->getDemandeur())
            ->setObservation($demandeAppro->getObservation())
        ;
    }

    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAppro(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => 'hoby.ralahy@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "modificationDa",
                'subject'    => "{$tab['numDa']} - modification demande d'approvisionnement ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list"),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
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
        foreach ($demandeApproLs as $demandeApproL) {
            /** 
             * @var DemandeApproL $demandeApproL
             */
            $demandeApproL
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setStatutDal(self::DA_STATUT)
                ->setEdit(self::EDIT_MODIF) // Indiquer que c'est une version modifiée
                ->setNumeroVersion($numeroVersionMax)
                ->setJoursDispo($this->getJoursRestants($demandeApproL))
            ; // Incrémenter le numéro de version
            $this->deleteDALR($demandeApproL);
            self::$em->persist($demandeApproL); // on persiste la DA
        }
    }

    /**
     * Suppression physique des DALR correspondant au DAL $dal
     *
     * @param DemandeApproL $dal
     * @return void
     */
    private function deleteDALR(DemandeApproL $dal)
    {
        if ($dal->getDeleted() === true) {
            $dalrs = $this->daLRRepository->findBy(['numeroLigneDem' => $dal->getNumeroLigne(), 'numeroDemandeAppro' => $dal->getNumeroDemandeAppro()]);
            foreach ($dalrs as $dalr) {
                self::$em->remove($dalr);
                self::$em->persist($dalr);
            }
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
