<?php

namespace App\Controller\badm;

use App\Entity\Badm;
use App\Form\BadmForm1Type;
use App\Entity\TypeMouvement;
use App\Controller\Controller;
use App\Controller\Traits\BadmsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BadmsController extends Controller
{
    use BadmsTrait;

    /**
     * @Route("/badm-form1", name="badms_newForm1")
     *
     * @return void
     */
    public function newForm1(Request $request)
    {
        $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            /**
             * INITIALISATION
             */
            $badm = new Badm();
            $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        

            $badm
            ->setAgenceEmetteur($CodeServiceofCours[0]['agence_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']))
            ->setServiceEmetteur($CodeServiceofCours[0]['service_ips'] . ' ' . strtoupper($CodeServiceofCours[0]['nom_agence_i100']))
          ;

          $form = self::$validator->createBuilder(BadmForm1Type::class, $badm)->getForm();


          $form->handleRequest($request);
            
        
          if($form->isSubmitted() && $form->isValid())
          {
            //recuperation de l'id du type de mouvement
            $idTypeMouvement = $badm->getTypeMouvement()->getId();

            //recuperation des information du materiel dans la base de donnée informix
            $data = $this->badm->findAll($badm->getIdMateriel(),  $badm->getNumParc(), $badm->getNumSerie());

            //recuperation du materiel dan sl abase de donner sqlserver
            $materiel = self::$em->getRepository(Badm::class)->findOneBy(['idMateriel' => $data[0]['num_matricule'] ], ['numBadm' => 'DESC']);
     
            
            //si le materiel n'est pas encore dans la base de donner on donne la valeur 0 pour l'idType ld emouvmentMateriel
            $idTypeMouvementMateriel = $materiel === null ? 0 : $materiel->getTypeMouvement()->getId();

            $conditionTypeMouvStatut = $idTypeMouvement === $idTypeMouvementMateriel && in_array($materiel->getStatutDemande()->getId(), [15, 16, 21, 46, 23, 25, 29, 30]);
            $conditionEntreeParc = $idTypeMouvement === 1 && $data[0]['code_affect'] !== 'VTE';
            $conditionChangementAgServ_1 = $idTypeMouvement === 2 && $data[0]['code_affect'] === 'VTE';
            $conditionChangementAgServ_2 = $idTypeMouvement === 2 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
            $conditionCessionActif = $idTypeMouvement === 4 && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM';
            $conditionMiseAuRebut = $idTypeMouvement === 5 && $data[0]['code_affect'] === 'CAS';
           
            
            if ($badm->getIdMateriel() === null &&  $badm->getNumParc() === null && $badm->getNumSerie() === null) {
                $message = " Renseigner l'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->alertRedirection($message);
            } elseif (empty($data)) {
                $message = "Matériel déjà vendu";
                $this->alertRedirection($message);
            } elseif ($conditionEntreeParc) {
                $message = 'Ce matériel est déjà en PARC';
                $this->alertRedirection($message);
            } elseif ($conditionChangementAgServ_1) {
                $message = "L\'agence et le service associés à ce matériel ne peuvent pas être modifiés.";
                $this->alertRedirection($message);
            } elseif ($conditionChangementAgServ_2) {
                $message = " l\'affectation matériel ne permet pas cette opération";
                $this->alertRedirection($message);
            } elseif ($conditionCessionActif) {
                $message = "Cession d\'actif ";
                $this->alertRedirection($message);
            } elseif ($conditionMiseAuRebut) {
                $message = 'Ce matériel ne peut pas être mis au rebut';
                $this->alertRedirection($message);
            } elseif ($conditionTypeMouvStatut) {
                $message = 'ce matériel est encours de traitement pour ce type de mouvement ';
                $this->alertRedirection($message);
            } else {
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
                
                $this->redirectToRoute("badms_newForm2");
            }
          }
        $agenceAutoriser = $this->badm->recupeSessionAutoriser($_SESSION['user']);
            if (empty($agenceAutoriser)) {
                $message = "verifiez votre Autorisation";
                $this->alertRedirection($message);
            } else {
                self::$twig->display(
                    'badm/firstForm.html.twig',
                    [
                        'infoUserCours' => $infoUserCours,
                        'boolean' => $boolean,
                        'form' => $form->createView()
                    ]
                );
            }
    }
}