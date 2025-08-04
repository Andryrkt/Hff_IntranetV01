<?php

namespace App\Controller\da;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaEditController extends Controller
{
    use DaTrait;
    use DemandeApproTrait;
    use lienGenerique;

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
        /** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
        $demandeAppro = $this->daRepository->find($id); // recupération de la DA
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $demandeAppro->getNumeroDemandeDit()]); // recupération du DIT associée à la DA
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
     * @Route("/delete-line-da/{id}",name="da_delete_line_da")
     */
    public function deleteLineDa(int $id)
    {
        $this->verifierSessionUtilisateur();

        /** @var DemandeApproL $demandeApproLVersionMax la ligne de demande appro correspondant à l'id $id */
        $demandeApproLVersionMax = self::$em->getRepository(DemandeApproL::class)->find($id);

        if ($demandeApproLVersionMax) {
            $demandeApproLs = self::$em->getRepository(DemandeApproL::class)->findBy([
                'numeroDemandeAppro' => $demandeApproLVersionMax->getNumeroDemandeAppro(),
                'numeroLigne' => $demandeApproLVersionMax->getNumeroLigne()
            ]);

            $demandeApproLRs = self::$em->getRepository(DemandeApproLR::class)->findBy([
                'numeroDemandeAppro' => $demandeApproLVersionMax->getNumeroDemandeAppro(),
                'numeroLigne' => $demandeApproLVersionMax->getNumeroLigne()
            ]);

            foreach ($demandeApproLs as $demandeApproL) {
                self::$em->remove($demandeApproL);
            }

            foreach ($demandeApproLRs as $demandeApproLR) {
                self::$em->remove($demandeApproLR);
            }

            self::$em->flush();

            $notifType = "success";
            $notifMessage = "Réussite de l'opération: la ligne de DA a été supprimée avec succès.";
        } else {
            $notifType = "danger";
            $notifMessage = "Echec de la suppression de la ligne: la ligne de DA n'existe pas.";
        }
        $this->sessionService->set('notification', ['type' => $notifType, 'message' => $notifMessage]);
        $this->redirectToRoute("list_da");
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
        return (Controller::estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == DemandeAppro::STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == DemandeAppro::STATUT_VALIDE));
    }

    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $demandeAppro = $form->getData();

            $this->modificationDa($demandeAppro, $form->get('DAL'));
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro);
            }

            /** ENVOIE MAIL */
            $this->mailPourAppro($demandeAppro, $demandeAppro->getObservation());

            //notification
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("list_da");
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
            'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
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
            'to'        => DemandeAppro::MAIL_APPRO,
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "modificationDa",
                'subject'    => "{$tab['numDa']} - modification demande d'approvisionnement ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }


    private function modificationDa(DemandeAppro $demandeAppro, $formDAL): void
    {
        $demandeAppro->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO);
        self::$em->persist($demandeAppro); // on persiste la DA
        $this->modificationDAL($demandeAppro, $formDAL);
        self::$em->flush(); // on enregistre les modifications
    }

    private function modificationDAL($demandeAppro, $formDAL): void
    {
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        foreach ($formDAL as $subFormDAL) {
            /** 
             * @var DemandeApproL $demandeApproL
             * 
             * On récupère les données du formulaire DAL
             */
            $demandeApproL = $subFormDAL->getData();
            $files = $subFormDAL->get('fileNames')->getData(); // Récupération des fichiers

            $demandeApproL
                ->setNumeroDemandeAppro($demandeAppro->getNumeroDemandeAppro())
                ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
                ->setEdit(self::EDIT_MODIF) // Indiquer que c'est une version modifiée
                ->setNumeroVersion($numeroVersionMax)
                ->setJoursDispo($this->getJoursRestants($demandeApproL))
            ; // Incrémenter le numéro de version
            $this->traitementFichiers($demandeApproL, $files); // Traitement des fichiers uploadés

            if ($demandeApproL->getDeleted() == 1) {
                $this->deleteDALR($demandeApproL);
            }
            self::$em->persist($demandeApproL); // on persiste la DAL
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
        $dalrs = $this->daLRRepository->findBy(['numeroLigne' => $dal->getNumeroLigne(), 'numeroDemandeAppro' => $dal->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            self::$em->remove($dalr);
        }
    }

    private function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return (int)$num + 1;
    }

    /** 
     * Traitement des fichiers
     */
    private function traitementFichiers(DemandeApproL $dal, $files)
    {
        if ($files !== []) {
            $fileNames = [];
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
            $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
        }
    }
}
