<?php

namespace App\Controller\da\ListeDa;

use App\Model\da\DaModel;
use App\Entity\admin\Agence;
use App\Entity\da\DaAfficher;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Controller\Traits\da\DaListeTrait;
use App\Controller\Traits\da\StatutBcTrait;
use App\Entity\admin\utilisateur\Role;
use App\Repository\admin\AgenceRepository;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\da\DaAfficherRepository;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use App\Form\da\HistoriqueModifDaType;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\da\DaSoumissionBcRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\da\DaHistoriqueDemandeModifDARepository;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaListeTrait;
    use StatutBcTrait;

    private DaAfficherRepository $daAfficherRepository;
    private DaHistoriqueDemandeModifDARepository $historiqueModifDARepository;
    private AgenceRepository $agenceRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DaModel $daModel;
    private DemandeApproRepository $daRepository;
    private DaSoumissionBcRepository $daSoumissionBcRepository;

    public function __construct()
    {
        parent::__construct();
        $this->daAfficherRepository = self::$em->getRepository(DaAfficher::class);
        $this->historiqueModifDARepository = self::$em->getRepository(DaHistoriqueDemandeModifDA::class);
        $this->agenceRepository = self::$em->getRepository(Agence::class);
        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
        $this->daModel = new DaModel();
        $this->daRepository = self::$em->getRepository(DemandeAppro::class);
        $this->daSoumissionBcRepository = self::$em->getRepository(DaSoumissionBc::class);
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
        $form = self::$validator->createBuilder(DaSearchType::class, null, [
            'method' => 'GET',
        ])->getForm();

        // Formulaire de l'historique de modification des DA
        $formHistorique = self::$validator->createBuilder(HistoriqueModifDaType::class, $historiqueModifDA)->getForm();
        $this->traitementFormulaireDeverouillage($formHistorique, $request); // traitement du formulaire de déverrouillage de la DA

        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        // Donnée à envoyer à la vue
        $data = $this->getData($criteria);

        self::$twig->display('da/list_da.html.twig', [
            'data'                   => $data,
            'form'                   => $form->createView(),
            'formHistorique'         => $formHistorique->createView(),
            'serviceAtelier'         => Controller::estUserDansServiceAtelier(),
            'serviceAppro'           => Controller::estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
        ]);
    }

    public function getData(array $criteria): array
    {
        //recuperation de l'id de l'agence de l'utilisateur connecter
        $codeAgence = Controller::getUser()->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findOneBy(['codeAgence' => $codeAgence])->getId();

        /** @var array $daAffichers Filtrage des DA en fonction des critères */
        $daAffichers = $this->daAfficherRepository->findDerniereVersionDesDA($criteria, $idAgenceUser);

        // mise à jours des donner dans la base de donner
        $this->quelqueModifictionDansDatabase($daAffichers);

        // Vérification du verrouillage des DA
        $daAffichers = $this->verouillerOuNonLesDa($daAffichers);



        // Retourne les DA filtrées
        return $daAffichers;
    }


    /** 
     * Vérifie si la DA doit être verrouillée ou non pour chaque DA filtrée
     * @param array $dasFiltered
     * @return array
     */
    private function verouillerOuNonLesDa($daAffichers)
    {
        foreach ($daAffichers as $daAfficher) {
            $this->estVerouillerOuNon($daAfficher);
        }
        return $daAffichers;
    }

    /** 
     * Vérifie si la DA doit être verrouillée ou non en fonction de son statut et du service de l'utilisateur
     */
    private function estVerouillerOuNon($daAfficher)
    {
        $statutDa = $daAfficher->getStatutDal(); // Récupération du statut de la DA
        $statutBc = $daAfficher->getStatutCde(); // Récupération du statut du BC

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
        $daAfficher->setVerouille($verouiller);
    }

    private function quelqueModifictionDansDatabase(array $datas)
    {
        foreach ($datas as $data) {
            $this->modificationDateRestant($data);
            $this->modificationStatutDa($data);
            // $this->modificationStatutBC($data);
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
        $statutDa = $this->daRepository->getStatutDa($data->getNumeroDemandeAppro());
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
            $demandeAppro = $this->daRepository->find($idDa);

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
                //     'userConnecter' => Controller::getUser()->getNomUtilisateur(),
                // ]);

                $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La demande de déverrouillage a été envoyée avec succès.']);
                return $this->redirectToRoute('list_da');
            }
        }
    }
}
