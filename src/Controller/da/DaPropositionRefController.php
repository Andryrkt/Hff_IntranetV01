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
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaPropositionRefController extends Controller
{
    use lienGenerique;

    private const ID_ATELIER = 3;
    private const DA_STATUT = 'soumis à l’ATE';
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
            'estAte' => $this->estUserDansServiceAtelier()
        ]);
    }

    private function estUserDansServiceAtelier()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }

    private function traitementFormulaire($form, $dals, Request $request, string $numDa, DemandeAppro $da)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Récupérer les valeurs des champs caché
            $dalrList = $form->getData()->getDALR();
            $observation = $form->getData()->getObservation();

            if ($request->request->has('enregistrer')) {
                $this->traitementPourBtnEnregistrer($dalrList, $request, $dals, $observation, $numDa, $da);
            } elseif ($request->request->has('changement')) {
                $this->traitementPourBtnChangement($request, $dals, $numDa, $dalrList, $observation, $da);
            }
        }
    }

    private function traitementPourBtnChangement(Request $request, $dals, $numDa, $dalrList, $observation, $da)
    {
        $refsString = $request->request->get('refs');
        $selectedRefs = $refsString ? explode(',', $refsString) : [];
        $refs = $this->separationNbrPageLigne($selectedRefs);
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        if ($dalrList->isEmpty() && empty($refs)) {
            $notification = $this->notification('danger', "Aucune modification n'a été effectuée");
        } else {
            //$this->enregistrementDb($dals, $dalrList); // enregistrement des données dans la table demande_appro_LR
            $this->duplicationDataDaL($dals); // duplication des lignes de la table demande_appro_L
            if ($observation !== null) {
                $this->insertionObservation($observation, $numDa); // enregistrement de l'observation dans la table da_observation
            }
            $this->modificationStatutDal($numDa); // modification du statut de la table demande_appro_L
            $this->modificationStatutDa($numDa); // modification du statut de la table demande_appro

            $notification = $this->notification('success', "Le choix de la proposition a été changer avec succès");
        }

        $this->modificationChoixEtligneDal($refs, $dals);


        /** ENVOIE D'EMAIL à l'ATE pour les propositions*/
        $numeroVersionMaxAvant = $numeroVersionMax - 1;
        $dalNouveau = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $da->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
        $dalAncien = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $da->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMaxAvant]);

        /** NOTIFICATION MAIL */
        $this->envoyerMailAuxAppro([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            'dalAncien'     => $dalAncien,
            'dalNouveau'    => $dalNouveau,
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);


        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
    }


    private function traitementPourBtnEnregistrer($dalrList, Request $request, $dals, ?string $observation, string $numDa, DemandeAppro $da): void
    {
        $refsString = $request->request->get('refs');
        $selectedRefs = $refsString ? explode(',', $refsString) : [];
        $refs = $this->separationNbrPageLigne($selectedRefs);
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        if ($dalrList->isEmpty() && empty($refs)) {
            $notification = $this->notification('danger', "Aucune modification n'a été effectuée");
        } else {
            $this->enregistrementDb($dals, $dalrList); // enregistrement des données dans la table demande_appro_LR
            $this->duplicationDataDaL($dals); // duplication des lignes de la table demande_appro_L
            if ($observation !== null) {
                $this->insertionObservation($observation, $numDa); // enregistrement de l'observation dans la table da_observation
            }
            $this->modificationStatutDal($numDa); // modification du statut de la table demande_appro_L
            $this->modificationStatutDa($numDa); // modification du statut de la table demande_appro

            $notification = $this->notification('success', "La proposition a été soumis à l'atelier");
        }

        $this->modificationChoixEtligneDal($refs, $dals);

        /** ENVOIE D'EMAIL à l'ATE pour les propositions*/
        $numeroVersionMaxAvant = $numeroVersionMax - 1;
        $dalNouveau = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $da->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
        $dalAncien = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $da->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMaxAvant]);

        /** NOTIFICATION MAIL */
        $this->envoyerMailAuxAte([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            'dalAncien'     => $dalAncien,
            'dalNouveau'    => $dalNouveau,
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->sessionService->set('notification', ['type' => $notification['type'], 'message' => $notification['message']]);
        $this->redirectToRoute("da_list");
    }

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
            'to'        => 'hasina.andrianadison@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "propositionDa",
                'subject'    => "{$tab['numDa']} - proposition créee par l'Appro ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/demande-appro/proposition/" . $tab['id']),
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
            'to'        => 'hasina.andrianadison@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "chagementChoixAte",
                'subject'    => "{$tab['numDa']} - changement de choix de proposition par l'ATE ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/demande-appro/proposition/" . $tab['id']),
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

        return $demandeApproLR;
    }
}
