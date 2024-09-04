<?php

namespace App\Controller\badm;

use App\Entity\badm\Badm;
use App\Form\BadmForm2Type;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use Illuminate\Support\Facades\Request;
use App\Entity\admin\badm\TypeMouvement;
use App\Controller\Traits\FormatageTrait;
use App\Service\genererPdf\GenererPdfBadm;
use App\Controller\Traits\BadmDuplicationTrait;
use Symfony\Component\Routing\Annotation\Route;

class BadmDupliController extends Controller
{
    use FormatageTrait;
    use BadmDuplicationTrait;

    /**
     * @Route("/dupliBADM/{numBadm}/{id}", name="BadmDupli_dupliBadm")
     */
    public function dupliBadm($numBadm, $id, Request $request)
    {
        $badm = new Badm();

        $dataDb = self::$em->getRepository(Badm::class)->find($id);
        

        $data = $this->badm->findAll($dataDb->getIdMateriel(),'','');
       
        /** INITIALISATION du formulaire 2*/
       $badm = $this->initialisation($badm, $dataDb->getTypeMouvement(), $data, self::$em);

       //création du formulaire
       $form = self::$validator->createBuilder(BadmForm2Type::class, $badm)->getForm();


       $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                
                $badm->setTypeMouvement(self::$em->getRepository(TypeMouvement::class)->find($badm->getTypeMouvement()));
                //recuperatin de l'id du type de mouvemnet choisi par l'utilisateur dans le formulaire 1
                $idTypeMouvement = $badm->getTypeMouvement()->getId();


                //condition
                $coditionAgenceService = $badm->getAgenceEmetteur() === $badm->getAgence() && $badm->getServiceEmetteur() === $badm->getService();
                $conditionAgenceServices = $badm->getAgence() === null && $badm->getService() === null || $coditionAgenceService;
                $conditionVide = $badm->getAgence() === null && $badm->getService() === null && $badm->getCasierDestinataire() === null && $badm->getDateMiseLocation() === null;
                $idMateriel = (int)$data[0]['num_matricule'];
                $idMateriels = self::$em->getRepository(Badm::class)->findIdMateriel();

            
                if (($idTypeMouvement === 1 || $idTypeMouvement === 2) && $conditionVide) {
                    $message = 'compléter tous les champs obligatoires';
                    $this->notification($message);
                } 
                elseif ($idTypeMouvement === 1 && in_array($idMateriel, $idMateriels)) {
                    $message = 'ce matériel est déjà en PARC';
                    $this->notification($message);
                } 
                elseif ($idTypeMouvement === 2 && $coditionAgenceService) {
                    $message = 'le choix du type devrait être Changement de Casier';
                    $this->notification($message);
                } elseif ($idTypeMouvement === 2 && $conditionAgenceServices) {
                    $message = 'le choix du type devrait être Changement de Casier';
                    $this->notification($message);
                } else {

                    $this->ajoutDesDonnnerFormulaire($data, self::$em, $badm, $form, $idTypeMouvement);
                

                    //recuperation des ordres de réparation
                    $orDb = $this->badm->recupeOr((int)$data[0]['num_matricule']);
                    $OR = $this->ouiNonOr($orDb);
                    $orDb = $this->miseEnformeOrDb($orDb);
                     
                    //envoie des pièce jointe dans une dossier et le fusionner
                    $this->envoiePieceJoint($form, $badm);

                    $generPdfBadm = $this->genereteTabPdf($OR, $data, $badm, $form, self::$em, $idTypeMouvement);

                    $idAgenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($badm->getAgenceEmetteur(), 0, 2)]);
                    $idServiceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => substr($badm->getServiceEmetteur(), 0, 3)]);
                   
                    $badm
                    ->setAgenceEmetteurId($idAgenceEmetteur)
                    ->setServiceEmetteurId($idServiceEmetteur)
                    ->setAgenceDebiteurId($badm->getAgence())
                    ->setServiceDebiteurId($badm->getService())
                    ;
                    //ENVOIE DANS LE BASE DE DONNEE
                    self::$em->persist($badm);
                    self::$em->flush();

                    /** CREATION PDF */
                    $createPdf = new GenererPdfBadm();
                    $createPdf->genererPdfBadm($generPdfBadm, $orDb);
                    $createPdf->copyInterneToDOXCUWARE($badm->getNumBadm(), substr($badm->getAgenceEmetteur(),0,2) . substr($badm->getServiceEmetteur(),0,3));
                
                    //RECUPERATION de la dernière NumeroDemandeIntervention 
                    $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'BDM']);
                    $application->setDerniereId($badm->getNumBadm());
                    // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
                    self::$em->persist($application);
                    self::$em->flush();
                    
                    $this->sessionService->set('notification',['type' => 'success', 'message' => 'Votre demande a été enregistrer']);
                    $this->redirectToRoute("badmListe_AffichageListeBadm");
                }
                
            }
            
        self::$twig->display(
            'badm/duplication.html.twig',
            [
                'items' => $data,
                'form1Data' => $dataDb->getTypeMouvement(),
                'form' => $form->createView()
            ]
        );
    }
}
