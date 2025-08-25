<?php

namespace App\Controller\da\Creation;

use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Form\da\DemandeApproFormType;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\ApplicationTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Controller\Traits\da\creation\DaNewAvecDitTrait;

/**
 * @Route("/demande-appro")
 */
class DaNewAvecDitController extends Controller
{
    use ApplicationTrait;
    use DaNewAvecDitTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaNewAvecDitTrait();
    }

    /**
     * @Route("/first-form", name="da_first_form")
     */
    public function firstForm()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        self::$twig->display('da/first-form.html.twig', [
            'estAte' => $this->estUserDansServiceAtelier(),
            'estAdmin' => $this->estAdmin()
        ]);
    }

    /**
     * @Route("/new-avec-dit/{id}", name="da_new_avec_dit")
     */
    public function new($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP, Service::ID_ATELIER);
        /** FIN AUtorisation acées */

        /** 
         * @var DemandeIntervention $dit DIT correspondant à l'id $id
         */
        $dit = $this->ditRepository->find($id);

        $demandeAppro = $this->initialisationDemandeApproAvecDit($dit);

        $form = self::$validator->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();
        $this->traitementForm($form, $request, $demandeAppro);

        self::$twig->display('da/new-avec-dit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $form->getData();
            /** @var DemandeIntervention $dit */
            $dit = $demandeAppro->getDit();

            $numDa = $demandeAppro->getNumeroDemandeAppro();
            $numDit = $demandeAppro->getNumeroDemandeDit();

            $formDAL = $form->get('DAL');
            /** ajout de ligne de demande appro dans la table Demande_Appro_L */
            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                /** 
                 * @var DemandeApproL $DAL
                 */
                $DAL
                    ->setNumeroDemandeAppro($numDa)
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal(DemandeAppro::STATUT_SOUMIS_APPRO)
                    ->setPrixUnitaire($this->daModel->getPrixUnitaire($DAL->getArtRefp())[0])
                    ->setNumeroDit($numDit)
                    ->setJoursDispo($this->getJoursRestants($DAL))
                ;
                $this->traitementFichiers($DAL, $formDAL[$ligne + 1]->get('fileNames')->getData()); // traitement des fichiers uploadés pour chaque ligne DAL
                if (null === $DAL->getNumeroFournisseur()) {
                    $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Erreur : Le nom du fournisseur doit correspondre à l’un des choix proposés.']);
                    $this->redirectToRoute("list_da");
                }
                self::$em->persist($DAL);
            }

            /** Modifie la colonne dernière_id dans la table applications */
            $this->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            self::$em->persist($demandeAppro);

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            self::$em->flush();

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro, $dit);

            $this->emailDaService->envoyerMailcreationDaAvecDit($demandeAppro, [
                'service'       => 'atelier',
                'observation'   => $demandeAppro->getObservation() ?? '-',
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
            ]);

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD pour chaque ligne de la demande appro (DAL)
     */
    private function traitementFichiers(DemandeApproL $dal, $files): void
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->daFileUploader->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
    }
}
