<?php

namespace App\Controller\dom;


use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Controller\Traits\dom\DomsTrait;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Entity\dom\Domtp;
use App\Form\dom\DomTropPercuFormType;
use App\Service\historiqueOperation\HistoriqueOperationDOMService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DomTropPercuController extends Controller
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
     * @Route("/dom-trop-percu-form/{id}", name="dom_trop_percu_form")
     */
    public function secondForm(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recuperation de l'utilisateur connecter
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        $dom = new Dom;
        $oldDom = self::$em->getRepository(Dom::class)->find($id);

        $this->initialisationFormTropPercu(self::$em, $dom, $oldDom);
        $criteria = [
            'oldDateDebut' => $oldDom->getDateDebut()->format('m/d/Y'),  // formater en mois/jour/année pour faciliter le traitement en JS
            'oldDateFin' => $oldDom->getDateFin()->format('m/d/Y'),  // formater en mois/jour/année pour faciliter le traitement en JS
            'oldNombreJour' => $oldDom->getNombreJour(),
            'nombreJourTropPercu' => $this->DomModel->getNombreJourTropPercu($oldDom->getNumeroOrdreMission()),
        ];

        $form = self::$validator->createBuilder(DomTropPercuFormType::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $domForm = $form->getData();

            $mode = $form->get('mode')->getData();
            $dom
                ->setHeureDebut($dom->getHeureDebut()->format('H:i'))
                ->setHeureFin($dom->getHeureFin()->format('H:i'))
                ->setModePayement(':' . $mode)
            ;

            $domTp = new Domtp;
            $domTp
                ->setNumeroOrdreMission($oldDom->getNumeroOrdreMission())
                ->setNumeroOrdreMissionTp($dom->getNumeroOrdreMission())
                ->setNombreJourTp($dom->getNombreJour())
            ;
            self::$em->persist($domTp);
            self::$em->flush();

            $this->recupAppEnvoiDbEtPdf($dom, $domForm, $form, self::$em, $this->fusionPdf, $user, true);

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistré', $dom->getNumeroOrdreMission(), 'doms_liste', true);
        }

        // $this->logUserVisit('dom_second_form'); // historisation du page visité par l'utilisateur

        self::$twig->display('doms/tropPercuForm.html.twig', [
            'form'          => $form->createView(),
            'is_temporaire' => 'PERMANENT',
            'criteria'      => $criteria
        ]);
    }
}
