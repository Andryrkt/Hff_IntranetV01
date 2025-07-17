<?php

namespace App\Controller\da;

use DateTime;
use App\Model\da\DaModel;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeApproLRCollection;
use App\Form\da\DaObservationType;
use App\Form\da\DaPropositionValidationType;
use App\Form\da\DemandeApproLRCollectionType;
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
class DaPropositionRefController extends Controller
{
    use DaTrait;
    use lienGenerique;

    private const DA_STATUT_CHANGE_CHOIX_ATE = 'changement de choix par l\'ATE';
    private const EDIT = 0;

    private DaModel $daModel;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DaObservation $daObservation;
    private DaObservationRepository $daObservationRepository;
    private DitRepository $ditRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daModel = new DaModel();
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daObservation = new DaObservation();
        $this->daObservationRepository = self::$em->getRepository(DaObservation::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
    }

    /**
     * @Route("/proposition/{id}", name="da_proposition")
     */
    public function propositionDeReference($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $da = $this->demandeApproRepository->findAvecDernieresDALetLR($id);
        $numDa = $da->getNumeroDemandeAppro();
        $dals = $da->getDAL();

        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);

        $DapLRCollection = new DemandeApproLRCollection();
        $daObservation = new DaObservation();
        $form = self::$validator->createBuilder(DemandeApproLRCollectionType::class, $DapLRCollection)->getForm();
        $formObservation = self::$validator->createBuilder(DaObservationType::class, $daObservation)->getForm();
        $formValidation = self::$validator->createBuilder(DaPropositionValidationType::class, [], ['action' => self::$generator->generate('da_validate', ['numDa' => $numDa])])->getForm();

        // Traitement du formulaire en géneral ===========================//
        $this->traitementFormulaire($form, $formObservation, $dals, $request, $numDa, $da); //
        // ===============================================================//

        $observations = $this->daObservationRepository->findBy(['numDa' => $numDa]);

