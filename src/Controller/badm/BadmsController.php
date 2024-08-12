<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Entity\User;
use App\Entity\Agence;
use App\Entity\Service;
use App\Form\BadmForm1Type;
use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BadmsController extends Controller
{
    
    /**
     * @Route("/badm-form1", name="badms_newForm1")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
            /** RECUPERATION ID USER CONNECTER */
            $userId = $this->sessionService->get('user_id');
            /** INITIALISATION*/
            $badm = new Badm();
            $agenceServiceIps= $this->agenceServiceIpsString();
        

            $badm
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
          ;

          $form = self::$validator->createBuilder(BadmForm1Type::class, $badm)->getForm();


          $form->handleRequest($request);
            
        
          if($form->isSubmitted() && $form->isValid())
          {
            if ($badm->getTypeMouvement() === null) {
                throw new \Exception('choisir une type de mouvement');
            }

            if ($badm->getIdMateriel() === null &&  $badm->getNumParc() === null && $badm->getNumSerie() === null) 
            {
                $message = " Renseigner l'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->notification($message);
            } else {
                //recuperation de l'id du type de mouvement
                $idTypeMouvement = $badm->getTypeMouvement()->getId();

                //recuperation des information du materiel dans la base de donnée informix
                $data = $this->badm->findAll($badm->getIdMateriel(),  $badm->getNumParc(), $badm->getNumSerie());

                if (empty($data)) {
                    $message = "Matériel déjà vendu ou L'information saisie n'est pas correcte.";
                    $this->notification($message);
                } else {
                    //recuperation du materiel dan sl abase de donner sqlserver
                 $materiel = self::$em->getRepository(Badm::class)->findOneBy(['idMateriel' => $data[0]['num_matricule'] ], ['numBadm' => 'DESC']);

                 //si le materiel n'est pas encore dans la base de donner on donne la valeur 0 pour l'idType ld emouvmentMateriel
                 $idTypeMouvementMateriel = $materiel === null ? 0 : $materiel->getTypeMouvement()->getId();
 
                 //recuperati
                 $user = self::$em->getRepository(User::class)->find($userId);
         
                 $agenceMaterielId = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => $data[0]["agence"]])->getId();

                 if ($data[0]["code_service"] === null || $data[0]["code_service"] === '' || $data[0]["code_service"] === null) {
                     $serviceMaterilId =  self::$em->getRepository(Service::class)->findOneBy(['codeService' => 'COM'])->getId();
                 } else {
                     $serviceMaterilId =  self::$em->getRepository(Service::class)->findOneBy(['codeService' => $data[0]["code_service"]])->getId();
                 }
                 //condition de blocage
                 $conditionTypeMouvStatut = $idTypeMouvement === $idTypeMouvementMateriel && in_array($materiel->getStatutDemande()->getId(), [15, 16, 21, 46, 23, 25, 29, 30]);
                 $conditionEntreeParc = $idTypeMouvement === 1 && $data[0]['code_affect'] !== 'VTE';
                 $conditionChangementAgServ_1 = $idTypeMouvement === 2 && $data[0]['code_affect'] === 'VTE';
                 $conditionChangementAgServ_2 = $idTypeMouvement === 2 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
                 $conditionCessionActif = $idTypeMouvement === 4 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
                 $conditionMiseAuRebut = $idTypeMouvement === 5 && $data[0]['code_affect'] === 'CAS';
                 $conditionRoleUtilisateur = in_array(1, $user->getRoleIds());
                 $conditionAgenceServiceAutoriser = in_array($agenceMaterielId, $user->getAgenceAutoriserIds()) && in_array($serviceMaterilId, $user->getServiceAutoriserIds());
                }
            }
            
           
            if ($conditionEntreeParc) {
                $message = 'Ce matériel est déjà en PARC';
                $this->notification($message);
            } 
            elseif ($conditionChangementAgServ_1) {
                $message = "L'agence et le service associés à ce matériel ne peuvent pas être modifiés.";
                $this->notification($message);
            } elseif ($conditionChangementAgServ_2) {
                $message = " l'affectation matériel ne permet pas cette opération";
                $this->notification($message);
            } elseif ($conditionCessionActif) {
                $message = "Ce matériel ne peut pas mise en cession d'actif ";
                $this->notification($message);
            } elseif ($conditionMiseAuRebut) {
                $message = 'Ce matériel ne peut pas être mis au rebut';
                $this->notification($message);
            } 
            elseif ($conditionTypeMouvStatut) {
                $message = 'ce matériel est encours de traitement pour ce type de mouvement ';
                $this->notification($message);
            } 
            else {

                $badm 
                ->setIdMateriel($data[0]['num_matricule']) 
                ->setNumParc($data[0]['num_parc'])
                ->setNumSerie($data[0]['num_serie'])
                ;

                $formData = [
                    'idMateriel' => $badm->getIdMateriel(),
                    'numParc' => $badm->getNumParc(),
                    'numSerie' => $badm->getNumSerie(),
                    'typeMouvemnt' => $badm->getTypeMouvement()
                ];
                //envoie des donner dan la session
                $this->sessionService->set('badmform1Data', $formData);
                if($conditionRoleUtilisateur){
                    $this->redirectToRoute("badms_newForm2");
                } elseif (!$conditionAgenceServiceAutoriser) {
                    $message = " vous n'êtes pas autoriser à consulter ce matériel";
                    $this->notification($message);
                } else {
                    $this->redirectToRoute("badms_newForm2");
                }
               
            }
          }

        
        self::$twig->display(
            'badm/firstForm.html.twig',
            [
                'form' => $form->createView()
            ]
        );
            
    }

    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("badms_newForm1");
    }
}