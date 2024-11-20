<?php

namespace App\Controller\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\tik\DemandeSupportInformatiqueType;
use App\Service\fichier\FileUploaderService;

class DemandeSupportInformatiqueController extends Controller
{
    /**
     * @Route("/demande-support-informatique", name="demande_support_informatique")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $supportInfo = new DemandeSupportInformatique();
        //INITIALISATION DU FORMULAIRE
        $this->initialisationForm($supportInfo);

        $form = self::$validator->createBuilder(DemandeSupportInformatiqueType::class, $supportInfo)->getForm();
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $donnerForm = $form->getData();
            $this->ajoutDonnerDansEntity($donnerForm, $supportInfo);
            $this->rectificationDernierIdApplication($supportInfo);
            $this->traitementEtEnvoiDeFichier($form, $supportInfo);
            
            //envoi les donnée dans la base de donnée
            self::$em->persist($supportInfo);
            self::$em->flush();

            $this->sessionService->set('notification',['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("liste_tik_index");
        }

        self::$twig->display('tik/demandeSupportInformatique/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * INITIALISER LA VALEUR DE LA FORMULAIRE
     *
     * @param DemandeIntervention $demandeIntervention
     * @param [type] $em
     * @return void
     */
    private function initialisationForm(DemandeSupportInformatique $supportInfo)
    {
        $agenceService = $this->agenceServiceIpsObjet();
        $supportInfo->setAgenceEmetteur($agenceService['agenceIps']->getCodeAgence() . ' '. $agenceService['agenceIps']->getLibelleAgence());
        $supportInfo->setServiceEmetteur($agenceService['serviceIps']->getCodeService() . ' ' . $agenceService['serviceIps']->getLibelleService());
        $supportInfo->setAgence($agenceService['agenceIps']);
        $supportInfo->setService($agenceService['serviceIps']);
        $supportInfo->setDateFinSouhaiteeAutomatique();
    }

    private function ajoutDonnerDansEntity($donnerForm, $supportInfo)
    {
        $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => explode(' ', $donnerForm->getAgenceEmetteur())[0]]);
        $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => explode(' ', $donnerForm->getServiceEmetteur())[0]]);
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);
        $statut = self::$em->getRepository(StatutDemande::class)->find('79');
        
        /** 
         * TODO: code_société à revoir (problem: utilisateur qui a plusieur société)
         * */
        $supportInfo 
            ->setAgenceDebiteurId($donnerForm->getAgence())
            ->setServiceDebiteurId($donnerForm->getService())
            ->setAgenceEmetteurId($agenceEmetteur)
            ->setServiceEmetteurId($serviceEmetteur)
            ->setHeureCreation($this->getTime())
            ->setUtilisateurDemandeur($user->getNomUtilisateur())
            ->setUserId($user)
            ->setMailDemandeur($user->getMail())
            ->setAgenceServiceEmetteur($agenceEmetteur->getCodeAgence() . $serviceEmetteur->getCodeService())
            ->setAgenceServiceDebiteur($donnerForm->getAgence()->getCodeAgence() . $donnerForm->getService()->getCodeService())
            ->setNumeroTicket($this->autoINcriment('TIK'))
            ->setIdStatutDemande($statut)
        ;

        $this->historiqueStatut($supportInfo, $statut);
    }

    private function historiqueStatut($supportInfo, $statut)
    {
        $tikStatut = new TkiStatutTicketInformatique();
        $tikStatut
            ->setNumeroTicket($supportInfo->getNumeroTicket())
            ->setCodeStatut($statut->getCodeStatut())
            ->setIdStatutDemande($statut)
        ;
        self::$em->persist($tikStatut);
        self::$em->flush();
    }

    private function rectificationDernierIdApplication($supportInfo)
    {
        //RECUPERATION de la dernière NumeroDemandeIntervention 
        $application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'TIK']);
        $application->setDerniereId($supportInfo->getNumeroTicket());
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        self::$em->persist($application);
        self::$em->flush();
    }

    private function traitementEtEnvoiDeFichier($form, $supportInfo)
    {
        //TRAITEMENT FICHIER
        $fileNames = [];
        // Récupérez les fichiers uploadés depuis le formulaire
        $files = $form->get('fileNames')->getData();
        $chemin = $_SERVER['DOCUMENT_ROOT'] . '/Upload/tik/fichiers';
        $fileUploader = new FileUploaderService($chemin);
        if ($files) {
            foreach ($files as $file) {
                // Définissez le préfixe pour chaque fichier, par exemple "DS_" pour "Demande de Support"
                $prefix = $supportInfo->getNumeroTicket() .'_';
                $fileName = $fileUploader->upload($file, $prefix);
                // Obtenir la taille du fichier dans l'emplacement final
            $filePath = $chemin . '/' . $fileName;
            $fileSize = round(filesize($filePath) / 1024, 2); // Taille en Ko avec 2 décimales
            if (file_exists($filePath)) {
                $fileSize = round(filesize($filePath) / 1024, 2);
            } else {
                $fileSize = 0; // ou autre valeur par défaut ou message d'erreur
            }
            
                $fileNames[] = 
                    [
                        'name' => $fileName,
                        'size' => $fileSize
                    ];
            }
        }
       // Enregistrez les noms des fichiers dans votre entité
        $supportInfo->setFileNames($fileNames);
    }
}

