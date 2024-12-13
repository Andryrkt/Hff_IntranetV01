<?php

namespace App\Controller\badm;

use App\Entity\cas\Casier;
use App\Controller\Controller;
use App\Model\badm\CasierModel;
use App\Entity\admin\Application;
use App\Entity\cas\CasierValider;
use App\Form\cas\CasierForm1Type;
use App\Form\cas\CasierForm2Type;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\ConversionTrait;
use App\Service\genererPdf\GenererPdfCasier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class CasierController extends Controller
{

    use Transformation;
    use ConversionTrait;
    use FormatageTrait;


    /**
     * @Route("/nouveauCasier", name="casier_nouveau")
     */
    public function NouveauCasier(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $casier = new Casier();

        $agenceService = $this->agenceServiceIpsString();

        $casier->setAgenceEmetteur($agenceService['agenceIps']);
        $casier->setServiceEmetteur($agenceService['serviceIps']);

        $form = self::$validator->createBuilder(CasierForm1Type::class, $casier)->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $casierModel = new CasierModel();
            $data = $casierModel->findAll($casier->getIdMateriel(),  $casier->getNumParc(), $casier->getNumSerie());
            if ($casier->getIdMateriel() === null &&  $casier->getNumParc() === null && $casier->getNumSerie() === null) {
                $message = " Renseigner l\'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->alertRedirection($message);
            } elseif (empty($data)) {
                $message = "Matériel déjà vendu";
                $this->alertRedirection($message);
            } else {
                $formData = [
                    'idMateriel' => $casier->getIdMateriel(),
                    'numParc' => $casier->getNumParc(),
                    'numSerie' => $casier->getNumSerie()
                ];
                $this->sessionService->set('casierform1Data', $formData);
                $this->redirectToRoute("casiser_formulaireCasier");
            }
        }

        $this->logUserVisit('casier_nouveau'); // historisation du page visité par l'utilisateur

        self::$twig->display(
            'badm/casier/nouveauCasier.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/createCasier", name="casiser_formulaireCasier", methods={"GET","POST"})
     */
    public function FormulaireCasier(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $casier = new Casier();
        $form1Data = $this->sessionService->get('casierform1Data', []);

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


        $form = self::$validator->createBuilder(CasierForm2Type::class, $casier)->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $casier->setNumeroCas($this->autoINcriment('CAS'));
            //RECUPERATION de la dernière NumeroDemandeIntervention 
            $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'CAS']);
            $application->setDerniereId($casier->getNumeroCas());
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();


            $NumCAS = $casier->getNumeroCas();
            $user = self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'));
            $casier->setAgenceRattacher($form->getData()->getAgence());
            $casier->setCasier($casier->getClient() . ' - ' . $casier->getChantier());
            $casier->setIdStatutDemande(self::$em->getRepository(StatutDemande::class)->find(55));
            $casier->setNomSessionUtilisateur($user);
            $agenceEmetteur = $data[0]['agence'];
            $serviceEmetteur = $data[0]['code_service'];
            $MailUser = $user->getMail();
            $dateDemande = $this->getDatesystem();

            $generPdfCasier = $this->generPdfCasier($NumCAS, $dateDemande, $data, $casier, $MailUser, $agenceEmetteur, $serviceEmetteur);

            /** CREATION PDF */
            $genererPdfCasier = new GenererPdfCasier();
            $genererPdfCasier->genererPdfCasier($generPdfCasier);
            $genererPdfCasier->copyInterneToDOXCUWARE($NumCAS, $agenceEmetteur . $serviceEmetteur);

            self::$em->persist($casier);
            self::$em->flush();

            $this->redirectToRoute('listeTemporaire_affichageListeCasier');
        }

        $this->logUserVisit('casiser_formulaireCasier'); // historisation du page visité par l'utilisateur

        self::$twig->display(
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

    /**
     * @Route("/casierDestinataire", name="badm_casierDestinataire")
     */
    public function casierDestinataire()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $casierDestinataireInformix = $this->badm->recupeCasierDestinataireInformix();
        //$casierDestinataireSqlServer = $this->badm->recupeCasierDestinataireSqlServer();
        $casierDestinataire = self::$em->getRepository(CasierValider::class)->findAll();

        $casierDestinataireSqlServer = [];
        foreach ($casierDestinataire as $value) {
            $casierDestinataireSqlServer[] = [
                'Agence_Rattacher' => $value->getAgenceRattacher()->getCodeAgence(),
                'Casier' => $value->getCasier()
            ];
        }

        // Combinaison des deux tableaux
        $resultat = [];

        foreach ($casierDestinataireInformix as $agence) {
            foreach ($casierDestinataireSqlServer as $casier) {

                if ($casier['Agence_Rattacher'] == $agence['code_agence']) {

                    $resultat[$agence['agence']][] = $casier['Casier'];
                }
            }

            //Assurez-vous que chaque agence est présente même si elle n'a pas de casiers
            if (!array_key_exists($agence['agence'], $resultat)) {
                $resultat[$agence['agence']] = [];
            }
        }

        header("Content-type:application/json");

        $jsonData = json_encode($resultat);

        $this->testJson($jsonData);
    }


    private function alertRedirection(string $message, string $chemin = "/Hffintranet/nouveauCasier")
    {
        echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
    }
}
