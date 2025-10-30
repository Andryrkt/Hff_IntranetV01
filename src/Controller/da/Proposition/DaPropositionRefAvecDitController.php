<?php

namespace App\Controller\da\Proposition;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\admin\Application;
use App\Entity\da\DemandeApproLR;
use App\Form\da\DaObservationType;
use App\Entity\da\DemandeApproLRCollection;
use App\Controller\Traits\AutorisationTrait;
use App\Form\da\DaPropositionValidationType;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Form\da\DemandeApproLRCollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Controller\Traits\da\validation\DaValidationAvecDitTrait;
use App\Controller\Traits\da\proposition\DaPropositionAvecDitTrait;

/**
 * @Route("/demande-appro")
 */
class DaPropositionRefAvecDitController extends Controller
{
    use DaAfficherTrait;
    use AutorisationTrait;
    use DaValidationAvecDitTrait;
    use DaPropositionAvecDitTrait;

    private const EDIT = 0;

    public function __construct()
    {
        parent::__construct();

        $this->initDaPropositionAvecDitTrait();
        $this->initDaValidationAvecDitTrait();
    }

    /**
     * @Route("/proposition-avec-dit/{id}", name="da_proposition_ref_avec_dit")
     */
    public function propositionDeReference($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        $da = $this->demandeApproRepository->findAvecDernieresDALetLR($id);
        $numDa = $da->getNumeroDemandeAppro();
        $dals = $da->getDAL();

        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);

        $DapLRCollection = new DemandeApproLRCollection();
        $daObservation = new DaObservation();
        $form = $this->getFormFactory()->createBuilder(DemandeApproLRCollectionType::class, $DapLRCollection)->getForm();
        $formObservation = $this->getFormFactory()->createBuilder(DaObservationType::class, $daObservation, ['daTypeId' => $da->getDaTypeId()])->getForm();
        $formValidation = $this->getFormFactory()->createBuilder(
            DaPropositionValidationType::class,
            [],
            [
                'action' => $this->getUrlGenerator()->generate('da_validate_avec_dit', ['numDa' => $numDa])
            ]
        )->getForm();

        //================== Traitement du formulaire en général ===========================//
        $this->traitementFormulaire($form, $formObservation, $dals, $request, $numDa, $da);
        // =================================================================================//

        $observations = $this->daObservationRepository->findBy(['numDa' => $numDa]);

