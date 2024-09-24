<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitOrsSoumisAValidationController extends Controller
{
    use FormatageTrait;
    
    /**
     * @Route("/insertion-or/{numDit}", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr(Request $request, $numDit)
    {
        $ditInsertionOrSoumis = new DitOrsSoumisAValidation();
        $ditInsertionOrSoumis->setNumeroDit($numDit);

        
    

        $form = self::$validator->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOrSoumis)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {   
            $numOrBaseDonner = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $numDit])->getNumeroOr();

            // if($numOrBaseDonner !== $ditInsertionOr->getNumeroOR()){
            //     $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
            //     $this->notification($message);
            // } else {
            $numeroVersionMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findNumeroVersionMax();
            $orSoumisValidationModel = $this->ditModel->recupOrSoumisValidation();
            $OrSoumisAvant = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvant('16403951');

            
                // $ditInsertionOrSoumis->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                //     ->setHeureSoumission($this->getTime())
                //     ->setDateSoumission(new \DateTime($this->getDatesystem()))
                // ;

                $totalRecapOr = [
                    'montant_itv' => 0,
                    'montant_piece' => 0,
                    'montant_mo' => 0,
                    'montant_achats_locaux' => 0,
                    'montant_frais_divers' => 0,
                    'montant_lubrifiants' => 0,
                ];
                $orSoumisValidataion = []; // Tableau pour stocker les objets

                foreach ($orSoumisValidationModel as $orSoumis) {
                    // Instancier une nouvelle entité pour chaque entrée du tableau
                    $ditInsertionOr = new DitOrsSoumisAValidation(); 
                    
                    $ditInsertionOr->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                                ->setHeureSoumission($this->getTime())
                                ->setDateSoumission(new \DateTime($this->getDatesystem()))
                                ->setNumeroItv($orSoumis['numero_itv'])
                                ->setNombreLigneItv($orSoumis['nombre_ligne'])
                                ->setMontantItv($orSoumis['montant_itv'])
                                ->setMontantPiece($orSoumis['montant_piece'])
                                ->setMontantMo($orSoumis['montant_mo'])
                                ->setMontantAchatLocaux($orSoumis['montant_achats_locaux'])
                                ->setMontantFraisDivers($orSoumis['montant_divers'])
                                ->setMontantLubrifiants($orSoumis['montant_lubrifiants'])
                                ->setLibellelItv($orSoumis['libelle_itv']);
                    
                    $orSoumisValidataion[] = $ditInsertionOr; // Ajouter l'objet dans le tableau
                
                    // Faire la somme des montants et les stocker dans le tableau
                    $totalRecapOr['montant_itv'] += $orSoumis['montant_itv'];
                    $totalRecapOr['montant_piece'] += $orSoumis['montant_piece'];
                    $totalRecapOr['montant_mo'] += $orSoumis['montant_mo'];
                    $totalRecapOr['montant_achats_locaux'] += $orSoumis['montant_achats_locaux'];
                    $totalRecapOr['montant_frais_divers'] += $orSoumis['montant_divers'];
                    $totalRecapOr['montant_lubrifiants'] += $orSoumis['montant_lubrifiants'];
                }

                
                
                $totalRecepAvantApres = [
                    'totalNbLigAv' => 0,
                    'totalNbLigAp' => 0,
                    'totalMttTotalAv' => 0,
                    'totalMttTotalAp' => 0
                ];
                $recapAvantApres = [];
                foreach ($orSoumisValidataion as  $orSoumis) {
                    for ($i=0; $i < count($orSoumisValidataion) ; $i++) { 
                        if(!empty($OrSoumisAvant)){
                            $recapAvantApres['itv'] = $orSoumis[$i]->getNumeroItv();
                            $recapAvantApres['libelleItv'] = $orSoumis[$i]->getLibellelItv();
                            $recapAvantApres['nbLigAv'] = $OrSoumisAvant[$i]->getNombreLigneItv();
                            $recapAvantApres['nbLigAp'] = $orSoumis[$i]->getNombreLigneItv();
                            $recapAvantApres['mttTotalAv'] = $OrSoumisAvant[$i]->getMontantItv();
                            $recapAvantApres['mttTotalAp'] = $orSoumis[$i]->getMontantItv();
                        } else {
                            $recapAvantApres['itv'] = $orSoumis[$i]->getNumeroItv();
                            $recapAvantApres['libelleItv'] = $orSoumis[$i]->getLibellelItv();
                            $recapAvantApres['nbLigAv'] = '';
                            $recapAvantApres['nbLigAp'] = $orSoumis[$i]->getNombreLigneItv();
                            $recapAvantApres['mttTotalAv'] = '';
                            $recapAvantApres['mttTotalAp'] = $orSoumis[$i]->getMontantItv();
                        }
                    }
                        if($recapAvantApres['nbLigAv'] === $recapAvantApres['nbLigAp'] && $recapAvantApres['mttTotalAv'] === $recapAvantApres['mttTotalAp']){
                            $recapAvantApres['statut'] = '';
                        } elseif (($recapAvantApres['nbLigAv'] !== $recapAvantApres['nbLigAp'] || $recapAvantApres['mttTotalAv'] !== $recapAvantApres['mttTotalAp']) && ($recapAvantApres['nbLigAv'] !== '' || $recapAvantApres['nbLigAp'] !== '')) {
                            $recapAvantApres['statut'] = 'Modif';
                        } elseif ($recapAvantApres['nbLigAv'] === '' && $recapAvantApres['mttTotalAv'] === '') {
                            $recapAvantApres['statut'] = 'Nouv';
                        } elseif( ($recapAvantApres['nbLigAv'] !== '' && $recapAvantApres['mttTotalAv'] !== '') && ($recapAvantApres['nbLigAp'] === '' &&  $recapAvantApres['mttTotalAp'] === '')) {
                            $recapAvantApres['statut'] = 'Supp';
                        }
                        
                }
dd($recapAvantApres);



                
                
                foreach ($recapAvantApres as  $value) {
                    $totalRecepAvantApres['totalNbLigAv'] += 0;
                    $totalRecepAvantApres['totalNbLigAp'] += 0;
                    $totalRecepAvantApres['totalMttTotalAv'] += 0;
                    $totalRecepAvantApres['totalMttTotalAp'] += 0;
                }

                $recapOr = [];
                foreach ($orSoumisValidataion as  $orSoumis) {
                    $recapOr['itv'] = $orSoumis['numero_itv'];
                    $recapOr['mttTotal'] = $this->formatNumber($orSoumis['montant_itv']);
                    $recapOr['mttPièces'] = $orSoumis['montant_piece'];
                    $recapOr['mttMo'] = $orSoumis['montant_mo'];
                    $recapOr['mttSt'] = $orSoumis['montant_achats_locaux'];
                    $recapOr['mttLub'] = $orSoumis['montant_lubrifiants'];
                    $recapOr['mttAutres'] = $orSoumis['montant_divers'];
                }

               
                

                $genererPdfDit = new GenererPdfOrSoumisAValidation();
                $genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOrSoumis);
                $genererPdfDit->copyToDw($ditInsertionOrSoumis->getNumeroVersion());
                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOrSoumis, $this->fusionPdf);

            
                foreach ($orSoumisValidataion as $entity) {
                    self::$em->persist($entity); // Persister chaque entité individuellement
                }
            

                self::$em->flush();

                $this->sessionService->set('notification',['type' => 'success', 'message' => 'l\'Or est bien validé']);
                $this->redirectToRoute("dit_index");
            //}
        }


        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * TRAITEMENT DES FICHIER UPLOAD
     *(copier le fichier uploder dans une repertoire et le donner un nom)
     */
    private function uplodeFile($form, $ditInsertionOr, $nomFichier, &$pdfFiles)
    {
        
        /** @var UploadedFile $file*/
        $file = $form->get($nomFichier)->getData();
        $fileName = 'oRValidation_' .$ditInsertionOr->getNumeroVersion(). '_01.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/fichier/';
      
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }


    }

    private function envoiePieceJoint($form, $ditInsertionOr, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i=1; $i < 2; $i++) { 
        $nom = "pieceJoint0{$i}";
        if($form->get($nom)->getData() !== null){
                $this->uplodeFile($form, $ditInsertionOr, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' . $ditInsertionOr->getNumeroVersion(). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' . $ditInsertionOr->getNumeroVersion(). '.pdf';

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    private function notification($message)
    {
        $this->sessionService->set('notification',['type' => 'danger', 'message' => $message]);
        $this->redirectToRoute("dit_index");
    }

    private function autoIncrement($num)
    {
        if($num === null){
            $num = 0;
        }
        return $num + 1;
    }
}
