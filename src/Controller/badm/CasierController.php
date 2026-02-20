<?php

namespace App\Controller\badm;

use App\Entity\cas\Casier;
use App\Controller\Controller;
use App\Model\badm\CasierModel;
use App\Entity\admin\Application;
use App\Form\cas\CasierForm1Type;
use App\Form\cas\CasierForm2Type;
use App\Entity\admin\StatutDemande;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\ConversionTrait;
use App\Service\genererPdf\GenererPdfCasier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationCASService;

/**
 * @Route("/materiel/casier")
 */
class CasierController extends Controller
{

    use Transformation;
    use ConversionTrait;
    use FormatageTrait;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationCASService($this->getEntityManager());
    }

    /**
     * @Route("/cas-form1", name="casier_nouveau")
     */
    public function NouveauCasier(Request $request)
    {
        $casier = new Casier();

        $agenceService = $this->agenceServiceIpsString();

        $casier->setAgenceEmetteur($agenceService['agenceIps']);
        $casier->setServiceEmetteur($agenceService['serviceIps']);

        $form = $this->getFormFactory()->createBuilder(CasierForm1Type::class, $casier)->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $casierModel = new CasierModel();
            $data = $casierModel->findAll($casier->getIdMateriel(),  $casier->getNumParc(), $casier->getNumSerie());
            if ($casier->getIdMateriel() === null &&  $casier->getNumParc() === null && $casier->getNumSerie() === null) {
                $message = " Renseigner l\'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'casier_nouveau');
            } elseif (empty($data)) {
                $message = "Matériel déjà vendu";
                $this->historiqueOperation->sendNotificationCreation($message, '-', 'casier_nouveau');
            } else {
                $formData = [
                    'idMateriel' => $casier->getIdMateriel(),
                    'numParc' => $casier->getNumParc(),
                    'numSerie' => $casier->getNumSerie()
                ];
                $this->getSessionService()->set('casierform1Data', $formData);

                $this->redirectToRoute("casiser_formulaireCasier");
            }
        }

        $this->logUserVisit('casier_nouveau'); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/casier/nouveauCasier.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/cas-form2", name="casiser_formulaireCasier", methods={"GET","POST"})
     */
    public function FormulaireCasier(Request $request)
    {
        $casier = new Casier();
        $form1Data = $this->getSessionService()->get('casierform1Data', []);

        //Recupérations de tous les matériel
        $casierModel = new CasierModel();
        $data = $casierModel->findAll($form1Data["idMateriel"],  $form1Data["numParc"], $form1Data["numSerie"]);


        $casier
            ->setGroupe($data[0]["famille"])
            ->setAffectation($data[0]["affectation"])
            ->setConstructeur($data[0]["constructeur"])
            ->setDesignation($data[0]["designation"])
            ->setModele($data[0]["modele"])
            ->setNumParc($data[0]["num_parc"])
            ->setNumSerie($data[0]["num_serie"])
            ->setIdMateriel($data[0]["num_matricule"])
            ->setAnneeDuModele($data[0]["annee"])
            ->setDateAchat($this->formatageDate($data[0]["date_achat"]))
            ->setDateCreation(\DateTime::createFromFormat('Y-m-d', $this->getDatesystem()))
        ;


        $form = $this->getFormFactory()->createBuilder(CasierForm2Type::class, $casier)->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $casier->setNumeroCas($this->autoINcriment('CAS'));
            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'CAS']);
            $application->setDerniereId($casier->getNumeroCas());
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            $this->getEntityManager()->persist($application);
            $this->getEntityManager()->flush();


            $NumCAS = $casier->getNumeroCas();
            $user = $this->getUser();
            $casier->setAgenceRattacher($form->getData()->getAgence());
            $casier->setCasier($casier->getClient() . ' - ' . $casier->getChantier());
            $casier->setIdStatutDemande($this->getEntityManager()->getRepository(StatutDemande::class)->find(55));
            $casier->setNomSessionUtilisateur($user);
            $agenceEmetteur = $data[0]['agence'];
            $serviceEmetteur = $data[0]['code_service'];
            $MailUser = $user->getMail();
            $dateDemande = $this->getDatesystem();

            $generPdfCasier = $this->generPdfCasier($NumCAS, $dateDemande, $data, $casier, $MailUser, $agenceEmetteur, $serviceEmetteur);

            /** CREATION PDF */
            $genererPdfCasier = new GenererPdfCasier();
            $genererPdfCasier->genererPdfCasier($generPdfCasier);
            $genererPdfCasier->copyInterneToDOCUWARE($NumCAS, $agenceEmetteur . $serviceEmetteur);

            $this->getEntityManager()->persist($casier);
            $this->getEntityManager()->flush();

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistré', $NumCAS, 'listeTemporaire_affichageListeCasier', true);
        }

        $this->logUserVisit('casiser_formulaireCasier'); // historisation du page visité par l'utilisateur

        return $this->render(
            'badm/casier/formulaireCasier.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }


    private function generPdfCasier($NumCAS, $dateDemande, $data, $casier, $MailUser, $agenceEmetteur, $serviceEmetteur): array
    {
        return [

            'Num_CAS' => $NumCAS,
            'Date_Demande' => $this->formatageDate($dateDemande),
            'Designation' => $data[0]['designation'],
            'Num_ID' => $data[0]['num_matricule'],
            'Num_Serie' => $data[0]['num_serie'],
            'Groupe' => $data[0]['famille'],
            'Num_Parc' => $casier->getNumParc(),
            'Affectation' => $data[0]['affectation'],
            'Constructeur' => $data[0]['constructeur'],
            'Date_Achat' => $this->formatageDate($data[0]['date_achat']),
            'Annee_Model' => $data[0]['annee'],
            'Modele' => $data[0]['modele'],
            'Agence' => $casier->getAgence()->getCodeAgence() . '-' . $casier->getAgence()->getLibelleAgence(),
            'Motif_Creation' => $casier->getMotif(),
            'Client' => $casier->getClient(),
            'Chantier' => $casier->getChantier(),
            'Email_Emetteur' => $MailUser,
            'Agence_Service_Emetteur_Non_separer' => $agenceEmetteur . $serviceEmetteur
        ];
    }
}
