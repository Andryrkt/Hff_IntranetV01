<?php

namespace App\Controller;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\AgenceServiceDefautSociete;
use App\Entity\admin\utilisateur\Profil;
use App\Form\ChoixSocieteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ChoixSocieteController extends Controller
{
    /**
     * @Route("/choix-societe", name="choix_societe")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $profils = $this->getUser()->getProfils();

        $societes = [];
        /** @var Profil $profil */
        foreach ($profils as $profil) {
            $societe = $profil->getSociete();
            if ($societe && !isset($societes[$societe->getId()])) $societes[$societe->getCodeSociete()] = $societe;
        }

        $form = $this->getFormFactory()->createBuilder(ChoixSocieteType::class, NULL, [
            'societes' => array_values($societes),
            'profils'  => $profils,
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $codeSociete = $data['societe'];

            $userId = $this->getUserId();
            /** @var AgenceServiceDefautSociete $agenceServiceDefaut */
            $agenceServiceDefaut = $this->getEntityManager()->getRepository(AgenceServiceDefautSociete::class)->findOneBy(['user' => $userId, 'codeSociete' => $codeSociete]);
            $societe = $societes[$codeSociete];

            if (!$societe) {
                $this->getSessionService()->set('notification', ['type' => 'error', 'message' => "La société sélectionnée portant le code société \"$codeSociete\" n’existe pas."]);
            } else {
                $libelleSociete = $societe->getNom();
                if (empty($agenceServiceDefaut)) {
                    $this->getSessionService()->set('notification', ['type' => 'error', 'message' => "Impossible de se connecter au compte affilié à la société \"$libelleSociete\". Aucun agence et service par défaut n’est défini pour la société \"$libelleSociete\"."]);
                } else {
                    $userInfo = $this->getSessionService()->get('user_info');
                    $userInfo["default_agence_code"]  = $agenceServiceDefaut->getCodeAgence();
                    $userInfo["default_service_code"] = $agenceServiceDefaut->getCodeService();
                    $userInfo["default_agence_id"]    = $agenceServiceDefaut->getAgence()->getId();
                    $userInfo["default_service_id"]   = $agenceServiceDefaut->getService()->getId();
                    $userInfo['societe_code']         = $data['societe'];
                    $userInfo['profil_id']            = $data['profil'];
                    $this->getSessionService()->set('user_info', $userInfo);
                    return $this->redirectToRoute('profil_acceuil');
                }
            }
        }

        return $this->render('choix_societe.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
