<?php

namespace App\Controller\da\Modification;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Controller\Traits\da\modification\DaEditAvecDitTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproFormType;
use App\Entity\da\DemandeApproLR;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaEditAvecDitController extends Controller
{
    use DaAfficherTrait;
    use DaEditAvecDitTrait;

    private const EDIT_DELETE = 2;
    private const EDIT_MODIF = 3;
    private const EDIT_LOADED_PAGE = 1;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaEditAvecDitTrait();
    }

    /**
     * @Route("/edit-avec-dit/{id}", name="da_edit_avec_dit")
     */
    public function edit(int $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** @var DemandeAppro $demandeAppro la demande appro correspondant à l'id $id */
        $demandeAppro = $this->demandeApproRepository->find($id); // recupération de la DA
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $demandeAppro->getNumeroDemandeDit()]); // recupération du DIT associée à la DA
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $demandeAppro = $this->filtreDal($demandeAppro, $dit, (int)$numeroVersionMax); // on filtre les lignes de la DA selon le numero de version max

        $ancienDals = $this->getAncienDAL($demandeAppro);

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();

        $this->traitementForm($form, $request, $ancienDals);

        $observations = $this->daObservationRepository->findBy(['numDa' => $demandeAppro->getNumeroDemandeAppro()], ['dateCreation' => 'DESC']);

        self::$twig->display('da/edit-avec-dit.html.twig', [
            'form'         => $form->createView(),
            'observations' => $observations,
            'peutModifier' => $this->PeutModifier($demandeAppro),
            'numDa'        => $numDa,
        ]);
    }

    /** 
     * @Route("/delete-line-avec-dit/{numDa}/{ligne}",name="da_delete_line_avec_dit")
     */
    public function deleteLineDa(string $numDa, string $ligne)
    {
        $this->verifierSessionUtilisateur();

        $demandeApproLs = self::$em->getRepository(DemandeApproL::class)->findBy([
            'numeroDemandeAppro' => $numDa,
            'numeroLigne'        => $ligne
        ]);

        if ($demandeApproLs) {
            $demandeApproLRs = self::$em->getRepository(DemandeApproLR::class)->findBy([
                'numeroDemandeAppro' => $numDa,
                'numeroLigne'        => $ligne
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
        return ($this->estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == DemandeAppro::STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == DemandeAppro::STATUT_VALIDE));
    }

    private function traitementForm($form, Request $request, iterable $ancienDals): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeAppro = $form->getData();
            $numDa = $demandeAppro->getNumeroDemandeAppro();

            $this->modificationDa($demandeAppro, $form->get('DAL'));
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            $this->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé

            /** ENVOIE MAIL */
            $this->envoyerMailModificationDaAvecDit($demandeAppro, [
                'ancienDals'    => $ancienDals,
                'nouveauDals'   => $demandeAppro->getDAL(),
                'service'       => 'atelier',
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms()
            ]);

            //notification
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre modification a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
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
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
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
                self::$em->remove($demandeApproL);
                $this->deleteDALR($demandeApproL);
            } else {
                self::$em->persist($demandeApproL); // on persiste la DAL
            }
        }
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $demandeAppro->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            $dalr->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO);
            self::$em->persist($dalr);
        }
    }

    /** 
     * Fonction pour obtenir les anciens DAL
     */
    private function getAncienDAL(DemandeAppro $demandeAppro): array
    {
        $result = [];
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $result[] = clone $demandeApproL;
        }
        return $result;
    }

    /**
     * Suppression physique des DALR correspondant au DAL $dal
     *
     * @param DemandeApproL $dal
     * @return void
     */
    private function deleteDALR(DemandeApproL $dal)
    {
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigne' => $dal->getNumeroLigne(), 'numeroDemandeAppro' => $dal->getNumeroDemandeAppro()]);
        foreach ($dalrs as $dalr) {
            self::$em->remove($dalr);
        }
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
