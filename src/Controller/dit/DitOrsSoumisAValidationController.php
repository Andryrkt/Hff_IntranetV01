<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\FormatageTrait;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Service\genererPdf\GenererPdfOrSoumisAValidation;

class DitOrsSoumisAValidationController extends Controller
{
    use FormatageTrait;

    private $magasinListOrLivrerModel;

    public function __construct()
    {
        parent::__construct();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
    }
    
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

            $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanning1($ditInsertionOrSoumis->getNumeroOR());
                $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanning2($ditInsertionOrSoumis->getNumeroOR());
            
                if(!empty($datePlannig1)){
                    $datePlanning = $datePlannig1[0]['dateplanning1'];
                } else if(!empty($datePlannig2)){
                    $datePlanning = $datePlannig2[0]['dateplanning2'];
                } else {
                    $datePlanning = '';
                }

            if($numOrBaseDonner !== $ditInsertionOrSoumis->getNumeroOR()){
                $message = "Le numéro Or que vous avez saisie ne correspond pas à la DIT";
                $this->notification($message);
            } elseif($datePlanning === '')
            {
                $message = "Le numéro Or doit avoir une date planning";
                $this->notification($message);
            } else {
                $numeroVersionMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findNumeroVersionMax($ditInsertionOrSoumis->getNumeroOR());
                $orSoumisValidationModel = $this->ditModel->recupOrSoumisValidation($ditInsertionOrSoumis->getNumeroOR());
                
                

                $ditInsertionOrSoumis
                                    ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                                    ->setHeureSoumission($this->getTime())
                                    ->setDateSoumission(new \DateTime($this->getDatesystem()))
                                    ;
                $orSoumisValidataion = $this->orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis);
                
                foreach ($orSoumisValidataion as $entity) {
                    self::$em->persist($entity); // Persister chaque entité individuellement
                }

                self::$em->flush();
                
                $OrSoumisAvant = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvant($ditInsertionOrSoumis->getNumeroOR());
                $OrSoumisAvantMax = self::$em->getRepository(DitOrsSoumisAValidation::class)->findOrSoumiAvantMax($ditInsertionOrSoumis->getNumeroOR());

                dump($OrSoumisAvant);
                dd($OrSoumisAvantMax);
                
                
                $montantPdf = $this->montantpdf($orSoumisValidataion, $OrSoumisAvant);
                
