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
            $this->sessionService->set('criteria_for_excel', $criteria);
        }

        // Donnée à envoyer à la vue
        $data = $this->getData($criteria, $fonctions);
        $dataPrepared = $this->prepareDataForDisplay($data, $numDaNonDeverrouillees);

        self::$twig->display('da/list_da.html.twig', [
            'data'                   => $dataPrepared,
            'form'                   => $form->createView(),
            'formHistorique'         => $formHistorique->createView(),
            'serviceAtelier'         => $this->estUserDansServiceAtelier(),
            'serviceAppro'           => $this->estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
            'fonctions'              => $fonctions,
        ]);
    }

    public function getData(array $criteria, &$fonctions): array
    {
        //recuperation de l'id de l'agence de l'utilisateur connecter
        $userConnecter = $this->getUser();
        $codeAgence = $userConnecter->getCodeAgenceUser();
        $idAgenceUser = $this->agenceRepository->findIdByCodeAgence($codeAgence);
        /** @var array $daAffichers Filtrage des DA en fonction des critères */
        $daAffichers = $this->daAfficherRepository->findDerniereVersionDesDA($userConnecter, $criteria, $idAgenceUser, $this->estUserDansServiceAppro(), $this->estUserDansServiceAtelier(), $this->estAdmin());

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

        $estAppro = $this->estUserDansServiceAppro();
        $estAtelier = $this->estUserDansServiceAtelier();
        $estAdmin = $this->estAdmin();
        $verouiller = false; // initialisation de la variable de verrouillage à false (déverouillée par défaut)

        $statutDaVerouillerAppro = [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_A_VALIDE_DW];
        $statutDaVerouillerAtelier = [DemandeAppro::STATUT_TERMINER, DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_SOUMIS_APPRO, DemandeAppro::STATUT_A_VALIDE_DW];

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
