<?php

namespace App\Controller\dit;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Form\dit\DitValidationType;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitValidationController extends Controller
{

    /**
     * @Route("/ditValidation/{id<\d+>}/{numDit<\w+>}", name="dit_validationDit")
     *
     * @return void
     */
    public function validationDit($numDit, $id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();


        /** CREATION D'AUTORISATION */
        $userId = $this->sessionService->get('user_id');
        $userConnecter = self::$em->getRepository(User::class)->find($userId);
        $roleNames = [];
        foreach ($userConnecter->getRoles() as $role) {
            $roleNames[] = $role->getRoleName();
        }
        $autoriser = in_array('ADMINISTRATEUR', $roleNames) || in_array('VALIDATEUR', $roleNames);
        //FIN AUTORISATION


        $dit = self::$em->getRepository(DemandeIntervention::class)->find($id);

        $data = $this->ditModel->findAll($dit->getIdMateriel(), $dit->getNumParc(), $dit->getNumSerie());

        $dit->setNumParc($data[0]['num_parc']);
        $dit->setNumSerie($data[0]['num_serie']);
        $dit->setIdMateriel($data[0]['num_matricule']);
        $dit->setConstructeur($data[0]['constructeur']);
        $dit->setModele($data[0]['modele']);
        $dit->setDesignation($data[0]['designation']);
        $dit->setCasier($data[0]['casier_emetteur']);
        //Bilan financière
        $dit->setCoutAcquisition($data[0]['prix_achat']);
        $dit->setAmortissement($data[0]['amortissement']);
        $dit->setChiffreAffaire($data[0]['chiffreaffaires']);
        $dit->setChargeEntretient($data[0]['chargeentretien']);
        $dit->setChargeLocative($data[0]['chargelocative']);
        $dit->setResultatExploitation($data[0]['chiffreaffaires'] - ($data[0]['chargeentretien'] + $data[0]['chargelocative']));
        $dit->setValeurNetComptable($data[0]['prix_achat'] - $data[0]['amortissement']);
        //Etat machine
        $dit->setKm($data[0]['km']);
        $dit->setHeure($data[0]['heure']);

        if ($dit->getInternetExterne() === 'I') {
            $dit->setInternetExterne('INTERNE');
        } elseif ($dit->getInternetExterne() === 'E') {
            $dit->setInternetExterne('EXTERNE');
        }

        $form = self::$validator->createBuilder(DitValidationType::class, $dit)->getForm();

        // $form->handleRequest($request);

        // // Vérifier si le formulaire est soumis et valide
        // if ($form->isSubmitted() && $form->isValid()) {

        //     $email = new EmailService();
        //     $dit = $form->getData();

        //     $userDemandeur = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $dit->getUtilisateurDemandeur()]);
        //     dump($userDemandeur);
        //         $userDemandeur = $this->arrayToObjet($userDemandeur);
        //         dump($userDemandeur);
        //         $emailSuperieurs = $this->recupMailSuperieur($userDemandeur);
        //         dump($emailSuperieurs);
        //         $id = $this->sessionService->get('user_id');
        //         dump($id);
        //         $userConnecter = self::$em->getRepository(User::class)->find($id);
        //         dump($userDemandeur);
        //     if ($request->request->has('refuser')) {

        //         $variableEmail = $this->donnerRefu($userDemandeur, $emailSuperieurs, $userConnecter, $dit);

        //         $content = $this->emailRefu($variableEmail);

        //         $this->confirmationEmail($email, $content);

        //     } elseif ($request->request->has('valider')) {


        //         $statutDemande = self::$em->getRepository(StatutDemande::class)->find(51);
        //         $dit
        //         ->setIdStatutDemande($statutDemande)
        //         ->setDateValidation(new \DateTime($this->getDatesystem()))
        //         ->setHeureValidation($this->getTime())
        //         ;
        //         self::$em->flush();

        //         dd($dit);

        //         if($dit->getDemandeDevis() === "OUI") {

        //         $variableEmail = $this->donnerValideAvecDevis($userDemandeur, $emailSuperieurs, $userConnecter, $dit);

        //         $content = $this->emailValideAvecDevis($variableEmail);

        //         $this->confirmationEmail($email, $content);
        //         } else {
        //         $content = $this->emailValideSansDevis();

        //         $this->confirmationEmail($email, $content);
        //         }
        //     }

        //     $this->redirectToRoute("dit_index");
        // }

        // dd($dit);
        //RECUPERATION DE LISTE COMMANDE 
        $commandes = $this->ditModel->RecupereCommandeOr($dit->getNumeroOR());

        $this->logUserVisit('dit_validationDit', [
            'id'     => $id,
            'numDit' => $numDit,
        ]); // historisation du page visité par l'utilisateur       

        self::$twig->display('dit/validation.html.twig', [
            'form' => $form->createView(),
            'dit' => $dit,
            'autoriser' => $autoriser,
            'commandes' => $commandes
        ]);
    }

