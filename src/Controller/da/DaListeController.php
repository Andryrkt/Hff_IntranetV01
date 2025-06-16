<?php

namespace App\Controller\da;

use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Controller\Traits\da\DaTrait;
use App\Controller\Traits\lienGenerique;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Form\da\HistoriqueModifDaType;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Service\EmailService;
use Symfony\Component\Routing\Annotation\Route;

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

    public function __construct()
    {
        parent::__construct();

        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->ditRepository = self::$em->getRepository(DemandeIntervention::class);
        $this->daLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->dalrRepository = self::$em->getRepository(DemandeApproLR::class);
    }

    /**
     * @Route("/list", name="da_list")
     */
    public function listeDA(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $historiqueModifDA = new DaHistoriqueDemandeModifDA();

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

        $this->modificationIdDALsDansDALRs($dasFiltered);
        $this->modificationDateRestant($dasFiltered);

        $this->initialiserHistorique($historiqueModifDA);

        $formHistorique = self::$validator->createBuilder(HistoriqueModifDaType::class, $historiqueModifDA)->getForm();

        $this->traitementFormulaireDeverouillage($formHistorique, $request); // traitement du formulaire de déverrouillage de la DA

        self::$twig->display('da/list.html.twig', [
            'data' => $dasFiltered,
            'form' => $form->createView(),
            'formHistorique' => $formHistorique->createView(),
            'serviceAtelier' => $this->estUserDansServiceAtelier(),
            'serviceAppro' => $this->estUserDansServiceAppro(),
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
        if (!$demandeAppro) {
            $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'La demande d\'approvisionnement n\'existe pas.']);
            return $this->redirectToRoute('da_list');
        } else {
            // if ($demandeAppro->getEstVerrouillee() == false) {
            //     $this->sessionService->set('notification', ['type' => 'warning', 'message' => 'La demande d\'approvisionnement n\'est pas verrouillée.']);
            //     return $this->redirectToRoute('da_list');
            // }

            if (!$this->estUserDansServiceAppro()) {
                $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Vous n\'êtes pas autorisé à déverrouiller cette demande.']);
                return $this->redirectToRoute('da_list');
            }

            // $demandeAppro->setEstVerrouillee(false);
            // self::$em->persist($demandeAppro);
            // self::$em->flush();

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
            $historiqueModifDA = $form->getData();
            $idDa = $form->get('idDa')->getData();

            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $this->daRepository->find($idDa);

            /** @var DaHistoriqueDemandeModifDA $historiqueModifDA */
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
        }
    }


    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => 'nomenjanahary.randrianantenaina@hff.mg',
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
            'to'        => 'nomenjanahary.randrianantenaina@hff.mg',
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
}