        self::$twig->display('da/proposition.html.twig', [
            'da'                => $da,
            'id'                => $id,
            'dit'               => $dit,
            'form'              => $form->createView(),
            'formValidation'    => $formValidation->createView(),
            'formObservation'   => $formObservation->createView(),
            'observations'      => $observations,
            'numDa'             => $numDa,
            'connectedUser'     => Controller::getUser(),
            'statutAutoriserModifAte' => $da->getStatutDal() === DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
            'estAte'            => Controller::estUserDansServiceAtelier(),
            'estAppro'          => Controller::estUserDansServiceAppro(),
            'nePeutPasModifier' => $this->nePeutPasModifier($da)
        ]);
    }

    private function nePeutPasModifier($demandeAppro)
    {
        return (Controller::estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == DemandeAppro::STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == DemandeAppro::STATUT_VALIDE));
    }

    private function traitementFormulaire($form, $formObservation, $dals, Request $request, string $numDa, DemandeAppro $da)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            $observation = $form->getData()->getObservation();
            $statutChange = $form->get('statutChange')->getData();

            if ($request->request->has('enregistrer')) {
                /** Envoyer proposition à l'atelier */
                $this->traitementPourBtnEnregistrer($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('changement')) {
                /** Valider les articles */
                $this->traitementPourBtnValider($request, $dals, $numDa, $dalrList, $observation, $da);
            } elseif ($request->request->has('observation')) {
                /** Envoyer observation */
                $this->traitementPourBtnEnvoyerObservation($observation, $da, $statutChange);
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
        $this->insertionObservationSeul($daObservation, $demandeAppro);

        if (Controller::estUserDansServiceAppro() && $daObservation->getStatutChange()) {
            $this->duplicationDataDaL($demandeAppro->getNumeroDemandeAppro());
            $this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
            $this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_AUTORISER_MODIF_ATE);
        }

        $notification = [
            'type' => 'success',
            'message' => 'Votre observation a été enregistré avec succès.',
        ];

        /** ENVOIE D'EMAIL à l'APPRO pour l'observation */
        $service = Controller::estUserDansServiceAtelier() ? 'atelier' : (Controller::estUserDansServiceAppro() ? 'appro' : '');
        $this->envoyerMailObservation([
            'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
            'mailDemandeur' => $demandeAppro->getUser()->getMail(),
            'observation'   => $daObservation->getObservation(),
            'service'       => $service,
            'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        return $this->redirectToRoute("da_list");
    }

    /** 
     * Traitement pour le cas où c'est envoi d'observation
     */
    private function traitementPourBtnEnvoyerObservation($observation, DemandeAppro $demandeAppro, $statutChange)
    {
        if ($observation !== null) {
            $this->insertionObservation($observation, $demandeAppro->getNumeroDemandeAppro());
            if ($statutChange) {
                $this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_SOUMIS_APPRO);
                $this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_SOUMIS_APPRO);
            }
            $notification = [
                'type' => 'success',
                'message' => 'Votre observation a été enregistré avec succès.',
            ];

            /** ENVOIE D'EMAIL à l'APPRO pour l'observation */
            $service = Controller::estUserDansServiceAtelier() ? 'atelier' : (Controller::estUserDansServiceAppro() ? 'appro' : '');
            $this->envoyerMailObservation([
                'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
                'mailDemandeur' => $demandeAppro->getUser()->getMail(),
                'observation'   => $observation,
                'service'       => $service,
                'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
            ]);
        } else {
            $notification = [
                'type' => 'danger',
                'message' => 'Echec: Pas d\'observation.',
            ];
        }

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
    }

    /** 
     * Traitement pour le cas où c'est l'atelier qui a validé la demande
     */
    private function traitementPourBtnValider(Request $request, $dals, $numDa, $dalrList, $observation, $da)
    {
        /** MODIFICATION de choix de reference */
        $notification = $this->modificationChoixDeRef(
            $dals,
            $dalrList,
            $observation,
            $numDa,
            $request
        );

        /** VALIDATION DU PROPOSITION PAR L'ATE */
        $nomEtChemin = $this->validerProposition($numDa);

        /** ENVOIE D'EMAIL pour le changement des références et la validation des propositions */
        $this->envoyerMailValidationAuxAppro([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            'dalNouveau'    => $this->getNouveauDal($numDa),
            'service'       => 'atelier',
            'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->envoyerMailValidationAuxAte([
            'id'                => $da->getId(),
            'numDa'             => $da->getNumeroDemandeAppro(),
            'mailDemandeur'     => $da->getUser()->getMail(),
            'objet'             => $da->getObjetDal(),
            'detail'            => $da->getDetailDal(),
            'fileName'          => $nomEtChemin['fileName'],
            'filePath'          => $nomEtChemin['filePath'],
            'dalNouveau'        => $this->getNouveauDal($numDa),
            'service'           => 'atelier',
            'phraseValidation'  => 'Vous trouverez en pièce jointe le fichier contenant les références ZST.',
            'userConnecter'     => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
    }

    private function modificationChoixDeRef(
        $dals,
        $dalrList,
        ?string $observation,
        string $numDa,
        Request $request
    ): array {
        $refs = $this->recuperationDesRef($request);

        $notification = $this->traiterProposition(
            $dals,
            $dalrList,
            $observation,
            $numDa,
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

        $da = $this->modificationDesTable($numDa, $numeroVersionMax);

        /** CREATION EXCEL */
        $nomEtChemin = $this->creationExcel($numDa, $numeroVersionMax);

        /** Ajout non fichier de reference zst */
        $da->setNonFichierRefZst($nomEtChemin['fileName']);
        self::$em->flush();

        return $nomEtChemin;
    }

    private function modificationDesTable(string $numDa, int $numeroVersionMax): DemandeAppro
    {
        /** @var DemandeAppro */
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        if ($da) {
            $da
                ->setEstValidee(true)
                ->setValidePar(Controller::getUser()->getNomUtilisateur())
                ->setValidateur(Controller::getUser())
                ->setStatutDal(DemandeAppro::STATUT_VALIDE)
            ;
        }

        /** @var DemandeApproL */
        $dal = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        if (!empty($dal)) {
            foreach ($dal as $item) {
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setValidePar(Controller::getUser()->getNomUtilisateur())
                        ->setStatutDal(DemandeAppro::STATUT_VALIDE)
                    ;
                }
            }
        }

        /** @var DemandeApproLR */
        $dalr = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa]);
        if (!empty($dalr)) {
            foreach ($dalr as $item) {
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setValidePar(Controller::getUser()->getNomUtilisateur())
                        ->setStatutDal(DemandeAppro::STATUT_VALIDE)
                    ;
                }
            }
        }

        return $da;
    }

    private function traitementPourBtnEnregistrer($dalrList, Request $request, $dals, ?string $observation, string $numDa, DemandeAppro $da): void
    {
        /** RECUPERATION DE NUMERO DE page et NUMERO de ligne de tableau */
        $refs = $this->recuperationDesRef($request);

        $notification = $this->traiterProposition(
            $dals,
            $dalrList,
            $observation,
            $numDa,
            $refs,
            "La proposition a été soumis à l'atelier",
            true
        );


        $this->modificationChoixEtligneDal($refs, $dals);

        /** ENVOIE D'EMAIL à l'ATE pour les propositions*/
        $this->envoyerMailPropositionAuxAte([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            'mailDemandeur' => $da->getUser()->getMail(),
            'hydratedDa'    => $this->demandeApproRepository->findAvecDernieresDALetLR($da->getId()),
            'observation'   => $observation,
            'service'       => 'appro',
            'userConnecter' => Controller::getUser()->getPersonnels()->getNom() . ' ' . Controller::getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
    }

    private function getNouveauDal($numDa)
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $dalNouveau = $this->recuperationRectificationDonnee($numDa, $numeroVersionMax);
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

    private function traiterProposition($dals, $dalrList, ?string $observation, string $numDa, array $refs, string $messageSuccess, bool $doSaveDb = false): array
    {
        if ($dalrList->isEmpty() && empty($refs)) {
            return $this->notification('danger', "Aucune modification n'a été effectuée");
        }

        if ($doSaveDb) {
            $this->enregistrementDb($dals, $dalrList);
        }

        $this->duplicationDataDaL($dals);

        if ($observation !== null) {
            $this->insertionObservation($observation, $numDa);
        }

        $this->modificationStatutDal($numDa, DemandeAppro::STATUT_SOUMIS_ATE);
        $this->modificationStatutDa($numDa, DemandeAppro::STATUT_SOUMIS_ATE);

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
                    self::$em->persist($dalr);
                }
            }
        }
        self::$em->flush();
    }

    /** 
     * Fonctions pour envoyer un mail des propositions à la service Appro 
     */
    private function envoyerMailPropositionAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => $tab['mailDemandeur'],
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "propositionDa",
                'subject'    => "{$tab['numDa']} - Proposition créee par l'Appro ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/proposition/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }

    /** 
     * Fonctions pour envoyer un mail de validation à la service Ate
     */
    private function envoyerMailValidationAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => $tab['mailDemandeur'],
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "validationDa",
                'subject'    => "{$tab['numDa']} - Proposition(s) validée(s) par l'ATELIER",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list")
            ],
            'attachments' => [
                $tab['filePath'] => $tab['fileName'],
            ],
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables'], $content['attachments']);
    }

    /** 
     * Fonctions pour envoyer un mail de validation à la service Appro 
     */
    private function envoyerMailValidationAuxAppro(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => DemandeAppro::MAIL_APPRO,
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "validationAteDa",
                'subject'    => "{$tab['numDa']} - Proposition(s) validée(s) par l'ATELIER",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/proposition/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }

    /** 
     * Fonctions pour envoyer un mail sur l'observation à la service Appro 
     */
    private function envoyerMailObservation(array $tab)
    {
        $email       = new EmailService;

        $to = $tab['service'] == 'atelier' ? DemandeAppro::MAIL_APPRO : $tab['mailDemandeur'];

        $content = [
            'to'        => $to,
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "commente",
                'subject'    => "{$tab['numDa']} - Observation ajoutée par l'" . strtoupper($tab['service']),
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list"),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }

    private function modificationStatutDal(string $numDa, string $statut): void
    {
        $numeroVersionMax = self::$em->getRepository(DemandeApproL::class)->getNumeroVersionMax($numDa);
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($dals as  $dal) {
            $dal->setStatutDal($statut);
            $dal->setEdit(self::EDIT);
            self::$em->persist($dal);
        }

        self::$em->flush();
    }

    private function modificationStatutDa(string $numDa, string $statut): void
    {
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $da->setStatutDal($statut);

        self::$em->persist($da);
        self::$em->flush();
    }

    private function statutDa()
    {
        if (Controller::estUserDansServiceAtelier()) {
            $statut = self::DA_STATUT_CHANGE_CHOIX_ATE;
        } else {
            $statut = DemandeAppro::STATUT_SOUMIS_ATE;
        }
        return $statut;
    }

    private function insertionObservation(?string $observation, string $numDa): void
    {
        $daObservation = $this->recupDonnerDaObservation($observation, $numDa);

        self::$em->persist($daObservation);

        self::$em->flush();
    }

    /** 
     * Insertion de l'observation dans la Base de donnée
     */
    private function insertionObservationSeul(DaObservation $daObservation, DemandeAppro $demandeAppro)
    {
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $daObservation->getObservation());

        $daObservation
            ->setObservation($text)
            ->setNumDa($demandeAppro->getNumeroDemandeAppro())
            ->setUtilisateur(Controller::getUser()->getNomUtilisateur())
        ;

        self::$em->persist($daObservation);
        self::$em->flush();
    }

    private function recupDonnerDaObservation(?string $observation, string $numDa): DaObservation
    {
        return $this->daObservation
            ->setNumDa($numDa)
            ->setUtilisateur(Controller::getUser()->getNomUtilisateur())
            ->setObservation($observation)
        ;
    }



    private function modificationTableDaL(array $refs,  $data): void
    {
        $dals = $this->recupDataDaL($refs,  $data);
        $dalrs = $this->recupDataDaLR($refs,  $data);

        for ($i = 0; $i < count($dals); $i++) {
            $dals[$i][0]
                // ->setQteDispo($dalrs[$i][0]->getQteDispo())
                // ->setArtRefp($dalrs[$i][0]->getArtRefp() == '' ? NULL : $dalrs[$i][0]->getArtRefp())
                // ->setArtFams1($dalrs[$i][0]->getArtFams1() == '' ? NULL : $dalrs[$i][0]->getArtFams1())
                // ->setArtFams2($dalrs[$i][0]->getArtFams2() == '' ? NULL : $dalrs[$i][0]->getArtFams2())
                // ->setArtDesi($dalrs[$i][0]->getArtDesi() == '' ? NULL : $dalrs[$i][0]->getArtDesi())
                // ->setCodeFams1($dalrs[$i][0]->getCodeFams1() == '' ? NULL : $dalrs[$i][0]->getCodeFams1())
                // ->setCodeFams2($dalrs[$i][0]->getCodeFams2() == '' ? NULL : $dalrs[$i][0]->getCodeFams2())
                ->setEstValidee($dalrs[$i][0]->getEstValidee())
                ->setEstModifier($dalrs[$i][0]->getChoix())
                // ->setCatalogue($dalrs[$i][0]->getArtFams1() == NULL && $dalrs[$i][0]->getArtFams2() == NULL ? FALSE : TRUE)
                // ->setPrixUnitaire($this->daModel->getPrixUnitaire($dalrs[$i][0]->getArtRefp())[0])
                // ->setNomFicheTechnique($dalrs[$i][0]->getNomFicheTechnique())
            ;
            self::$em->persist($dals[$i][0]);
        }

        self::$em->flush();
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

    /**
     * Dupliquer les lignes de la table demande_appro_L
     *
     * @param array $refs
     * @param [type] $data
     * @return array
     */
    private function duplicationDataDaL($data): void
    {
        $numeroVersionMax = self::$em->getRepository(DemandeApproL::class)->getNumeroVersionMax($data[0]->getNumeroDemandeAppro());
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);

        foreach ($dals as $dal) {
            // On clone l'entité (copie en mémoire)
            $newDal = clone $dal;
            $newDal->setNumeroVersion($this->autoIncrement($dal->getNumeroVersion())); // Incrémenter le numéro de version

            // Doctrine crée un nouvel ID automatiquement (ne pas setter manuellement)
            self::$em->persist($newDal);
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
            self::$em->persist($dalr);
        }
        self::$em->flush();
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
            self::$em->persist($dalr);
        }
        self::$em->flush();
    }

    private function notification(string $type, string $message): array
    {
        return [
            'type'    => $type,
            'message' => $message,
        ];
    }

    private function enregistrementDb($data, $dalrList)
    {
        foreach ($dalrList as $demandeApproLR) {
            $DAL = $this->filtreDal($data, $demandeApproLR);
            $demandeApproLR = $this->ajoutDonnerDaLR($DAL, $demandeApproLR);
            self::$em->persist($demandeApproLR);
        }
        self::$em->flush();
    }

    private function filtreDal($data, $demandeApproLR): ?object
    {
        return  $data->filter(function ($entite) use ($demandeApproLR) {
            return $entite->getNumeroLigne() === $demandeApproLR->getNumeroLigne();
        })->first();
    }

    private function ajoutDonnerDaLR(DemandeApproL $DAL, DemandeApproLR $demandeApproLR)
    {
        $demandeApproLR_Ancien = $this->demandeApproLRRepository->getDalrByPageAndRow($DAL->getNumeroDemandeAppro(), $demandeApproLR->getNumeroLigne(), $demandeApproLR->getNumLigneTableau());

        $file = $demandeApproLR->getNomFicheTechnique(); // fiche technique de la DALR
        $fileNames = $demandeApproLR->getFileNames(); // pièces jointes de la DALR

        if ($demandeApproLR_Ancien) {
            $this->uploadFTForDalr($file, $demandeApproLR_Ancien);
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
                ->setStatutDal(DemandeAppro::STATUT_SOUMIS_ATE)
                ->setNumeroDemandeDit($DAL->getNumeroDit())
                ->setJoursDispo($DAL->getJoursDispo())
            ;

            if ($file) {
                $this->uploadFTForDalr($file, $demandeApproLR);
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
                    $fileName = $this->uploadPJForDalr($file, $dalr, $i); // Appel de la méthode pour uploader le fichier
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
