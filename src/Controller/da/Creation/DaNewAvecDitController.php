<?php

namespace App\Controller\da\Creation;

use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Form\da\DemandeApproFormType;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Controller\Traits\da\creation\DaNewAvecDitTrait;

/**
 * @Route("/demande-appro")
 */
class DaNewAvecDitController extends Controller
{
    use DaNewAvecDitTrait;
    use AutorisationTrait;
    const STATUT_DAL = [
        'enregistrerBrouillon' => DemandeAppro::STATUT_EN_COURS_CREATION,
        'soumissionAppro'      => DemandeAppro::STATUT_SOUMIS_APPRO,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->initDaNewAvecDitTrait();
    }

    /**
     * @Route("/da-first-form", name="da_first_form")
     */
    public function firstForm()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        return $this->render('da/first-form.html.twig', [
            'estAte'                 => $this->estUserDansServiceAtelier(),
            'estCreateurDeDADirecte' => $this->estCreateurDeDADirecte(),
            'urls'                   => [
                'avecDit' => $this->getUrlGenerator()->generate('da_list_dit'),
                'direct'  => $this->getUrlGenerator()->generate('da_new_direct', ['id' => 0]),
                'reappro' => $this->getUrlGenerator()->generate('da_new_reappro', ['id' => 0]),
            ],
            'estAdmin'               => $this->estAdmin(),
        ]);
    }

    /**
     * @Route("/new-avec-dit/{daId<\d+>}/{ditId}", name="da_new_avec_dit")
     */
    public function new(int $daId, int $ditId, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP, Service::ID_ATELIER);
        /** FIN AUtorisation accès */

        /** 
         * @var DemandeIntervention $dit DIT correspondant à l'id $ditId
         */
        $dit = $this->ditRepository->find($ditId);

        $demandeAppro = $daId === 0 ? $this->initialisationDemandeApproAvecDit($dit) : $this->demandeApproRepository->findAvecDernieresDALetLR($daId);
        $demandeAppro
            ->setDit($dit)
            ->setDateFinSouhaite($this->dateLivraisonPrevueDA($dit->getNumeroDemandeIntervention(), $dit->getIdNiveauUrgence()->getDescription()))
        ;

        $form = $this->getFormFactory()->createBuilder(DemandeApproFormType::class, $demandeAppro)->getForm();
        $this->traitementForm($form, $request, $demandeAppro, $dit);

        return $this->render('da/new-avec-dit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementForm($form, Request $request, DemandeAppro $demandeAppro, DemandeIntervention $dit): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $form->getData();

            $numDa = $demandeAppro->getNumeroDemandeAppro();
            $formDAL = $form->get('DAL');

            // Récupérer le nom du bouton cliqué
            $clickedButtonName = $this->getButtonName($request);
            $demandeAppro->setStatutDal(self::STATUT_DAL[$clickedButtonName]);

            foreach ($formDAL as $subFormDAL) {
                /** 
                 * @var DemandeApproL $demandeApproL
                 * On récupère les données du formulaire DAL
                 */
                $demandeApproL = $subFormDAL->getData();
                $files = $subFormDAL->get('fileNames')->getData(); // Récupération des fichiers

                if ($demandeApproL->getDeleted() == 1) {
                    $this->getEntityManager()->remove($demandeApproL);
                } else {
                    /** 
                     * @var DemandeApproL $demandeApproL
                     */
                    $demandeApproL
                        ->setNumeroDemandeAppro($numDa)
                        ->setStatutDal(self::STATUT_DAL[$clickedButtonName])
                        ->setPrixUnitaire($this->daModel->getPrixUnitaire($demandeApproL->getArtRefp())[0])
                        ->setNumeroDit($demandeAppro->getNumeroDemandeDit())
                        ->setJoursDispo($this->getJoursRestants($demandeApproL))
                    ;

                    if ($demandeApproL->getNumeroFournisseur() == 0) {
                        $demandeApproL->setNumeroFournisseur($this->fournisseurs[$demandeApproL->getNomFournisseur()] ?? 0); // définir le numéro du fournisseur
                    }
                    $this->traitementFichiers($demandeApproL, $files); // traitement des fichiers uploadés pour chaque ligne DAL
                    $this->getEntityManager()->persist($demandeApproL);
                }
            }

            /** Modifie la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->getEntityManager());
            $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeAppro);
            $this->getEntityManager()->flush();

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation()) $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro, $dit);

            if ($clickedButtonName === "soumissionAppro") $this->emailDaService->envoyerMailCreationDa($demandeAppro, $this->getUser());

            $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
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