        return $this->render("da/proposition.html.twig", [
            'demandeAppro'            => $da,
            'id'                      => $id,
            'dit'                     => $dit,
            'form'                    => $form->createView(),
            'formValidation'          => $formValidation->createView(),
            'formObservation'         => $formObservation->createView(),
            'observations'            => $observations,
            'numDa'                   => $numDa,
            'connectedUser'           => $this->getUser(),
            'statutAutoriserModifAte' => $da->getStatutDal() === DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
            'estAte'                  => $this->estUserDansServiceAtelier(),
            'estAppro'                => $this->estUserDansServiceAppro(),
            'nePeutPasModifier'       => $this->nePeutPasModifier($da),
            'propValTemplate'         => 'proposition-validation-avec-dit',
            'dossierJS'               => 'propositionAvecDit',
        ]);
    }

    private function nePeutPasModifier(DemandeAppro $demandeAppro)
    {
        return ($this->estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == DemandeAppro::STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == DemandeAppro::STATUT_VALIDE));
    }

    private function traitementFormulaire($form, $formObservation, $dals, Request $request, string $numDa, DemandeAppro $da)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            $observation = $form->getData()->getObservation();
            $statutChange = $form->get('statutChange')->getData();

            if ($request->request->has('brouillon')) {
                /** Enregistrer provisoirement */
                $this->traitementPourBtnBrouillon($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('enregistrer')) {
                /** Envoyer proposition à l'atelier */
                $this->traitementPourBtnEnregistrer($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('changement')) {
                /** Valider les articles par l'atelier */
                $this->traitementPourBtnValiderAtelier($request, $dals, $numDa, $dalrList, $observation, $da);
            } elseif ($request->request->has('observation')) {
                /** Envoyer observation */
                $this->traitementPourBtnEnvoyerObservation($observation, $da, $statutChange);
            } elseif ($request->request->has('valider')) {
                /** Valider les articles par l'appro */
                $this->traitementPourBtnValiderAppro($request, $dals, $numDa, $dalrList, $observation, $da);
            }
        }

        $formObservation->handleRequest($request);

        if ($formObservation->isSubmitted() && $formObservation->isValid()) {
            /** @var DaObservation $daObservation daObservation correspondant au donnée du formObservation */
            $daObservation = $formObservation->getData();

            $this->traitementEnvoiObservation($daObservation, $da);
        }
    }

    private function traitementEnvoiObservation(DaObservation $daObservation, DemandeAppro $demandeAppro)
    {
        $this->insertionObservation($daObservation->getObservation(), $demandeAppro);

        if ($this->estUserDansServiceAppro() && $daObservation->getStatutChange()) {
            $this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
            $this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);

            $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro()); // ajout dans la table DaAfficher si le statut a changé
        }

        $notification = [
            'type' => 'success',
            'message' => 'Votre observation a été enregistré avec succès.',
        ];

        /** ENVOIE D'EMAIL à l'APPRO pour l'observation */
        $service = $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : '');
        $this->emailDaService->envoyerMailObservationDaAvecDit($demandeAppro, [
            'service'       => $service,
            'observation'   => $daObservation->getObservation(),
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        return $this->redirectToRoute("list_da");
    }

    /** 
     * Traitement pour le cas où c'est envoi d'observation
     */
    private function traitementPourBtnEnvoyerObservation($observation, DemandeAppro $demandeAppro, $statutChange)
    {
        if ($observation !== null) {
            $this->insertionObservation($observation, $demandeAppro);
            if ($statutChange) {
                $this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_SOUMIS_APPRO);
                $this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_SOUMIS_APPRO);

                $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro()); // ajout dans la table DaAfficher si le statut a changé
            }
            $notification = [
                'type' => 'success',
                'message' => 'Votre observation a été enregistré avec succès.',
            ];

            /** ENVOIE D'EMAIL à l'APPRO pour l'observation */
            $service = $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : '');
            $this->emailDaService->envoyerMailObservationDaAvecDit($demandeAppro, [
                'service'       => $service,
                'observation'   => $observation,
                'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
            ]);
        } else {
            $notification = [
                'type' => 'danger',
                'message' => 'Echec: Pas d\'observation.',
            ];
        }

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("list_da");
    }

    /** 
     * Traitement pour le cas où c'est l'appro qui propose et valide la demande
     */
    private function traitementPourBtnValiderAppro(Request $request, $dals, $numDa, $dalrList, $observation, DemandeAppro $da)
    {
        /** RECUPERATION DE NUMERO DE page et NUMERO de ligne de tableau */
        $refs = $this->recuperationDesRef($request);

        $notification = $this->traiterProposition(
            $dals,
            $dalrList,
            $observation,
            $da,
            $refs,
            "Les articles ont été validés avec succès",
            true,
            DemandeAppro::STATUT_VALIDE
        );

        $this->modificationChoixEtligneDal($refs, $dals);
        $nomEtChemin = $this->validerProposition($numDa);

        $this->ajouterDansTableAffichageParNumDa($numDa, true); // enregistrement dans la table DaAfficher

        /** ENVOI DE MAIL POUR LA VALIDATION DES ARTICLES */
        $this->emailDaService->envoyerMailValidationDaAvecDit($da, $nomEtChemin, [
            'service'           => $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : ''),
            'phraseValidation'  => 'Vous trouverez en pièce jointe le fichier contenant les références ZST.',
            'userConnecter'     => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("list_da");
    }

    /** 
     * Traitement pour le cas où c'est l'atelier qui a validé la demande
     */
    private function traitementPourBtnValiderAtelier(Request $request, $dals, $numDa, $dalrList, $observation, DemandeAppro $da)
    {
        /** MODIFICATION de choix de reference */
        $notification = $this->modificationChoixDeRef(
            $dals,
            $dalrList,
            $observation,
            $da,
            $request
        );

        /** VALIDATION DU PROPOSITION PAR L'ATE */
        $nomEtChemin = $this->validerProposition($numDa);

        $this->ajouterDansTableAffichageParNumDa($numDa, true);

        /** ENVOI DE MAIL POUR LES ARTICLES VALIDES */
        $this->emailDaService->envoyerMailValidationDaAvecDit($da, $nomEtChemin, [
            'service'           => $this->estUserDansServiceAtelier() ? 'atelier' : ($this->estUserDansServiceAppro() ? 'appro' : ''),
            'phraseValidation'  => 'Vous trouverez en pièce jointe le fichier contenant les références ZST.',
            'userConnecter'     => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("list_da");
    }

    private function modificationChoixDeRef(
        $dals,
        $dalrList,
        ?string $observation,
        DemandeAppro $da,
        Request $request
    ): array {
        $refs = $this->recuperationDesRef($request);

        $notification = $this->traiterProposition(
            $dals,
            $dalrList,
            $observation,
            $da,
            $refs,
            "Le choix de la proposition a été changé avec succès",
            false
        );

        $this->modificationChoixEtligneDal($refs, $dals);

        return $notification;
    }

    private function validerProposition(string $numDa)
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        $da = $this->validerDemandeApproAvecLignes($numDa, $numeroVersionMax);

        /** CREATION EXCEL */
        $nomEtChemin = $this->exporterDaAvecDitEnExcelEtPdf($numDa, $numeroVersionMax);

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($nomEtChemin['fileName']);
        $this->getEntityManager()->flush();

        return $nomEtChemin;
    }

    private function traitementPourBtnEnregistrer($dalrList, Request $request, $dals, ?string $observation, string $numDa, DemandeAppro $da): void
    {
        /** RECUPERATION DE NUMERO DE page et NUMERO de ligne de tableau */
        $refs = $this->recuperationDesRef($request);

        $notification = $this->traiterProposition(
            $dals,
            $dalrList,
            $observation,
            $da,
            $refs,
            "La proposition a été soumis à l'atelier",
            true
        );

        $this->modificationChoixEtligneDal($refs, $dals);

        $this->ajouterDansTableAffichageParNumDa($numDa);

        $this->emailDaService->envoyerMailPropositionDa($this->demandeApproRepository->findAvecDernieresDALetLR($da->getId()), $this->getUser());

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("list_da");
    }

    private function traitementPourBtnBrouillon($dalrList, Request $request, $dals, ?string $observation, string $numDa, DemandeAppro $da): void
    {
        /** RECUPERATION DE NUMERO DE page et NUMERO de ligne de tableau */
        $refs = $this->recuperationDesRef($request);

        $notification = $this->traiterProposition(
            $dals,
            $dalrList,
            $observation,
            $da,
            $refs,
            "La proposition a été enregistré avec succès",
            true,
            DemandeAppro::STATUT_EN_COURS_PROPOSITION
        );

        $this->modificationChoixEtligneDal($refs, $dals);

        $this->ajouterDansTableAffichageParNumDa($numDa);

        $this->getSessionService()->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("list_da");
    }

    private function getNouveauDal($numDa)
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $dalNouveau = $this->getLignesRectifieesDA($numDa, $numeroVersionMax);
        return $dalNouveau;
    }

    private function nouveauEtAncienDal(DemandeAppro $da, string $numDa): array
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa); //la position de cette ligne ne peut pas modifier (il faut mettre en haut ou en bas)
        $numeroVersionMaxAvant = $numeroVersionMax - 1;
        $dalNouveau = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $da->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
        $dalAncien = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $da->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMaxAvant]);

        return [
            'dalAncien' => $dalAncien,
            'dalNouveau' => $dalNouveau
        ];
    }

    private function traiterProposition($dals, $dalrList, ?string $observation, DemandeAppro $demandeAppro, array $refs, string $messageSuccess, bool $doSaveDb = false, $statut = DemandeAppro::STATUT_SOUMIS_ATE): array
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();

        if ($dalrList->isEmpty() && empty($refs)) {
            return $this->notification('danger', "Aucune modification n'a été effectuée");
        }

        if ($doSaveDb) {
            $this->enregistrementDb($dals, $dalrList, $statut);
        }

        if ($observation !== null) {
            $this->insertionObservation($observation, $demandeAppro);
        }

        $this->modificationStatutDal($numDa, $statut);
        $this->modificationStatutDa($numDa, $statut);

        return $this->notification('success', $messageSuccess);
    }

    private function recuperationDesRef(Request $request): array
    {
        $refsString = $request->request->get('refs');
        $selectedRefs = $refsString ? explode(',', $refsString) : [];
        $refs = $this->separationNbrPageLigne($selectedRefs);
        return $refs;
    }

    /** 
     * Modifie l'ancien choix de DAL en ligne de DALR sélectionnée
     */
    private function modificationChoixEtligneDal($refs, $dals)
    {
        if (!empty($refs)) {
            // reset les ligne de la page courante
            $this->resetChoix($refs, $dals);

            //modifier la colonne choix
            $this->modifChoix($refs, $dals);

            //modification de la table demande_appro_L pour les lignes refs
            $this->modificationTableDaL($refs, $dals);
        }
    }

    /**
     * Permet de modifier l'id de la relation demande_appro_L dans la table demande_appro_LR
     *
     * @param string $numDa
     * @param integer $numeroVersionMax
     * @return void
     */
    public function modificationIdDALDansDALR(string $numDa, int $numeroVersionMax): void
    {
        //recupération des dal du dernière version du numeroDa dans la table demande_appro_L
        $dalsTrie = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);
        //modification du colonne $demandeApproL dans le table demande_appro_LR
        foreach ($dalsTrie as $dal) {
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroLigne' => $dal->getNumeroLigne()]);
            if (!empty($dalrs)) {
                foreach ($dalrs as $dalr) {
                    $dalr->setDemandeApproL($dal);
                    $this->getEntityManager()->persist($dalr);
                }
            }
        }
        $this->getEntityManager()->flush();
    }

    private function modificationStatutDal(string $numDa, string $statut): void
    {
        $numeroVersionMax = $this->getEntityManager()->getRepository(DemandeApproL::class)->getNumeroVersionMax($numDa);
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($dals as  $dal) {
            $dal->setStatutDal($statut);
            $dal->setEdit(self::EDIT);
            $this->getEntityManager()->persist($dal);
        }

        $this->getEntityManager()->flush();
    }

    private function modificationStatutDa(string $numDa, string $statut): void
    {
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $da->setStatutDal($statut);

        $this->getEntityManager()->persist($da);
        $this->getEntityManager()->flush();
    }

    private function modificationTableDaL(array $refs,  $data): void
    {
        $dals = $this->recupDataDaL($refs,  $data);
        $dalrs = $this->recupDataDaLR($refs,  $data);

        for ($i = 0; $i < count($dals); $i++) {
            $dals[$i][0]
                ->setEstValidee($dalrs[$i][0]->getEstValidee())
                ->setEstModifier($dalrs[$i][0]->getChoix())
            ;
            $this->getEntityManager()->persist($dals[$i][0]);
        }

        $this->getEntityManager()->flush();
    }


    /**
     * recupération d'uen ou des lignes à modifier
     *
     * @param array $refs
     * @param [type] $data
     * @return array
     */
    private function recupDataDaL(array $refs, $data): array
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($data[0]->getNumeroDemandeAppro());
        $dals = [];
        for ($i = 0; $i < count($refs); $i++) {
            $dals[] = $this->demandeApproLRepository->findBy(['numeroLigne' => $refs[$i][0], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);
        }

        return $dals;
    }

    private function recupDataDaLR(array $refs,  $data): array
    {
        $dalrs = [];
        for ($i = 0; $i < count($refs); $i++) {
            $dalrs[] = $this->demandeApproLRRepository->findBy(['numeroLigne' => $refs[$i][0], 'numLigneTableau' => $refs[$i][1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()], ['numeroLigne' => 'ASC']);
        }

        return $dalrs;
    }

    private function resetChoix(array $refs, $data): void
    {
        $dalrsAll = $this->recupEntitePageCourante($refs, $data);
        $dalrsAll = $this->resetEntite($dalrsAll);
        $this->resetBd($dalrsAll);
    }

    private function modifChoix(array $refs, $data): void
    {
        $dalrs = $this->recupEntiteAModifier($refs, $data);
        $dalrs = $this->modifEntite($dalrs);
        $this->modificationBd($dalrs);
    }

    private function separationNbrPageLigne(array $selectedRefs): array
    {
        $refs = [];
        foreach ($selectedRefs as  $value) {
            $refs[] = explode('-', $value);
        }
        return $refs;
    }

    private function recupEntitePageCourante(array $refs, $data): array
    {
        foreach ($refs as $ref) {
            $dalrsAllTab = $this->demandeApproLRRepository->findBy(['numeroLigne' => $ref[0], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()]);
            foreach ($dalrsAllTab as $dalr) {
                $dalrsAll[] = $dalr;
            }
        }

        return $dalrsAll;
    }

    private function resetEntite(array $dalrsAll): array
    {
        foreach ($dalrsAll as  $dalr) {
            $dalr->setChoix(false);
        }
        return $dalrsAll;
    }

    private function resetBd(array $dalrsAll): void
    {
        foreach ($dalrsAll as $dalr) {
            $this->getEntityManager()->persist($dalr);
        }
        $this->getEntityManager()->flush();
    }

    private function recupEntiteAModifier(array $refs, $data): array
    {
        foreach ($refs as $ref) {
            $dalrsTab = $this->demandeApproLRRepository->findBy(['numeroLigne' => $ref[0], 'numLigneTableau' => $ref[1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()]);
            foreach ($dalrsTab as $dalr) {
                $dalrs[] = $dalr;
            }
        }

        return $dalrs;
    }

    private function modifEntite(array $dalrs): array
    {
        foreach ($dalrs as  $dalr) {
            $dalr->setChoix(true);
        }
        return $dalrs;
    }

    private function modificationBd(array $dalrs): void
    {
        foreach ($dalrs as $dalr) {
            $this->getEntityManager()->persist($dalr);
        }
        $this->getEntityManager()->flush();
    }

    private function notification(string $type, string $message): array
    {
        return [
            'type'    => $type,
            'message' => $message,
        ];
    }

    private function enregistrementDb($dals, $dalrList, $statut)
    {
        foreach ($dalrList as $demandeApproLR) {
            $DAL = $this->filtreDal($dals, $demandeApproLR);
            $demandeApproLR = $this->ajoutDonnerDaLR($DAL, $demandeApproLR, $statut);
            $this->getEntityManager()->persist($demandeApproLR);
        }
        $this->getEntityManager()->flush();
    }

    private function filtreDal($dals, $demandeApproLR): ?object
    {
        return  $dals->filter(function ($dal) use ($demandeApproLR) {
            return $dal->getNumeroLigne() === $demandeApproLR->getNumeroLigne();
        })->first();
    }

    private function ajoutDonnerDaLR(DemandeApproL $DAL, DemandeApproLR $demandeApproLR, $statut): DemandeApproLR
    {
        $demandeApproLR_Ancien = $this->demandeApproLRRepository->getDalrByPageAndRow($DAL->getNumeroDemandeAppro(), $demandeApproLR->getNumeroLigne(), $demandeApproLR->getNumLigneTableau());

        $file = $demandeApproLR->getNomFicheTechnique(); // fiche technique de la DALR
        $fileNames = $demandeApproLR->getFileNames(); // pièces jointes de la DALR

        if ($demandeApproLR_Ancien) {
            $this->daFileUploader->uploadFTForDalr($file, $demandeApproLR_Ancien);
            $this->traitementFichiers($demandeApproLR_Ancien, $fileNames);

            $DAL->getDemandeApproLR()->add($demandeApproLR_Ancien);

            return $demandeApproLR_Ancien;
        } else {
            $libelleFamille = $this->daModel->getLibelleFamille($demandeApproLR->getArtFams1()); // changement de code famille en libelle famille
            $libelleSousFamille = $this->daModel->getLibelleSousFamille($demandeApproLR->getArtFams2(), $demandeApproLR->getArtFams1()); // changement de code sous famille en libelle sous famille

            $demandeApproLR
                ->setDemandeApproL($DAL)
                ->setNumeroDemandeAppro($DAL->getNumeroDemandeAppro())
                ->setQteDem($DAL->getQteDem())
                ->setArtConstp($DAL->getArtConstp())
                ->setCodeFams1($demandeApproLR->getArtFams1() == '' ? NULL : $demandeApproLR->getArtFams1()) // ceci doit toujour avant le setArtFams1
                ->setCodeFams2($demandeApproLR->getArtFams2() == '' ? NULL : $demandeApproLR->getArtFams2()) // ceci doit toujour avant le setArtFams2
                ->setArtFams1($libelleFamille == '' ? NULL : $libelleFamille) // ceci doit toujour après le codeFams1
                ->setArtFams2($libelleSousFamille == '' ? NULL : $libelleSousFamille) // ceci doit toujour après le codeFams2
                ->setDateFinSouhaite($DAL->getDateFinSouhaite())
                ->setStatutDal($statut)
                ->setNumeroDemandeDit($DAL->getNumeroDit())
                ->setJoursDispo($DAL->getJoursDispo())
            ;
            if ($demandeApproLR->getNumeroFournisseur() == 0) {
                $demandeApproLR->setNumeroFournisseur($this->fournisseurs[$demandeApproLR->getNomFournisseur()] ?? 0); // définir le numéro du fournisseur
            }

            if ($file) {
                $this->daFileUploader->uploadFTForDalr($file, $demandeApproLR);
            }

            if ($fileNames) {
                $this->traitementFichiers($demandeApproLR, $fileNames);
            }

            $DAL->getDemandeApproLR()->add($demandeApproLR);

            return $demandeApproLR;
        }
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD pour chaque ligne de remplacement la demande appro (DALR)
     */
    private function traitementFichiers(DemandeApproLR $dalr, $files): void
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->daFileUploader->uploadPJForDalr($file, $dalr, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        $dalr->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
    }
}
