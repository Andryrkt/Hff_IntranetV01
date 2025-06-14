<?php

namespace App\Controller\da;

use DateTime;
use App\Model\da\DaModel;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeApproLRCollection;
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
    use lienGenerique;

    private const ID_ATELIER = 3;
    private const DA_STATUT = 'Proposition achats';
    private const DA_STATUT_SOUMIS_APPRO = 'Demande d’achats';
    private const DA_STATUT_VALIDE = 'Bon d’achats validé';
    private const DA_STATUT_CHANGE_CHOIX_ATE = 'changement de choix par l\'ATE';
    private const EDIT = 0;

    private DaModel $daModel;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DaObservation $daObservation;
    private  $daObservationRepository;
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
        $form = self::$validator->createBuilder(DemandeApproLRCollectionType::class, $DapLRCollection)->getForm();

        $this->traitementFormulaire($form, $dals, $request, $numDa, $da);

        $observations = $this->daObservationRepository->findBy(['numDa' => $numDa], ['dateCreation' => 'DESC']);

        self::$twig->display('da/proposition.html.twig', [
            'da' => $da,
            'id' => $id,
            'dit_id' => $dit->getId(),
            'form' => $form->createView(),
            'observations' => $observations,
            'numDa' => $numDa,
            'estAte' => $this->estUserDansServiceAtelier(),
            'nePeutPasModifier' => $this->nePeutPasModifier($da)
        ]);
    }

    private function estUserDansServiceAtelier()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }

    private function nePeutPasModifier($demandeAppro)
    {
        return ($this->estUserDansServiceAtelier() && ($demandeAppro->getStatutDal() == self::DA_STATUT_SOUMIS_APPRO || $demandeAppro->getStatutDal() == self::DA_STATUT_VALIDE));
    }

    private function traitementFormulaire($form, $dals, Request $request, string $numDa, DemandeAppro $da)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            // dd($dalrList);
            $observation = $form->getData()->getObservation();

            if ($request->request->has('enregistrer')) {
                $this->traitementPourBtnEnregistrer($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('changement')) {
                $this->traitementPourBtnValider($request, $dals, $numDa, $dalrList, $observation, $da);
            }
        }
    }

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
        $this->validerProposition($numDa);

        /** ENVOIE D'EMAIL à l'APPRO pour le changement des références et la validation des propositions */
        $nouvAncienDal = $this->nouveauEtAncienDal($da,  $numDa);
        $this->envoyerMailAuxAppro([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            // 'dalAncien'     => $nouvAncienDal['dalAncien'],
            'dalNouveau'    => $nouvAncienDal['dalNouveau'],
            'service'       => 'atelier',
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
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

    private function validerProposition(string $numDa): void
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        $da = $this->modificationDesTable($numDa, $numeroVersionMax);

        /** CREATION EXCEL */
        $nomEtChemin = $this->creationExcel($numDa, $numeroVersionMax);

        /** Ajout non fichier de reference zst */
        $da->setNonFichierRefZst($nomEtChemin['fileName']);
        self::$em->flush();
    }

    private function modificationDesTable(string $numDa, int $numeroVersionMax): DemandeAppro
    {
        /** @var DemandeAppro */
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        if ($da) {
            $da
                ->setEstValidee(true)
                ->setValidePar($this->getUser()->getNomUtilisateur())
                ->setStatutDal(self::DA_STATUT_VALIDE)
            ;
        }

        /** @var DemandeApproL */
        $dal = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        if (!empty($dal)) {
            foreach ($dal as $item) {
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setValidePar($this->getUser()->getNomUtilisateur())
                        ->setStatutDal(self::DA_STATUT_VALIDE)
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
                        ->setValidePar($this->getUser()->getNomUtilisateur())
                    ;
                }
            }
        }

        return $da;
    }

    private function creationExcel(string $numDa, int $numeroVersionMax): array
    {
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        // Convertir les entités en tableau de données
        $dataExel = $this->transformationEnTableauAvecEntet($dals);

        //creation du fichier excel
        $date = new DateTime();
        $formattedDate = $date->format('Ymd_His');
        $fileName = $numDa . '_' . $formattedDate . '.xlsx';
        $filePath = $_ENV['BASE_PATH_FICHIER'] . '/da/ba/' . $fileName;
        $this->excelService->createSpreadsheetEnregistrer($dataExel, $filePath);

        return [
            'fileName' => $fileName,
            'filePath' => $filePath
        ];
    }

    private function transformationEnTableauAvecEntet($entities): array
    {
        $data = [];
        $data[] = ['constructeur', 'reference', 'quantité'];

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getArtConstp(),
                $entity->getArtRefp(),
                $entity->getQteDem(),
            ];
        }

        return $data;
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
        $nouvAncienDal = $this->nouveauEtAncienDal($da,  $numDa);
        $this->envoyerMailAuxAte([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            'dalAncien'     => $nouvAncienDal['dalAncien'],
            'dalNouveau'    => $nouvAncienDal['dalNouveau'],
            'observation'   => $observation,
            'service'       => 'appro',
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
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

        $this->modificationStatutDal($numDa);
        $this->modificationStatutDa($numDa);

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
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroLigneDem' => $dal->getNumeroLigne()]);
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
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => 'hoby.ralahy@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "propositionDa",
                'subject'    => "{$tab['numDa']} - proposition créee par l'Appro ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/proposition/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
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
                'statut'     => "validationAteDa",
                'subject'    => "{$tab['numDa']} - validation des propositions par l'ATE ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/proposition/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }

    private function modificationStatutDal(string $numDa): void
    {
        $numeroVersionMax = self::$em->getRepository(DemandeApproL::class)->getNumeroVersionMax($numDa);
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($dals as  $dal) {
            $dal->setStatutDal($this->statutDa());
            $dal->setEdit(self::EDIT);
            self::$em->persist($dal);
        }

        self::$em->flush();
    }

    private function modificationStatutDa(string $numDa): void
    {
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $da->setStatutDal($this->statutDa());

        self::$em->persist($da);
        self::$em->flush();
    }

    private function statutDa()
    {
        if ($this->estUserDansServiceAtelier()) {
            $statut = self::DA_STATUT_CHANGE_CHOIX_ATE;
        } else {
            $statut = self::DA_STATUT;
        }
        return $statut;
    }

    private function insertionObservation(?string $observation, string $numDa): void
    {
        $daObservation = $this->recupDonnerDaObservation($observation, $numDa);

        self::$em->persist($daObservation);

        self::$em->flush();
    }

    private function recupDonnerDaObservation(?string $observation, string $numDa): DaObservation
    {
        return $this->daObservation
            ->setNumDa($numDa)
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
            ->setObservation($observation)
        ;
    }



    private function modificationTableDaL(array $refs,  $data): void
    {
        $dals = $this->recupDataDaL($refs,  $data);
        $dalrs = $this->recupDataDaLR($refs,  $data);

        for ($i = 0; $i < count($dals); $i++) {
            $dals[$i][0]
                ->setQteDispo($dalrs[$i][0]->getQteDispo())
                ->setArtRefp($dalrs[$i][0]->getArtRefp() == '' ? NULL : $dalrs[$i][0]->getArtRefp())
                ->setArtFams1($dalrs[$i][0]->getArtFams1() == '' ? NULL : $dalrs[$i][0]->getArtFams1())
                ->setArtFams2($dalrs[$i][0]->getArtFams2() == '' ? NULL : $dalrs[$i][0]->getArtFams2())
                ->setArtDesi($dalrs[$i][0]->getArtDesi() == '' ? NULL : $dalrs[$i][0]->getArtDesi())
                ->setCodeFams1($dalrs[$i][0]->getCodeFams1() == '' ? NULL : $dalrs[$i][0]->getCodeFams1())
                ->setCodeFams2($dalrs[$i][0]->getCodeFams2() == '' ? NULL : $dalrs[$i][0]->getCodeFams2())
                ->setEstValidee($dalrs[$i][0]->getEstValidee())
                ->setEstModifier($dalrs[$i][0]->getChoix())
                ->setCatalogue($dalrs[$i][0]->getArtFams1() == NULL && $dalrs[$i][0]->getArtFams2() == NULL ? FALSE : TRUE)
                ->setPrixUnitaire($this->daModel->getPrixUnitaire($dalrs[$i][0]->getArtRefp())[0])
                ->setNomFicheTechnique($dalrs[$i][0]->getNomFicheTechnique())
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
            $dalrs[] = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $refs[$i][0], 'numLigneTableau' => $refs[$i][1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()], ['numeroLigneDem' => 'ASC']);
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
            $dalrsAll = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $ref[0], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()]);
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
            $dalrs = $this->demandeApproLRRepository->findBy(['numeroLigneDem' => $ref[0], 'numLigneTableau' => $ref[1], 'numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro()]);
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
            return $entite->getNumeroLigne() === $demandeApproLR->getNumeroLigneDem();
        })->first();
    }

    private function ajoutDonnerDaLR($DAL, $demandeApproLR)
    {
        $demandeApproLR_Ancien = $this->demandeApproLRRepository->getDalrByPageAndRow($DAL->getNumeroDemandeAppro(), $demandeApproLR->getNumeroLigneDem(), $demandeApproLR->getNumLigneTableau());

        $file = $demandeApproLR->getNomFicheTechnique();

        if ($demandeApproLR_Ancien) {
            $this->uploadFile($file, $demandeApproLR_Ancien);

            $DAL->getDemandeApproLR()->add($demandeApproLR_Ancien);

            return $demandeApproLR_Ancien;
        } else {
            $libelleSousFamille = $this->daModel->getLibelleSousFamille($demandeApproLR->getArtFams2(), $demandeApproLR->getArtFams1()); // changement de code sous famille en libelle sous famille
            $libelleFamille = $this->daModel->getLibelleFamille($demandeApproLR->getArtFams1()); // changement de code famille en libelle famille

            $demandeApproLR
                ->setDemandeApproL($DAL)
                ->setNumeroDemandeAppro($DAL->getNumeroDemandeAppro())
                ->setQteDem($DAL->getQteDem())
                ->setArtConstp($DAL->getArtConstp())
                ->setCodeFams1($demandeApproLR->getArtFams1() == '' ? NULL : $demandeApproLR->getArtFams1()) // ceci doit toujour avant le setArtFams1
                ->setCodeFams2($demandeApproLR->getArtFams2() == '' ? NULL : $demandeApproLR->getArtFams2()) // ceci doit toujour avant le setArtFams2
                ->setArtFams1($libelleFamille == '' ? NULL : $libelleFamille) // ceci doit toujour après le codeFams1
                ->setArtFams2($libelleSousFamille == '' ? NULL : $libelleSousFamille) // ceci doit toujour après le codeFams2
            ;

            if ($file) {
                $this->uploadFile($file, $demandeApproLR);
            }

            $DAL->getDemandeApproLR()->add($demandeApproLR);

            return $demandeApproLR;
        }
    }

    /**
     * TRAITEMENT DES FICHIER UPLOAD
     * (copier le fichier uploader dans une répertoire et le donner un nom)
     */
    private function uploadFile(UploadedFile $file, DemandeApproLR $dalr)
    {
        $fileName = sprintf(
            'ft_%s_%s_%s_%s.%s',
            $dalr->getNumeroDemandeAppro(),
            $dalr->getNumeroLigneDem(),
            $dalr->getNumLigneTableau(),
            date("YmdHis"),
            $file->getClientOriginalExtension()
        );

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/da/fichiers/';

        // Assurer que le répertoire existe
        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        $dalr->setNomFicheTechnique($fileName);
    }
}
