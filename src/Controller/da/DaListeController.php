<?php

namespace App\Controller\da;

use App\Model\da\DaModel;
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

    private const ID_ATELIER = 3;
    private const ID_APPRO = 16;

    private DemandeApproRepository $daRepository;
    private DitRepository $ditRepository;
    private DemandeApproLRepository $daLRepository;
    private DemandeApproLRRepository $dalrRepository;
    private DaHistoriqueDemandeModifDARepository $historiqueModifDARepository;
    private DaModel $daModel;
    private DaSoumissionBcRepository $daSoumissionBcRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;

    public function __construct()
    {
        parent::__construct();

        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->dalrRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->historiqueModifDARepository = self::$em->getRepository(DaHistoriqueDemandeModifDA::class);
        $this->daModel = new DaModel();
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
    }

    /**
     * @Route("/list", name="da_list")
     */
    public function listeDA(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

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

        $das = $this->daRepository->findDaData($criteria);
        $this->deleteDal($das);

        $this->ajoutInfoDit($das);
        $dasFiltered  = $this->filtreDal($das);
        $this->ajoutStatutBc($dasFiltered);
        $this->ajoutQte($dasFiltered);

        $this->modificationIdDALsDansDALRs($dasFiltered);
        $this->modificationDateRestant($dasFiltered);
        $this->demandeDeverouillageDA($dasFiltered);
        $this->initialiserHistorique($historiqueModifDA);

        $formHistorique = self::$validator->createBuilder(HistoriqueModifDaType::class, $historiqueModifDA)->getForm();

        $this->traitementFormulaireDeverouillage($formHistorique, $request); // traitement du formulaire de déverrouillage de la DA

        self::$twig->display('da/list.html.twig', [
            'data'                   => $dasFiltered,
            'form'                   => $form->createView(),
            'formHistorique'         => $formHistorique->createView(),
            'serviceAtelier'         => $this->estUserDansServiceAtelier(),
            'serviceAppro'           => $this->estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
        ]);
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
            if (!$this->estUserDansServiceAppro()) {
                $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Vous n\'êtes pas autorisé à déverrouiller cette demande.']);
                return $this->redirectToRoute('da_list');
            }

            $this->duplicationDataDaL($demandeAppro->getDAL()->toArray());
            $this->modificationStatutDal($demandeAppro->getNumeroDemandeAppro());
            $this->modificationStatutDa($demandeAppro->getNumeroDemandeAppro());

            $historiqueModifDA->setEstDeverouillee(true); // Marquer la demande comme déverrouillée
            self::$em->persist($historiqueModifDA);
            self::$em->flush();

            $this->envoyerMailAuxAte([
                'numDa' => $demandeAppro->getNumeroDemandeAppro(),
                'userConnecter' => $this->getUser()->getNomUtilisateur(),
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
                $dalrs = $this->dalrRepository->findBy(['numeroDemandeAppro' => $dal->getNumeroDemandeAppro(), 'numeroLigneDem' => $dal->getNumeroLigne()]);
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
            $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($da->getNumeroDemandeAppro());
            // filtre une collection de versions selon le numero de version max
            $dalDernieresVersions = $da->getDAL()->filter(function ($item) use ($numeroVersionMax) {
                return $item->getNumeroVersion() == $numeroVersionMax && $item->getDeleted() == 0;
            });

            $da->setDAL($dalDernieresVersions);
        }


        return $das;
    }

    private function ajoutStatutBc($dasFiltereds): void
    {
        foreach ($dasFiltereds as $dasFiltered) {
            foreach ($dasFiltered->getDAL() as $dal) {
                $statutBc = $this->statutBc($dal->getArtRefp(), $dasFiltered->getNumeroDemandeDit());
                $dal->setStatutBc($statutBc);
            }
        }
    }

    private function ajoutQte($dasFiltereds)
    {
        foreach ($dasFiltereds as $dasFiltered) {
            foreach ($dasFiltered->getDAL() as $dal) {
                $qte = $this->daModel->getEvolutionQte($dasFiltered->getNumeroDemandeDit());
                if (array_key_exists(0, $qte)) {
                    $dal->setQteLivee((int)$qte[0]['qte_a_livrer']);
                    $dal->setQteALivrer((int)$qte[0]['qte_livee']);
                }
            }
        }
    }

    private function statutBc(?string $ref, string $numDit)
    {
        $situationCde = $this->daModel->getSituationCde($ref, $numDit);

        $statutDa = $this->daRepository->getStatut($numDit);

        $statutOr = $this->ditOrsSoumisAValidationRepository->getStatut($numDit);
        $numcde = array_key_exists(0, $situationCde) ? $situationCde[0]['num_cde'] : '';
        $bcExiste = $this->daSoumissionBcRepository->bcExists($numcde);

        $statutBc = $this->daSoumissionBcRepository->getStatut($numcde);


        $statut_bc = '';
        if (!array_key_exists(0, $situationCde)) {
            $statut_bc = $statutBc;
        } elseif ($situationCde[0]['num_cde'] == '' && $statutDa == DemandeAppro::STATUT_VALIDE && $statutOr == 'Validé') {
            $statut_bc = 'A générer';
        } elseif ((int)$situationCde[0]['num_cde'] > 0 && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'TE') {
            $statut_bc = 'A éditer';
        } elseif ((int)$situationCde[0]['num_cde'] > 0 && $situationCde[0]['slor_natcm'] == 'C' && $situationCde[0]['position_bc'] == 'ED' && !$bcExiste) {
            $statut_bc = 'A soumettre à validation';
        } elseif ($situationCde[0]['position_bc'] == 'ED' && $statutBc == 'Validé') {
            $statut_bc = 'A envoyer au fournisseur';
        } else {
            $statut_bc = $statutBc;
        }

        return $statut_bc;
    }

    private function ajoutInfoDit(array $datas): void
    {
        foreach ($datas as $data) {
            $data->setDit($this->ditRepository->findOneBy(['numeroDemandeIntervention' => $data->getNumeroDemandeDit()]));
        }
    }


    private function estUserDansServiceAtelier()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }

    private function estUserDansServiceAppro()
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_APPRO, $serviceIds);
    }

    private function initialiserHistorique(DaHistoriqueDemandeModifDA $historique)
    {
        $historique
            ->setDemandeur($this->getUser()->getNomUtilisateur());
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
                    'userConnecter' => $this->getUser()->getNomUtilisateur(),
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
            'to'        => DemandeAppro::MAIL_ATELIER,
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
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($data[0]->getNumeroDemandeAppro());
        $dals = $this->daLRepository->findBy(['numeroDemandeAppro' => $data[0]->getNumeroDemandeAppro(), 'numeroVersion' => $numeroVersionMax], ['numeroLigne' => 'ASC']);

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

    private function modificationStatutDal(string $numDa): void
    {
        $numeroVersionMax = $this->daLRepository->getNumeroVersionMax($numDa);
        $dals = $this->daLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        foreach ($dals as  $dal) {
            $dal->setStatutDal(DemandeAppro::STATUT_SOUMIS_ATE);
            self::$em->persist($dal);
        }

        self::$em->flush();
    }

    private function modificationStatutDa(string $numDa): void
    {
        $da = $this->daRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        $da->setStatutDal(DemandeAppro::STATUT_SOUMIS_ATE);

        self::$em->persist($da);
        self::$em->flush();
    }
}
