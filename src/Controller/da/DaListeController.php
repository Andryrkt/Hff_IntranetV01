<?php

namespace App\Controller\da;

use App\Model\da\DaModel;
use App\Entity\admin\Agence;
use App\Entity\da\DaValider;
use App\Form\da\DaSearchType;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeApproLR;
use App\Controller\Traits\da\DaTrait;
use App\Repository\dit\DitRepository;
use App\Form\da\HistoriqueModifDaType;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\lienGenerique;
use App\Entity\admin\utilisateur\Role;
use App\Repository\admin\AgenceRepository;
use App\Repository\da\DaValiderRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DaSoumissionBcRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\da\DaHistoriqueDemandeModifDARepository;

/**
 * @Route("/demande-appro")
 */
class DaListeController extends Controller
{
    use lienGenerique;
    use DaTrait;

    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DaHistoriqueDemandeModifDARepository $historiqueModifDARepository;
    private DaModel $daModel;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaValiderRepository $daValiderRepository;
    private DaValider $daValider;
    private AgenceRepository $agenceRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->historiqueModifDARepository = self::$em->getRepository(DaHistoriqueDemandeModifDA::class);
        $this->daModel = new DaModel();
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daValiderRepository = self::$em->getRepository(DaValider::class);
        $this->daValider = new DaValider();
        $this->agenceRepository = self::$em->getRepository(Agence::class);
    }

    /**
     * @Route("/list", name="da_list")
     */
    public function listeDA(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation */


        $historiqueModifDA = new DaHistoriqueDemandeModifDA();
        $numDaNonDeverrouillees = $this->historiqueModifDARepository->findNumDaOfNonDeverrouillees();

        $form = self::$validator->createBuilder(DaSearchType::class, null, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $this->sessionService->remove('firstCharge');

        //recuperation de l'id de l'agence de l'utilisateur connecter
        $codeAgence = Controller::getUser()->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findOneBy(['codeAgence' => $codeAgence])->getId();
        // recupération des données de la DA
        $das = $this->daRepository->findDaData($criteria, $idAgenceUser);
        $this->deleteDal($das);

        $this->ajoutInfoDit($das);
        $dasFiltered  = $this->filtreDal($das);
        /** modification des donnée dans DaValider  (Tsy azo alefa any afara an'ity toerana misy azy inty)*/
        $this->ChangeQteDaValider($dasFiltered);
        $this->ChangeStatutBcDaValider($dasFiltered);

        /**  ajout des donners */
        $this->ajoutStatutBc($dasFiltered);
        $this->ajoutQte($dasFiltered);
        $this->ajoutStatutDal($dasFiltered);

        $this->modificationIdDALsDansDALRs($dasFiltered);
        $this->modificationDateRestant($dasFiltered);
        $this->demandeDeverouillageDA($dasFiltered);
        $this->verouillerOuNonLesDa($dasFiltered);
        $this->initialiserHistorique($historiqueModifDA);

        // changer le statut de la DA si la situation des pièce est tout livré
        $this->modificationStatutSiSituationPieceLivree($dasFiltered);


        $formHistorique = self::$validator->createBuilder(HistoriqueModifDaType::class, $historiqueModifDA)->getForm();

        $this->traitementFormulaireDeverouillage($formHistorique, $request); // traitement du formulaire de déverrouillage de la DA

        self::$twig->display('da/list.html.twig', [
            'data'                   => $dasFiltered,
            'form'                   => $form->createView(),
            'formHistorique'         => $formHistorique->createView(),
            'serviceAtelier'         => Controller::estUserDansServiceAtelier(),
            'serviceAppro'           => Controller::estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
        ]);
    }

    private function modificationStatutSiSituationPieceLivree(array $dasFiltered): void
    {
        foreach ($dasFiltered as $da) {
            $sumQteDemEtLivrer = $this->daValiderRepository->getSumQteDemEtLivrer($da->getNumeroDemandeAppro());
            if ((int)$sumQteDemEtLivrer['qteDem'] != 0 && (int)$sumQteDemEtLivrer['qteLivrer'] != 0 && (int)$sumQteDemEtLivrer['qteDem'] === (int)$sumQteDemEtLivrer['qteLivrer']) {
                $this->modificationStatutDalr($da->getNumeroDemandeAppro(), DemandeAppro::STATUT_TERMINER);
                $this->modificationStatutDal($da->getNumeroDemandeAppro(), DemandeAppro::STATUT_TERMINER);
                $this->modificationStatutDa($da->getNumeroDemandeAppro(), DemandeAppro::STATUT_TERMINER);
                $this->modificationStatutDaValider($da->getNumeroDemandeAppro(), DemandeAppro::STATUT_TERMINER);
            }
        }
    }

    /** 
     * @Route("/deverrouiller-da/{idDa}", name="da_deverrouiller_da")
     */
    public function deverouillerDa(int $idDa)
    {
        // verification si user connecter
        $this->verifierSessionUtilisateur();
        $demandeAppro = $this->daRepository->find($idDa);
        /** @var DaHistoriqueDemandeModifDA $historiqueModifDA */
        $historiqueModifDA = $this->historiqueModifDARepository->findOneBy(['demandeAppro' => $demandeAppro]);

        if (!$demandeAppro) {
            $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'La demande d\'approvisionnement n\'existe pas.']);
            return $this->redirectToRoute('da_list');
        } else if (!$historiqueModifDA) {
            $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Aucune demande de déverrouillage n\'a été faite pour cette DA.']);
            return $this->redirectToRoute('da_list');
        } else if ($historiqueModifDA->getEstDeverouillee()) {
            $this->sessionService->set('notification', ['type' => 'warning', 'message' => 'La demande d\'approvisionnement est déjà déverrouillée.']);
            return $this->redirectToRoute('da_list');
        } else {
            if (!Controller::estUserDansServiceAppro()) {
                $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Vous n\'êtes pas autorisé à déverrouiller cette demande.']);
                return $this->redirectToRoute('da_list');
            }

            $this->duplicationDataDaL($demandeAppro->getDAL()->toArray());
            $this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_SOUMIS_ATE);
            $this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro(), DemandeAppro::STATUT_SOUMIS_ATE);

            $historiqueModifDA->setEstDeverouillee(true); // Marquer la demande comme déverrouillée
            self::$em->persist($historiqueModifDA);
            self::$em->flush();

            $this->envoyerMailAuxAte([
                'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
                'mailDemandeur' => $demandeAppro->getUser()->getMail(),
                'userConnecter' => Controller::getUser()->getNomUtilisateur(),
            ]);

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La demande d\'approvisionnement a été déverrouillée avec succès.']);
            return $this->redirectToRoute('da_list');
        }
    }

    /**
     * Permet de modifier l'id de la relation demande_appro_L dans la table demande_appro_LR
     *
     * @param array $dasFiltered
     * 
     * @return void
     */
    public function modificationIdDALsDansDALRs(array $dasFiltered): void
    {
        foreach ($dasFiltered as $da) {
            foreach ($da->getDAL() as $dal) {
                $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $dal->getNumeroDemandeAppro(), 'numeroLigneDem' => $dal->getNumeroLigne()]);
                if (!empty($dalrs)) {
                    foreach ($dalrs as $dalr) {
                        $dalr->setDemandeApproL($dal);
                        self::$em->persist($dalr);
                    }
                }
            }
        }
        self::$em->flush();
    }

    /** 
     * Permet de calculer le nombre de jours restants pour chaque DAL
     */
    private function modificationDateRestant($dasFiltered)
    {
        foreach ($dasFiltered as $da) {
            $this->ajoutNbrJourRestant($da->getDAL());
            foreach ($da->getDAL() as $dal) {
                self::$em->persist($dal);
            }
        }

        self::$em->flush();
    }

    /** 
     */
    private function demandeDeverouillageDA($dasFiltered)
    {
        /** @var DemandeAppro $da */
        foreach ($dasFiltered as $da) {
            $dit = $da->getDit();
            $statutOr = $dit->getStatutOr();
            $constructeurs = $this->daModel->getAllConstructeur($dit->getNumeroDemandeIntervention());

            if (in_array($statutOr, ['Refusé client interne', 'Refusé chef atelier'])) { // statut de l'or: refusé / non validé
                if (in_array('ZST', $constructeurs)) { // l'OR est munie d'articles ZST.
                    $da->setDemandeDeverouillage(true);
                }
            }
        }
    }

    /**
     * supprime les ligne de DAl qui est dupliquer mais pas modifier (l'utilisateur ne clique pas sur le bouton modifier)
     *
     * @param array $das
     * @return void
     */
    private function deleteDal(array $das): void
    {
        foreach ($das as $da) {
            foreach ($da->getDAL() as $dal) {
                if ($dal->getEdit() !== 3 && $dal->getEdit() !== 0 && !is_null($dal->getEdit()) && $dal->getEstValidee() == false) {
                    $demandeAppro = $dal->getDemandeAppro();
                    $demandeAppro->removeDAL($dal); // supprime le lien
                    self::$em->remove($dal); // supprime l'entité

                    self::$em->flush();
                }
            }
        }
    }

    private function filtreDal(array $das): array
    {
        foreach ($das as $da) {
            $numDa = $da->getNumeroDemandeAppro();
            $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
            // filtre une collection de versions selon le numero de version max
            $dalDernieresVersions = $da->getDAL()->filter(function ($item) use ($numeroVersionMax) {
                return $item->getNumeroVersion() == $numeroVersionMax && $item->getDeleted() == 0;
            });

            $da->setDAL($dalDernieresVersions);

            //da final
            $daFinal = $this->recuperationRectificationDonnee($numDa, (int)$numeroVersionMax);
            $da->setDaValiderOuProposer($daFinal);
        }


        return $das;
    }

    private function ajoutStatutDal($dasFiltereds): void
    {
        foreach ($dasFiltereds as $dasFiltered) {
            foreach ($dasFiltered->getDaValiderOuProposer() as $davp) {
                $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMaxDit($dasFiltered->getNumeroDemandeDit());
                $daValiders = $this->daValiderRepository->findBy(['numeroDemandeAppro' => $dasFiltered->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
                foreach ($daValiders as $daValider) {
                    $davp->setStatutDal($daValider->getStatutDal());
                }
            }
        }
    }

    private function ajoutStatutBc($dasFiltereds): void
    {
        foreach ($dasFiltereds as $dasFiltered) {
            foreach ($dasFiltered->getDaValiderOuProposer() as $davp) {
                $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMaxDit($dasFiltered->getNumeroDemandeDit());
                $daValiders = $this->daValiderRepository->findBy(['numeroDemandeAppro' => $dasFiltered->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
                foreach ($daValiders as $daValider) {
                    $davp->setStatutBc($daValider->getStatutCde());
                }
            }
        }
    }

    private function ajoutQte(array $dasFiltereds): void
    {
        foreach ($dasFiltereds as $daFiltered) {
            $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMaxDit($daFiltered->getNumeroDemandeDit());

            if ($numeroVersionMax === null) {
                continue; // Sauter si aucune version trouvée
            }

            $daValiderList = $this->daValiderRepository->findBy([
                'numeroDemandeAppro' => $daFiltered->getNumeroDemandeAppro(),
                'numeroVersion' => $numeroVersionMax
            ]);

            if (empty($daValiderList)) {
                continue;
            }

            foreach ($daFiltered->getDaValiderOuProposer() as $daVP) {
                foreach ($daValiderList as $daValider) {
                    if ($daVP->getArtRefp() === $daValider->getArtRefp() && $daVP->getArtDesi() === $daValider->getArtDesi()) {
                        $daVP->setQteLivee($daValider->getQteLivrer());
                        $daVP->setQteALivrer($daValider->getQteALivrer());
                        $daVP->setQteEnAttent($daValider->getQteEnAttent());
                    }
                }
            }
        }
    }


    private function ChangeQteDaValider($dasFiltereds)
    {
        foreach ($dasFiltereds as $dasFiltered) {
            $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMaxDit($dasFiltered->getNumeroDemandeDit());
            $daValiders = $this->daValiderRepository->findBy(['numeroDemandeAppro' => $dasFiltered->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
            $qtes = $this->daModel->getEvolutionQte($dasFiltered->getNumeroDemandeDit(), false);
            if (array_key_exists(0, $qtes) && !empty($daValiders)) {
                foreach ($daValiders as $daValider) {
                    foreach ($qtes as $qte) {
                        if ($qte['num_dit'] === $daValider->getNumeroDemandeDit() && $qte['reference'] === $daValider->getArtRefp() && $qte['designation'] === $daValider->getArtDesi()) {
                            $daValider->setQteLivrer((int)$qte['qte_livee']);
                            $daValider->setQteALivrer((int)$qte['qte_a_livrer']);
                            $daValider->setQteEnAttent((int)$qte['qte_reliquat']);
                            self::$em->persist($daValider);
                        }
                    }
                }
            }
        }
        self::$em->flush();
    }

    private function ChangeStatutBcDaValider($dasFiltereds): void
    {
        foreach ($dasFiltereds as $dasFiltered) {
            $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMaxDit($dasFiltered->getNumeroDemandeDit());
            $daValiders = $this->daValiderRepository->findBy(['numeroDemandeAppro' => $dasFiltered->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax]);
            if (!empty($daValiders)) {
                foreach ($daValiders as $daValider) {
                    $statutBc = $this->statutBc($daValider->getArtRefp(), $dasFiltered->getNumeroDemandeDit(), $daValider->getArtDesi());
                    $daValider->setStatutCde($statutBc);
                    self::$em->persist($daValider);
                }
            }
        }
        self::$em->flush();
    }


    private function ajoutInfoDit(array $datas): void
    {
        foreach ($datas as $data) {
            $data->setDit($this->ditRepository->findOneBy(['numeroDemandeIntervention' => $data->getNumeroDemandeDit()]));
        }
    }

    private function initialiserHistorique(DaHistoriqueDemandeModifDA $historique)
    {
        $historique
            ->setDemandeur(Controller::getUser()->getNomUtilisateur());
    }

    private function traitementFormulaireDeverouillage($form, $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $idDa = $form->get('idDa')->getData();

            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $this->daRepository->find($idDa);

            $historiqueModifDA = $this->historiqueModifDARepository->findOneBy(['demandeAppro' => $demandeAppro]);

            if ($historiqueModifDA) {
                $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Echec de la demande: une demande de déverouillage a déjà été envoyé sur cette DA.']);
                return $this->redirectToRoute('da_list');
            } else {
                /** @var DaHistoriqueDemandeModifDA $historiqueModifDA */
                $historiqueModifDA = $form->getData();
                $historiqueModifDA
                    ->setNumDa($demandeAppro->getNumeroDemandeAppro())
                    ->setDemandeAppro($demandeAppro)
                ;

                self::$em->persist($historiqueModifDA);
                self::$em->flush();

                $this->envoyerMailAuxAppro([
                    'numDa' => $demandeAppro->getNumeroDemandeAppro(),
                    'motif' => $historiqueModifDA->getMotif(),
                    'userConnecter' => Controller::getUser()->getNomUtilisateur(),
                ]);

                $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La demande de déverrouillage a été envoyée avec succès.']);
                return $this->redirectToRoute('da_list');
            }
        }
    }


    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => $tab['mailDemandeur'],
            'cc'        => [],
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "confirmationDeverrouillage",
                'subject'    => "{$tab['numDa']} - demande déverouillée par l'Appro ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list"),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
    }

    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAppro(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => DemandeAppro::MAIL_APPRO,
            'cc'        => [],
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "demandeDeverouillage",
                'subject'    => "{$tab['numDa']} - demande de déverouillage par l'ATE ",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list"),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
    }


    /**
     * Dupliquer les lignes de la table demande_appro_L
     */
    private function duplicationDataDaL($data): void
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($data[0]->getNumeroDemandeAppro());
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

    private function modificationStatutDal(string $numDa, string $statut): void
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($dals as  $dal) {
            $dal->setStatutDal($statut);
            self::$em->persist($dal);
        }

        self::$em->flush();
    }

    private function modificationStatutDa(string $numDa, string $statut): void
    {
        $da = $this->daRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $da->setStatutDal($statut);

        self::$em->persist($da);
        self::$em->flush();
    }

    private function modificationStatutDaValider(string $numDa, string $statut): void
    {
        $numeroVersionMax = $this->daValiderRepository->getNumeroVersionMax($numDa);
        $daValiders = $this->daValiderRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($daValiders as  $daValider) {
            $daValider->setStatutDal($statut);
            self::$em->persist($daValider);
        }

        self::$em->flush();
    }

    private function modificationStatutDalr(string $numDa, string $statut): void
    {
        $dalrs = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa]);

        foreach ($dalrs as  $dalr) {
            $dalr->setStatutDal($statut);
            self::$em->persist($dalr);
        }

        self::$em->flush();
    }

    /** 
     * Vérifie si la DA doit être verrouillée ou non pour chaque DA filtrée
     * @param array $dasFiltered
     * @return array
     */
    private function verouillerOuNonLesDa($dasFiltered)
    {
        foreach ($dasFiltered as $da) {
            foreach ($da->getDaValiderOuProposer() as $daValiderOuProposer) {
                $this->estVerouillerOuNon($daValiderOuProposer);
            }
        }
        return $dasFiltered;
    }

    /** 
     * Vérifie si la DA doit être verrouillée ou non en fonction de son statut et du service de l'utilisateur
     */
    private function estVerouillerOuNon($daValiderOuProposer)
    {
        $statutDa = $daValiderOuProposer->getStatutDal(); // Récupération du statut de la DA
        $statutBc = $daValiderOuProposer->getStatutBc(); // Récupération du statut du BC

        $estAppro = Controller::estUserDansServiceAppro();
        $estAtelier = Controller::estUserDansServiceAtelier();
        $estAdmin = in_array(Role::ROLE_ADMINISTRATEUR, Controller::getUser()->getRoleIds());
        $verouiller = false; // initialisation de la variable de verrouillage à false (déverouillée par défaut)

        $statutDaVerouillerAppro = [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_VALIDE];
        $statutDaVerouillerAtelier = [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_SOUMIS_APPRO];

        if (!$estAdmin && $estAppro && in_array($statutDa, $statutDaVerouillerAppro) && $statutBc !== DaSoumissionBc::STATUT_REFUSE) {
            /** 
             * Si l'utilisateur est Appro mais n'est pas Admin, et que le statut de la DA est TERMINER ou VALIDE,
             * et que le statut de la soumission BC n'est pas REFUSE, alors on verrouille la DA. 
             **/
            $verouiller = true;
        } elseif (!$estAdmin && $estAtelier && in_array($statutDa, $statutDaVerouillerAtelier)) {
            /** 
             * Si l'utilisateur est Atelier mais n'est pas Admin, et que le statut de la DA est TERMINER ou VALIDE ou SOUMIS A APPRO, 
             * alors on verrouille la DA.
             **/
            $verouiller = true;
        } elseif (!$estAtelier && !$estAppro && !$estAdmin) {
            /** 
             * Si l'utilisateur n'est ni Appro ni Atelier, et n'est pas Administrateur,
             * alors on verrouille la DA.
             */
            $verouiller = true;
        }

        // On applique le verrouillage ou non à l'entité Da Valider ou Proposer
        $daValiderOuProposer->setVerouille($verouiller);
    }
}
