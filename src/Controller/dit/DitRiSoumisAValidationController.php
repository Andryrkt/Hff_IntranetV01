<?php

namespace App\Controller\dit;

ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitRiSoumisAValidation;
use App\Form\dit\DitRiSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Model\dit\DitRiSoumisAValidationModel;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\dit\DitHistoriqueOperationDocument;
use App\Controller\Traits\dit\DitRiSoumisAValidationTrait;
use App\Service\genererPdf\GenererPdfRiSoumisAValidataion;

class DitRiSoumisAValidationController extends Controller
{
    use DitRiSoumisAValidationTrait;

    /**
     * @Route("/insertion-ri/{numDit}", name="dit_insertion_ri")
     *
     * @return void
     */
    public function riSoumisAValidation(Request $request, $numDit)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $ditRiSoumisAValidationModel = new DitRiSoumisAValidationModel();
        $numOrBaseDonner = $ditRiSoumisAValidationModel->recupNumeroOr($numDit);
        if(empty($numOrBaseDonner)){
            $message = "Le DIT n'a pas encore du numéro OR";
            $this->notification($message);
        }
        $ditRiSoumiAValidation = new DitRiSoumisAValidation();
        $ditRiSoumiAValidation->setNumeroDit($numDit);
        $ditRiSoumiAValidation->setNumeroOR($numOrBaseDonner[0]['numor']);
        
        $itvDejaSoumis = $ditRiSoumisAValidationModel->findItvDejaSoumis($ditRiSoumiAValidation->getNumeroOR());
        $itvAfficher = $ditRiSoumisAValidationModel->recupInterventionOr($ditRiSoumiAValidation->getNumeroOR(), $itvDejaSoumis);

        $form = self::$validator->createBuilder(DitRiSoumisAValidationType::class, $ditRiSoumiAValidation, [
            'itvAfficher' => $itvAfficher
        ])->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted())
        { 
            $dataForm = $form->getData();
            $itvCoches = [];

            // Récupérer les valeurs des cases cochées
            for ($i = 0; $i < count($itvAfficher); $i++) {
                $checkboxFieldName = 'checkbox_' . $i;
                if ($form->has($checkboxFieldName) && $form->get($checkboxFieldName)->getData()) {
                    $itvCoches[] = (int)$itvAfficher[$i]['numeroitv'];
                }
            }
            $toutNumeroItv = $ditRiSoumisAValidationModel->recupNumeroItv($numOrBaseDonner[0]['numor']);
            
            $existe= false;
            $estSoumis = false;
            foreach ($itvCoches as $value) {
                if(in_array($value, $itvDejaSoumis)){
                    $estSoumis =true;
                    break;
                }
                if(!in_array($value, $toutNumeroItv)){
                    $existe = true;
                }
            }

            if($numOrBaseDonner[0]['numor'] !== $ditRiSoumiAValidation->getNumeroOR()){
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->notification($message);
            } elseif($estSoumis) {
                $message = "Erreur lors de la soumission, car certaines interventions ont déjà fait l'objet d'une soumission dans DocuWare.";
                $this->notification($message);
            } elseif ($existe) {
                $message = "Erreur lors de la soumission, car certaines interventions n'ont pas encore été validées dans DocuWare.";
                $this->notification($message);
            } else {
                
                $numeroSoumission = $ditRiSoumisAValidationModel->recupNumeroSoumission($dataForm->getNumeroOR());
                $ditRiSoumiAValidation
                    ->setNumeroDit($numDit)
                    ->setNumeroOR($dataForm->getNumeroOR())
                    ->setHeureSoumission($this->getTime())
                    ->setDateSoumission(new \DateTime($this->getDatesystem()))
                    ->setNumeroSoumission($numeroSoumission)
                ;

                $historique = new DitHistoriqueOperationDocument();
                $genererPdfRi = new GenererPdfRiSoumisAValidataion();
                

                // ENREGISTRE LE FICHIER
                    /** @var UploadedFile $file */
                    $file = $form->get("pieceJoint01")->getData();

                    foreach ($itvCoches as $value) {
                        if ($file) { // Vérification si le fichier existe
                            try {
                                $fileName = 'RI_' . $dataForm->getNumeroOR() . '-' . $value . '.' . $file->getClientOriginalExtension();
                                $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vri/';
                                
                                // Créer une copie temporaire du fichier
                                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                                copy($file->getPathname(), $tempFile);

                                // Déplacer le fichier depuis la copie temporaire
                                $targetPath = $fileDossier . $fileName;
                                if (!copy($tempFile, $targetPath)) {
                                    throw new \Exception('Erreur lors de la copie du fichier.');
                                }

                                // Supprimer la copie temporaire après l'utilisation
                                unlink($tempFile);
                            } catch (\Exception $e) {
                                // Gestion de l'erreur de déplacement
                                $message = 'Le fichier n\'a pas pu être téléchargé. Veuillez réessayer.';
                                $this->notification($message);
                            }
                        } else {
                            // Message si aucun fichier n'a été téléchargé
                            $message = 'Aucun fichier n\'a été sélectionné.';
                            $this->notification($message);
                        }
                    }

                foreach ($itvCoches as $value) {
                    $riSoumisAValidation = new DitRiSoumisAValidation();
                    $riSoumisAValidation
                        ->setNumeroDit($numDit)
                        ->setNumeroOR($dataForm->getNumeroOR())
                        ->setHeureSoumission($this->getTime())
                        ->setDateSoumission(new \DateTime($this->getDatesystem()))
                        ->setNumeroSoumission($numeroSoumission)
                        ->setNumeroItv((int)$value)
                    ;
                    // Persist les entités liées
                    self::$em->persist($riSoumisAValidation);

                    //HISOTRIQUE
                    $historique
                        ->setNumeroDocument('RI_'.$dataForm->getNumeroOR().'-'.$value)
                        ->setUtilisateur($this->nomUtilisateur(self::$em))
                        ->setIdTypeDocument(self::$em->getRepository(DitTypeDocument::class)->find(3))
                        ->setIdTypeOperation(self::$em->getRepository(DitTypeOperation::class)->find(2))
                        ;
                    self::$em->persist($historique); // Persist l'historique avec les entités liées


                    // Génération du PDF
                    $genererPdfRi->copyToDwRiSoumis($value, $riSoumisAValidation->getNumeroOR());
                }

                /** ENVOIE des DONNEE dans BASE DE DONNEE */
                // Flushe toutes les entités et l'historique
                self::$em->flush();

            
                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le rapport d\'intervention a été soumis avec succès']);
                $this->redirectToRoute("dit_index");
            }
        } 


        self::$twig->display('dit/DitRiSoumisAValidation.html.twig', [
            'form' => $form->createView(),
            'itvAfficher' => $itvAfficher
        ]);
    }
}