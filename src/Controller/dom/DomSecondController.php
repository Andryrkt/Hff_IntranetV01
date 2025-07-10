<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Controller\Traits\dom\DomsTrait;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Service\historiqueOperation\HistoriqueOperationDOMService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DomSecondController extends Controller
{
    use FormatageTrait;
    use DomsTrait;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDOMService;
    }
    /**
     * @Route("/dom-second-form", name="dom_second_form")
     */
    public function secondForm(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recuperation de l'utilisateur connecter
        $user = $this->getUser();

        $dom = new Dom();
        /** INITIALISATION des données  */
        //recupération des données qui vient du formulaire 1
        $form1Data = $this->sessionService->get('form1Data', []);

        $this->initialisationSecondForm($form1Data, self::$em, $dom);
        $criteria = $this->criteria($form1Data, self::$em);

        $is_temporaire = $form1Data['salarier'];


        $form = self::$validator->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $domForm = $form->getData();

            $this->enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, self::$em, $user);

            $verificationDateExistant = $this->verifierSiDateExistant($dom->getMatricule(),  $dom->getDateDebut(), $dom->getDateFin());

            if ($form1Data['sousTypeDocument']->getCodeSousType() !== 'COMPLEMENT' && $form1Data['sousTypeDocument']->getCodeSousType() !== 'TROP PERCU') {
                if ($verificationDateExistant) {
                    $message = $dom->getMatricule() . ' ' . $dom->getNom() . ' ' . $dom->getPrenom() . " a déja une mission enregistrée sur ces dates, vérifier SVP!";
                    $this->historiqueOperation->sendNotificationCreation($message, $dom->getNumeroOrdreMission(), 'dom_first_form');
                } else {
                    if ($form1Data['sousTypeDocument']->getCodeSousType()  === 'FRAIS EXCEPTIONNEL') {
                        $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em, $this->fusionPdf, $user);
                    } else {
                        if ((explode(':', $dom->getModePayement())[0] !== 'MOBILE MONEY' || (explode(':', $dom->getModePayement())[0] === 'MOBILE MONEY')) && (int)str_replace('.', '', $dom->getTotalGeneralPayer()) <= 500000) {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em, $this->fusionPdf, $user);
                        } else {
                            $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                            $this->historiqueOperation->sendNotificationCreation($message, $dom->getNumeroOrdreMission(), 'dom_first_form');
                        }
                    }
                }
            } else {
                if ((explode(':', $dom->getModePayement())[0] !== 'MOBILE MONEY' || (explode(':', $dom->getModePayement())[0] === 'MOBILE MONEY')) && (int)str_replace('.', '', $dom->getTotalGeneralPayer()) <= 500000) {
                    $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em, $this->fusionPdf, $user);
                } else {
                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                    $this->historiqueOperation->sendNotificationCreation($message, $dom->getNumeroOrdreMission(), 'dom_first_form');
                }
            }

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistré', $dom->getNumeroOrdreMission(), 'doms_liste', true);
        }

        $this->logUserVisit('dom_second_form'); // historisation du page visité par l'utilisateur

        self::$twig->display('doms/secondForm.html.twig', [
            'form'          => $form->createView(),
            'is_temporaire' => $is_temporaire,
            'criteria'      => $criteria
        ]);
    }
}
