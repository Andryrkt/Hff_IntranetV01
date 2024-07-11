<?php

namespace App\Controller\dit;

use App\Entity\User;
use App\Entity\StatutDemande;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Form\DitValidationType;
use App\Entity\DemandeIntervention;
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
    
    $this->SessionStart();
    $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    $text = file_get_contents($fichier);
    $boolean = strpos($text, $_SESSION['user']);

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
            //Etat machine
            $dit->setKm($data[0]['km']);
            $dit->setHeure($data[0]['heure']);

            if($dit->getInternetExterne() === 'I'){
                $dit->setInternetExterne('INTERNE');
            } elseif($dit->getInternetExterne() === 'E') {
                $dit->setInternetExterne('EXTERNE');
            }
    
    $form = self::$validator->createBuilder(DitValidationType::class, $dit)->getForm();

    $form->handleRequest($request);

    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {

        $email = new EmailService();
        $dit = $form->getData();
        $userDemandeur = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $dit->getUtilisateurDemandeur()]);
            $userDemandeur = $this->arrayToObjet($userDemandeur);
            $emailSuperieurs = $this->recupMailSuperieur($userDemandeur);
            $userConnecter = self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'));
        if ($request->request->has('refuser')) {

            $variableEmail = [
                'emailUserDemandeur' => $userDemandeur->getMail(),
                'emailSuperieurs' => $emailSuperieurs,
                'template' => 'dit/emailRefu.html.twig',
                'numDit' => $dit->getNumeroDemandeIntervention(),
                'id' => $dit->getId(),
                'observation' => $dit->getObservationDirectionTechnique(),
                'nomPrenom' => $userConnecter->getPersonnels()->getNom() . ' ' . $userConnecter->getPersonnels()->getPrenoms()
            ];

            $content = $this->emailRefu($variableEmail);
    
            $this->confirmationEmail($email, $content);
           
        } elseif ($request->request->has('valider')) {
            
           
           $statutDemande = self::$em->getRepository(StatutDemande::class)->find(51);
           $dit
            ->setIdStatutDemande($statutDemande)
            ->setDateValidation(new \DateTime($this->getDatesystem()))
            ->setHeureValidation($this->getTime())
           ;
           self::$em->flush();

           if($dit->getDemandeDevis() === "OUI") {
            $content = $this->emailValideAvecDevis();
    
            $this->confirmationEmail($email, $content);
           } else {
            $content = $this->emailValideSansDevis();
    
            $this->confirmationEmail($email, $content);
           }
        }

        $this->redirectToRoute("dit_index");
        
    }
        

    self::$twig->display('dit/validation.html.twig', [
        'form' => $form->createView(),
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'dit' => $dit,
        'autoriser' => $autoriser
    ]);
   }

   private function recupMailSuperieur(User $userDemandeur): array
   {
    $emailSuperieurs = [];
           foreach ($userDemandeur->getSuperieurs() as $value) {
                $emailSuperieurs[] = $value->getMail();
           }
        return $emailSuperieurs;
   }

   private function emailRefu($tab): array
   {

    return [ 
        'to' => $tab['emailUserDemandeur'],
        'cc' => $tab['emailSuperieurs'],
        'template' => $tab['template'],
        'variables' => [
            'subject' => "LA DEMANDE D'INTERVENTION {$tab['numDit']} A ETE REFUSE",
            'message' => "La demande d'intervention {$tab['numDit']} à été réfusé par {$tab['nomPrenom']} \n en raison de {$tab['observation']}. \n Vous pouvez voir le detail en cliquant sur le bouton en bas.",
            'action_url' => "http://172.20.11.32/Hffintranet/ditValidation/{$tab['id']}/{$tab['numDit']}"
            ]
        ];
   }

   private function emailValideAvecDevis()
   {
    return [ 
        'to' => 'hasina.andrianadison@hff.mg',
        'template' => 'dit/email_template.html.twig',
        'variables' => [
            'subject' => 'Your Subject Here',
            'name' => 'Recipient Name',
            'message' => 'This is the body of your email.',
            'action_url' => 'https://example.com/action'
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
            'name' => 'Recipient Name',
            'message' => 'This is the body of your email.',
            'action_url' => 'https://example.com/action'
        ]
        ];
   }

   private function confirmationEmail( $email, array $content)
   {
    if ($email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables'])) {
        $this->sessionService->set('notification',['type' => 'success', 'message' => 'Une email a été envoyé au demandeur ']);
    } else {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => "l'email n'a pas été envoyé au demandeur"]);
    }
   }
}