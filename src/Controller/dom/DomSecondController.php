<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
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
class DomSecondController extends Controller
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
     * @Route("/dom-second-form", name="dom_second_form")
     */
    public function secondForm(Request $request)
    {
        //recuperation de l'utilisateur connecter
        $user = $this->getUser();

        $dom = new Dom();
        //recupération des données qui vient du formulaire 1
        $form1Data = $this->getSessionService()->get('form1Data', []);
        $codeSousTypeDoc = $form1Data['sousTypeDocument']->getCodeSousType();

        /** INITIALISATION des données  */
        $this->initialisationSecondForm($form1Data, $this->getEntityManager(), $dom);
        $criteria = $this->criteria($form1Data, $this->getEntityManager());

        $is_temporaire = $form1Data['salarier'];


        $form = $this->getFormFactory()->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $domForm = $form->getData();

            $this->enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, $this->getEntityManager(), $user);

            $verificationDateExistant = $this->verifierSiDateExistant($dom->getMatricule(),  $dom->getDateDebut(), $dom->getDateFin());

            if ($codeSousTypeDoc !== 'COMPLEMENT' && $codeSousTypeDoc !== 'TROP PERCU') {
                if ($verificationDateExistant) {
                    $message = $dom->getMatricule() . ' ' . $dom->getNom() . ' ' . $dom->getPrenom() . " a déja une mission enregistrée sur ces dates, vérifier SVP!";
                    $this->historiqueOperation->sendNotificationCreation($message, $dom->getNumeroOrdreMission(), 'dom_first_form');
                } else {
                    if ($codeSousTypeDoc  === 'FRAIS EXCEPTIONNEL') {
                        $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager(), $this->fusionPdf, $user);
                    } else {
                        if ((explode(':', $dom->getModePayement())[0] !== 'MOBILE MONEY' || (explode(':', $dom->getModePayement())[0] === 'MOBILE MONEY')) && (int)str_replace('.', '', $dom->getTotalGeneralPayer()) <= 500000) {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager(), $this->fusionPdf, $user);
                        } else {
                            $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                            $this->historiqueOperation->sendNotificationCreation($message, $dom->getNumeroOrdreMission(), 'dom_first_form');
                        }
                    }
                }
            } else {
                if ((explode(':', $dom->getModePayement())[0] !== 'MOBILE MONEY' || (explode(':', $dom->getModePayement())[0] === 'MOBILE MONEY')) && (int)str_replace('.', '', $dom->getTotalGeneralPayer()) <= 500000) {
                    $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager(), $this->fusionPdf, $user);
                } else {
                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                    $this->historiqueOperation->sendNotificationCreation($message, $dom->getNumeroOrdreMission(), 'dom_first_form');
                }
            }

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistré', $dom->getNumeroOrdreMission(), 'doms_liste', true);
        }

        $this->logUserVisit('dom_second_form'); // historisation du page visité par l'utilisateur

        return $this->render('doms/secondForm.html.twig', [
            'form'          => $form->createView(),
            'is_temporaire' => $is_temporaire,
            'criteria'      => $criteria,
            'codeSousTypeDoc'   => $codeSousTypeDoc
        ]);
    }
}
