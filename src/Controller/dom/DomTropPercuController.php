<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Entity\dom\Domtp;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Entity\admin\utilisateur\User;
use App\Form\dom\DomTropPercuFormType;
use App\Controller\Traits\dom\DomsTrait;
use App\Controller\Traits\FormatageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationDOMService;
use App\Model\dom\DomModel;
use App\Service\FusionPdf;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomTropPercuController extends Controller
{
    use FormatageTrait;
    use DomsTrait;
    private $historiqueOperation;
    private $DomModel;
    private $fusionPdf;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDOMService($this->getEntityManager());
        $this->DomModel = new DomModel();
        $this->fusionPdf = new FusionPdf();
    }
    /**
     * @Route("/trop-percu/{id}", name="dom_trop_percu_form")
     */
    public function secondForm(Request $request, $id)
    {
        //recuperation de l'utilisateur connecter
        $user = $this->getUser();

        $dom = new Dom;
        /** 
         * @var Dom $oldDom
         */
        $oldDom = $this->getEntityManager()->getRepository(Dom::class)->find($id);

        $this->statutTropPercu($oldDom);

        if (!$this->DomModel->verifierSiTropPercu($oldDom->getNumeroOrdreMission())) {
            $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => 'Erreur : On ne peut pas créer de DOM trop perçu à partir de ce DOM à cause des dates.']);
            $this->redirectToRoute("doms_liste");
        } elseif (!$oldDom->getStatutTropPercuOk()) {
            $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => 'Erreur : On ne peut pas créer de DOM trop perçu à partir de ce DOM à cause de son statut.']);
            $this->redirectToRoute("doms_liste");
        }

        $this->initialisationFormTropPercu($this->getEntityManager(), $dom, $oldDom, $user);
        $categoryId = $dom->getCategoryId();
        $criteria = [
            'oldDateDebut' => $oldDom->getDateDebut()->format('m/d/Y'),  // formater en mois/jour/année pour faciliter le traitement en JS
            'oldDateFin' => $oldDom->getDateFin()->format('m/d/Y'),  // formater en mois/jour/année pour faciliter le traitement en JS
            'oldNombreJour' => $oldDom->getNombreJour(),
            'nombreJourTropPercu' => $this->DomModel->getNombreJourTropPercu($oldDom->getNumeroOrdreMission()),
            'totalGeneral' => $oldDom->getTotalGeneralPayer(),
        ];

        $form = $this->getFormFactory()->createBuilder(DomTropPercuFormType::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $domForm = $form->getData();

            $mode = $form->get('mode')->getData();
            $dom
                ->setHeureDebut($dom->getHeureDebut()->format('H:i'))
                ->setHeureFin($dom->getHeureFin()->format('H:i'))
                ->setModePayement(':' . $mode)
                ->setCategoryId($categoryId)
            ;

            $domTp = new Domtp;
            $domTp
                ->setNumeroOrdreMission($oldDom->getNumeroOrdreMission())
                ->setNumeroOrdreMissionTp($dom->getNumeroOrdreMission())
                ->setNombreJourTp($dom->getNombreJour())
            ;
            $this->getEntityManager()->persist($domTp);
            $this->getEntityManager()->flush();

            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager(), $this->fusionPdf, $user, true);

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistré', $dom->getNumeroOrdreMission(), 'doms_liste', true);
        }

        // $this->logUserVisit('dom_second_form'); // historisation du page visité par l'utilisateur

        return $this->render('doms/tropPercuForm.html.twig', [
            'form'          => $form->createView(),
            'is_temporaire' => 'PERMANENT',
            'criteria'      => $criteria
        ]);
    }
}
