<?php

namespace App\Controller\da\ListeDa;

use App\Entity\da\DaAfficher;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Controller\Traits\da\DaListeTrait;
use App\Controller\Traits\da\StatutBcTrait;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use App\Form\da\HistoriqueModifDaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaListeTrait;
    use StatutBcTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaListeTrait(self::$generator);
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $historiqueModifDA = new DaHistoriqueDemandeModifDA();
        $numDaNonDeverrouillees = $this->historiqueModifDARepository->findNumDaOfNonDeverrouillees();

        //formulaire de recherche
        $form = self::$validator->createBuilder(DaSearchType::class, null, ['method' => 'GET'])->getForm();

        // Formulaire de l'historique de modification des DA
        $formHistorique = self::$validator->createBuilder(HistoriqueModifDaType::class, $historiqueModifDA)->getForm();

        $this->traitementFormulaireDeverouillage($formHistorique, $request); // traitement du formulaire de déverrouillage de la DA
        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        $this->sessionService->set('criteria_for_excel', $criteria);

        // Donnée à envoyer à la vue
        $data = $this->getData($criteria);
        $dataPrepared = $this->prepareDataForDisplay($data, $numDaNonDeverrouillees);
        self::$twig->display('da/list-da.html.twig', [
            'data'                   => $dataPrepared,
            'form'                   => $form->createView(),
            'formHistorique'         => $formHistorique->createView(),
            'serviceAtelier'         => $this->estUserDansServiceAtelier(),
            'serviceAppro'           => $this->estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
        ]);
    }

    public function getData(array $criteria): array
    {
        //recuperation de l'id de l'agence de l'utilisateur connecter
        $userConnecter = $this->getUser();
        $codeAgence = $userConnecter->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findIdByCodeAgence($codeAgence);
        /** @var array $daAffichers Filtrage des DA en fonction des critères */
        $daAffichers = $this->daAfficherRepository->findDerniereVersionDesDA($userConnecter, $criteria, $idAgenceUser, $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->estAdmin());

        // mise à jours des donner dans la base de donner
        $this->quelqueModifictionDansDatabase($daAffichers);

        // Vérification du verrouillage des DA et Retourne les DA filtrées
        return $this->appliquerVerrouillageSelonProfil(
            $daAffichers,
            $this->estAdmin(),
            $this->estUserDansServiceAppro(),
            $this->estUserDansServiceAtelier()
        );
    }


    /**
     * Applique le verrouillage ou déverrouillage des DA en fonction du profil utilisateur
     * 
     * @param iterable<DaAfficher> $daAffichers
     * @param bool $estAdmin
     * @param bool $estAppro
     * @param bool $estAtelier
     * 
     * @return iterable<DaAfficher>
     */
    private function appliquerVerrouillageSelonProfil(
        iterable $daAffichers,
        bool $estAdmin,
        bool $estAppro,
        bool $estAtelier
    ): iterable {
        foreach ($daAffichers as $daAfficher) {
            $this->determinerEtatVerrouillage($daAfficher, $estAdmin, $estAppro, $estAtelier);
        }
        return $daAffichers;
    }

    /**
     * Détermine si une DA doit être verrouillée ou non selon son statut et le profil utilisateur
     * 
     * @param DaAfficher $daAfficher
     * @param bool $estAdmin
     * @param bool $estAppro
     * @param bool $estAtelier
     */
    private function determinerEtatVerrouillage(
        DaAfficher $daAfficher,
        bool $estAdmin,
        bool $estAppro,
        bool $estAtelier
    ): void {
        $statutDa = $daAfficher->getStatutDal();
        $statutBc = $daAfficher->getStatutCde();

        $verrouille = true; // verrouillage par défaut

        $statutsDeverouillageAppro = [
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_SOUMIS_ATE,
        ];

        $statutsDeverouillageAtelier = [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_EN_COURS_CREATION,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
        ];

        // Déverrouillage selon le profil et les statuts
        if ($estAdmin) {
            $verrouille = false;
        } elseif ($estAppro && (in_array($statutDa, $statutsDeverouillageAppro) ||
            ($statutDa === DemandeAppro::STATUT_VALIDE && $statutBc === DaSoumissionBc::STATUT_REFUSE))) {
            $verrouille = false;
        } elseif ($estAtelier && in_array($statutDa, $statutsDeverouillageAtelier)) {
            $verrouille = false;
        }

        $daAfficher->setVerouille($verrouille);
    }

    private function quelqueModifictionDansDatabase(array $datas)
    {
        foreach ($datas as $data) {
            $this->modificationDateRestant($data);
            // $this->modificationStatutDa($data);
            $this->modificationStatutBC($data);
        }
        self::$em->flush();
    }

    /** 
     * Permet de calculer le nombre de jours restants pour chaque DAL
     */
    private function modificationDateRestant(DaAfficher $data): void
    {
        $this->ajoutNbrJourRestant($data);
        self::$em->persist($data);
    }

    /**
     * ctee methode parmer de mettre à jour le statut de la DA
     *
     * @return void
     */
    private function modificationStatutDa(DaAfficher $data)
    {
        $statutDa = $this->demandeApproRepository->getStatutDa($data->getNumeroDemandeAppro());
        $data->setStatutDal($statutDa);
        self::$em->persist($data);
    }


    /**
     * Cette methode permet de modifier le statut du BC
     *
     * @return void
     */
    private function modificationStatutBC(DaAfficher $data)
    {
        $statutBC = $this->statutBc($data->getArtRefp(), $data->getNumeroDemandeDit(), $data->getNumeroDemandeAppro(), $data->getArtDesi(), $data->getNumeroOr());
        $data->setStatutCde($statutBC);
        self::$em->persist($data);
    }

    private function traitementFormulaireDeverouillage($form, $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $idDa = $form->get('idDa')->getData();

            /** @var DemandeAppro $demandeAppro */
            $demandeAppro = $this->demandeApproRepository->find($idDa);

            $historiqueModifDA = $this->historiqueModifDARepository->findOneBy(['demandeAppro' => $demandeAppro]);

            if ($historiqueModifDA) {
                $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Echec de la demande: une demande de déverouillage a déjà été envoyé sur cette DA.']);
                return $this->redirectToRoute('list_da');
            } else {
                /** @var DaHistoriqueDemandeModifDA $historiqueModifDA */
                $historiqueModifDA = $form->getData();
                $historiqueModifDA
                    ->setNumDa($demandeAppro->getNumeroDemandeAppro())
                    ->setDemandeAppro($demandeAppro)
                ;

                self::$em->persist($historiqueModifDA);
                self::$em->flush();

                // todo: envoyer un mail aux appro pour les informer de la demande de déverrouillage
                // $this->envoyerMailAuxAppro([
                //     'idDa'          => $idDa,
                //     'numDa'         => $demandeAppro->getNumeroDemandeAppro(),
                //     'motif'         => $historiqueModifDA->getMotif(),
                //     'userConnecter' => $this->getUser()->getNomUtilisateur(),
                // ]);

                $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La demande de déverrouillage a été envoyée avec succès.']);
                return $this->redirectToRoute('list_da');
            }
        }
    }
}