    private function recupMailSuperieur(User $userDemandeur): array
    {
        $emailSuperieurs = [];
        foreach ($userDemandeur->getSuperieurs() as $value) {
            if (empty($value)) {
                return [];
            } else {

                $emailSuperieurs[] = $value->getMail();
            }
        }
        return $emailSuperieurs;
    }

    private function donnerRefu($userDemandeur, $emailSuperieurs, $userConnecter, $dit): array
    {
        return [
            'emailUserDemandeur' => $userDemandeur->getMail(),
            'emailSuperieurs' => $emailSuperieurs,
            'template' => 'dit/emailRefu.html.twig',
            'numDit' => $dit->getNumeroDemandeIntervention(),
            'id' => $dit->getId(),
            'observation' => $dit->getObservationDirectionTechnique(),
            'nomPrenom' => $userConnecter->getPersonnels()->getNom() . ' ' . $userConnecter->getPersonnels()->getPrenoms()
        ];
    }

    private function donnerValideAvecDevis($userDemandeur, $emailSuperieurs, $userConnecter, $dit): array
    {
        return [
            'emailUserDemandeur' => $userDemandeur->getMail(),
            'emailSuperieurs' => $emailSuperieurs,
            'template' => 'dit/emailRefu.html.twig',
            'numDit' => $dit->getNumeroDemandeIntervention(),
            'id' => $dit->getId(),
            'observation' => $dit->getObservationDirectionTechnique(),
            'nomPrenom' => $userConnecter->getPersonnels()->getNom() . ' ' . $userConnecter->getPersonnels()->getPrenoms()
        ];
    }

    private function emailRefu($tab): array
    {

        return [
            'to' => $tab['emailUserDemandeur'],
            'cc' => $tab['emailSuperieurs'],
            'template' => $tab['template'],
            'variables' => [
                'subject' => " DEMANDE D'INTERVENTION REFUSE ({$tab['numDit']})",
                'message' => "La demande d'intervention {$tab['numDit']} à été réfusée par {$tab['nomPrenom']}. ",
                'observation' => $tab['observation'],
                'action_url' => "http://172.20.11.32/Hffintranet/ditValidation/{$tab['id']}/{$tab['numDit']}"
            ]
        ];
    }

    private function emailValideAvecDevis($tab)
    {
        return [
            'to' => $tab['emailUserDemandeur'],
            'cc' => $tab['emailSuperieurs'],
            'template' => $tab['template'],
            'variables' => [
                'subject' => " DEMANDE D'INTERVENTION VALIDE ({$tab['numDit']})",
                'message' => "La demande d'intervention {$tab['numDit']} à été validée par {$tab['nomPrenom']}. En attente de devis.",
                'observation' => $tab['observation'],
                'action_url' => "http://172.20.11.32/Hffintranet/ditValidation/{$tab['id']}/{$tab['numDit']}"
            ]
        ];
    }

    private function emailValideSansDevis()
    {
        return [
            'to' => 'hasina.andrianadison@hff.mg',
            'template' => 'dit/email_template.html.twig',
            'variables' => [
                'subject' => 'Your Subject Here',
                'message' => 'This is the body of your email.',
                'action_url' => 'https://example.com/action'
            ]
        ];
    }

    private function confirmationEmail($email, array $content)
    {
        if ($email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables'])) {
            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Une email a été envoyé au demandeur ']);
        } else {
            $this->sessionService->set('notification', ['type' => 'danger', 'message' => "l'email n'a pas été envoyé au demandeur"]);
        }
    }
}