                $genererPdfDit = new GenererPdfOrSoumisAValidation();
                $genererPdfDit->GenererPdfOrSoumisAValidation($ditInsertionOrSoumis, $montantPdf);
                $genererPdfDit->copyToDw($ditInsertionOrSoumis->getNumeroVersion(), $ditInsertionOrSoumis->getNumeroOR());
                //envoie des pièce jointe dans une dossier et la fusionner
                $this->envoiePieceJoint($form, $ditInsertionOrSoumis, $this->fusionPdf);

                

                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Le document de controle a été généré et soumis pour validation']);
                $this->redirectToRoute("dit_index");
            }
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
        $fileName = 'oRValidation_' .$ditInsertionOr->getNumeroOR().'_'.$ditInsertionOr->getNumeroVersion(). '_01.' . $file->getClientOriginalExtension();
       
        $fileDossier = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/fichier/';
      
        $file->move($fileDossier, $fileName);

        if ($file->getClientOriginalExtension() === 'pdf') {
            $pdfFiles[] = $fileDossier.$fileName;
        }


    }

    private function envoiePieceJoint($form, $ditInsertionOr, $fusionPdf)
    {

        $pdfFiles = [];

        for ($i=1; $i < 5; $i++) { 
        $nom = "pieceJoint0{$i}";
        if($form->get($nom)->getData() !== null){
                $this->uplodeFile($form, $ditInsertionOr, $nom, $pdfFiles);
            }
        }
        //ajouter le nom du pdf crée par dit en avant du tableau
        array_unshift($pdfFiles, $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' .$ditInsertionOr->getNumeroOR().'_'. $ditInsertionOr->getNumeroVersion(). '.pdf');

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $_SERVER['DOCUMENT_ROOT'] . '/Upload/vor/oRValidation_' .$ditInsertionOr->getNumeroOR().'_'. $ditInsertionOr->getNumeroVersion(). '.pdf';

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

    public function calculeSommeMontant($orSoumisValidataion)
    {
        $totalRecapOr = [
            'total' => 'TOTAL',
            'montant_itv' => 0,
            'montant_piece' => 0,
            'montant_mo' => 0,
            'montant_achats_locaux' => 0,
            'montant_frais_divers' => 0,
            'montant_lubrifiants' => 0,
        ];
        foreach ($orSoumisValidataion as $orSoumis) {
            // Faire la somme des montants et les stocker dans le tableau
            $totalRecapOr['montant_itv'] += $orSoumis->getMontantItv();
            $totalRecapOr['montant_piece'] += $orSoumis->getMontantPiece();
            $totalRecapOr['montant_mo'] += $orSoumis->getMontantMo();
            $totalRecapOr['montant_achats_locaux'] += $orSoumis->getMontantAchatLocaux();
            $totalRecapOr['montant_frais_divers'] += $orSoumis->getMontantFraisDivers();
            $totalRecapOr['montant_lubrifiants'] += $orSoumis->getMontantLubrifiants();
        }

        return $totalRecapOr;
    }

    public function recuperationAvantApres($orSoumisValidataion, $OrSoumisAvant)
    {
        $recapAvantApres = [];

        
        for ($i = 0; $i < count($orSoumisValidataion); $i++) {
            // Vérification si l'index $i existe dans $OrSoumisAvant
            $nbLigAv = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getNombreLigneItv() : 0;
            $mttTotalAv = isset($OrSoumisAvant[$i]) ? $OrSoumisAvant[$i]->getMontantItv() : 0;

            $recapAvantApres[] = [
                'itv' => $orSoumisValidataion[$i]->getNumeroItv(),
                'libelleItv' => $orSoumisValidataion[$i]->getLibellelItv(),
                'nbLigAv' => $nbLigAv,
                'nbLigAp' => $orSoumisValidataion[$i]->getNombreLigneItv(),
                'mttTotalAv' => $mttTotalAv,
                'mttTotalAp' => $orSoumisValidataion[$i]->getMontantItv(),
            ];
        }
        return $recapAvantApres;
    }



    public function affectationStatut($recapAvantApres)
    {
        $nombreStatutNouvEtSupp = [
            'nbrNouv' => 0,
            'nbrSupp' => 0
        ];

        foreach ($recapAvantApres as &$value) { // Référence les éléments pour les modifier directement
            if ($value['nbLigAv'] === $value['nbLigAp'] && $value['mttTotalAv'] === $value['mttTotalAp']) {
                $value['statut'] = '';
            } elseif ($value['nbLigAv'] !== 0 && $value['mttTotalAv'] !== 0 && $value['nbLigAp'] === 0 && $value['mttTotalAp'] === 0) {
                $value['statut'] = 'Supp';
                $nombreStatutNouvEtSupp['nbrSupp']++;
            } elseif (($value['nbLigAv'] === 0 || $value['nbLigAv'] === '' ) && $value['mttTotalAv'] === 0) {
                $value['statut'] = 'Nouv';
                $nombreStatutNouvEtSupp['nbrNouv']++;
            } elseif (($value['nbLigAv'] !== $value['nbLigAp'] || $value['mttTotalAv'] !== $value['mttTotalAp']) && ($value['nbLigAv'] !== 0 || $value['nbLigAv'] !== '' || $value['nbLigAp'] !== 0)) {
                $value['statut'] = 'Modif';
            
            }
        }

        // Retourner le tableau modifié et les statistiques de nouveaux et supprimés
        return [
            'recapAvantApres' => $recapAvantApres,
            'nombreStatutNouvEtSupp' => $nombreStatutNouvEtSupp
        ];
    }


    public function calculeSommeAvantApres($recapAvantApres)
    {
        $totalRecepAvantApres = [
            'premierLigne' => '',
            'total' => 'TOTAL',
            'totalNbLigAv' => 0,
            'totalNbLigAp' => 0,
            'totalMttTotalAv' => 0,
            'totalMttTotalAp' => 0,
            'dernierLigne' => ''
        ];
        foreach ($recapAvantApres as  $value) {
            $totalRecepAvantApres['totalNbLigAv'] += $value['nbLigAv'] === '' ? 0 : $value['nbLigAv'];
            $totalRecepAvantApres['totalNbLigAp'] += $value['nbLigAp'];
            $totalRecepAvantApres['totalMttTotalAv'] += $value['mttTotalAv'] === '' ? 0 : $value['mttTotalAv'];
            $totalRecepAvantApres['totalMttTotalAp'] += $value['mttTotalAp'];
        }

        return $totalRecepAvantApres;
    }

    public function recapitulationOr($orSoumisValidataion)
    {
        $recapOr = [];
        foreach ($orSoumisValidataion as $orSoumis) {
            $recapOr[] = [
                'itv' => $orSoumis->getNumeroItv(),
                'mttTotal' => $orSoumis->getMontantItv(),
                'mttPieces' => $orSoumis->getMontantPiece(),
                'mttMo' => $orSoumis->getMontantMo(),
                'mttSt' => $orSoumis->getMontantAchatLocaux(),
                'mttLub' => $orSoumis->getMontantLubrifiants(),
                'mttAutres' => $orSoumis->getMontantFraisDivers(),
            ];
        }
        return $recapOr;
    }
    

    public function montantpdf($orSoumisValidataion, $OrSoumisAvant)
    {
        $recapAvantApres =$this->recuperationAvantApres($orSoumisValidataion, $OrSoumisAvant);
                return [
                    'avantApres' => $this->affectationStatut($recapAvantApres)['recapAvantApres'],
                    'totalAvantApres' => $this->calculeSommeAvantApres($recapAvantApres),
                    'recapOr' => $this->recapitulationOr($orSoumisValidataion),
                    'totalRecapOr' => $this->calculeSommeMontant($orSoumisValidataion),
                    'nombreStatutNouvEtSupp' => $this->affectationStatut($recapAvantApres)['nombreStatutNouvEtSupp']
                ];
    }

    public function orSoumisValidataion($orSoumisValidationModel, $numeroVersionMax, $ditInsertionOrSoumis)
    {
        $orSoumisValidataion = []; // Tableau pour stocker les objets

                foreach ($orSoumisValidationModel as $orSoumis) {
                    // Instancier une nouvelle entité pour chaque entrée du tableau
                    $ditInsertionOr = new DitOrsSoumisAValidation(); 
                    
                    $ditInsertionOr
                                ->setNumeroVersion($this->autoIncrement($numeroVersionMax))
                                ->setHeureSoumission($this->getTime())
                                ->setDateSoumission(new \DateTime($this->getDatesystem()))
                                ->setNumeroOR($ditInsertionOrSoumis->getNumeroOR())
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
                
                }
                return $orSoumisValidataion;
    }
}
