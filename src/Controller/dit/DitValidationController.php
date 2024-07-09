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
    $autoriser = in_array('ADMINISTRATEUR', $roleNames);
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
        if ($request->request->has('refuser')) {

            dd('refu');
           // Définir l'expéditeur
            // $email->setFrom('hasimanjaka.ratompoarinandro@hff.mg', 'Different Sender');
            
            $to = 'hasina.andrianadison@hff.mg';
            $subject = 'Sujet de l\'email';
            $body = 'Ceci est le <b>contenu</b> de l\'email.';
            $altBody = 'Ceci est le contenu de l\'email en texte brut.';
    
            if ($email->sendEmail($to, $subject, $body, $altBody)) {
                dd( 'Email envoyé avec succès');
            } else {
                dd( 'L\'envoi de l\'email a échoué' );
            }
           
        } elseif ($request->request->has('valider')) {
           $dit = $form->getData();
           $statutDemande = self::$em->getRepository(StatutDemande::class)->find(52);
           $dit->setIdStatutDemande($statutDemande);
           self::$em->flush();
           $this->redirectToRoute("dit_index");
        }

        dd('Okey');
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
}