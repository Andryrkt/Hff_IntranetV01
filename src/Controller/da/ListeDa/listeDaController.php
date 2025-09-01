<?php

namespace App\Controller\da\ListeDa;

use App\Entity\da\DaAfficher;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\admin\Application;
use App\Entity\da\DaSoumissionBc;
use App\Form\da\HistoriqueModifDaType;
use App\Controller\Traits\da\DaListeTrait;
use App\Controller\Traits\da\StatutBcTrait;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\da\DaHistoriqueDemandeModifDA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends BaseController
{
    use DaListeTrait;
    use StatutBcTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager($this->getEntityManager());
        $this->initDaListeTrait(self::$generator);
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        $historiqueModifDA = new DaHistoriqueDemandeModifDA();
        $numDaNonDeverrouillees = $this->historiqueModifDARepository->findNumDaOfNonDeverrouillees();

        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, null, ['method' => 'GET'])->getForm();

        // Formulaire de l'historique de modification des DA
        $formHistorique = $this->getFormFactory()->createBuilder(HistoriqueModifDaType::class, $historiqueModifDA)->getForm();

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
        $this->getTwig()->render('da/list-da.html.twig', [
            'data'                   => $dataPrepared,
            'form'                   => $form->createView(),
            'formHistorique'         => $formHistorique->createView(),
            'serviceAtelier'         => $this->estUserDansServiceAtelier(),
            'serviceAppro'           => $this->estUserDansServiceAppro(),
            'numDaNonDeverrouillees' => $numDaNonDeverrouillees,
        ]);
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

                $this->getEntityManager()->persist($historiqueModifDA);
                $this->getEntityManager()->flush();

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
