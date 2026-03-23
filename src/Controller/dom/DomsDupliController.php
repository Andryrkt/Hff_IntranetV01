<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\dom\DomsDupliTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\dom\DomModel;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomsDupliController extends Controller
{
    use FormatageTrait;
    use DomsDupliTrait;

    private $DomModel;

    public function __construct()
    {
        parent::__construct();
        $this->DomModel = new DomModel();
        $this->initDomsDupliTrait();
    }


    /**
     * @Route("/duplication/{id}", name="dom_dupli_form")
     */
    public function secondForm(Request $request, $id)
    {
        $dom = new Dom();
        /** INITIALISATION des données  */
        //recupération des données qui vient du formulaire 1
        // $form1Data = $this->getSessionService()->get('form1Data', []);
        // $this->initialisationSecondForm($form1Data, $this->getEntityManager(), $dom);

        $dom = $this->getEntityManager()->getRepository(Dom::class)->find($id);
        // dd($dom);
        $criteria = $this->criteria($form1Data, $this->getEntityManager());

        $is_temporaire = $form1Data['salarier'];

        $form = $this->getFormFactory()->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $domForm = $form->getData();

            $this->enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, $this->getEntityManager());


            $verificationDateExistant = $this->verifierSiDateExistant($dom->getMatricule(),  $dom->getDateDebut(), $dom->getDateFin());

            if ($form1Data['salarier'] === "PERMANANT") {
                if ($form1Data['sousTypeDocument']->getCodeSousType() !== 'COMPLEMENT') {
                    if ($form1Data['sousTypeDocument']->getCodeSousType()  === 'FRAIS EXCEPTIONNEL') {
                        if ($verificationDateExistant) {
                            $message = $dom->getMatricule() . ' ' . $dom->getNom() . ' ' . $dom->getPrenom() . " a déja une mission enregistrée sur ces dates, vérifier SVP!";
                            $this->notification($message);
                        } else {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager());
                        }
                    }

                    if ($verificationDateExistant) {
                        $message = $dom->getMatricule() . ' ' . $dom->getNom() . ' ' . $dom->getPrenom() . "  a déja une mission enregistrée sur ces dates, vérifier SVP!";

                        $this->notification($message);
                    } else {
                        if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager());
                        } else {
                            $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                            $this->notification($message);
                        }
                    }
                } else {
                    if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {

                        $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager());
                    } else {
                        $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                        $this->notification($message);
                    }
                }
            } else {

                if ($form1Data['sousTypeDocument'] !== 'COMPLEMENT') {

                    if ($form1Data['sousTypeDocument'] === 'FRAIS EXCEPTIONNEL' && $dom->getDevis() !== 'MGA') {
                        if ($verificationDateExistant) {
                            $message = $dom->getMatricule() . ' ' . $dom->getNom() . ' ' . $dom->getPrenom() . "  a déja une mission enregistrée sur ces dates, vérifier SVP!";
                            $this->notification($message);
                        } else {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager());
                        }
                    }

                    if ($verificationDateExistant) {
                        $message = $dom->getMatricule() . ' ' . $dom->getNom() . ' ' . $dom->getPrenom() . "  a déja une mission enregistrée sur ces dates, vérifier SVP!";
                        $this->notification($message);
                    } else {
                        if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {
                            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager());
                        } else {
                            $message = "Assurez vous que le Montant Total est inférieur à 500.000";
                            $this->notification($message);
                        }
                    }
                } else {

                    if ($dom->getModePayement() !== 'MOBILE MONEY' || ($dom->getModePayement() === 'MOBILE MONEY' && $dom->getTotalGeneralPayer() <= 500000)) {
                        $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, $this->getEntityManager());
                    } else {
                        $message = "Assurer que le Montant Total est supérieur ou égale à 500.000";

                        $this->notification($message);
                    }
                }
            }

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('domList_ShowListDomRecherche');
        }

        return $this->render('doms/dupli.html.twig', [
            'form' => $form->createView(),
            'is_temporaire' => $is_temporaire,
            'criteria' => $criteria
        ]);
    }


    private function notification($message)
    {
        $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dom_first_form");
    }
}
