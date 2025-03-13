<?php

namespace App\Controller\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\lienGenerique;
use App\Service\fichier\FileUploaderService;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\tik\DemandeSupportInformatiqueType;
use App\Repository\admin\utilisateur\UserRepository;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Service\historiqueOperation\HistoriqueOperationTIKService;

class DemandeSupportInformatiqueController extends Controller
{
    use lienGenerique;
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationTIKService;
    }

    /**
     * @Route("/demande-support-informatique", name="demande_support_informatique")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        $supportInfo = new DemandeSupportInformatique();
        //INITIALISATION DU FORMULAIRE
        $this->initialisationForm($supportInfo, $user);

        $form = self::$validator->createBuilder(DemandeSupportInformatiqueType::class, $supportInfo)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donnerForm = $form->getData();
            $this->ajoutDonnerDansEntity($donnerForm, $supportInfo, $user);
            $this->rectificationDernierIdApplication($supportInfo);
            $this->traitementEtEnvoiDeFichier($form, $supportInfo);

            $text = str_replace(["\r\n", "\n", "\r"], "<br>", $supportInfo->getDetailDemande());
            $supportInfo->setDetailDemande($text);

            //envoi les donnée dans la base de donnée
            self::$em->persist($supportInfo);
            self::$em->flush();

            $this->envoyerMailAuxValidateurs([
                'id'            => $donnerForm->getId(),
                'numTik'        => $donnerForm->getNumeroTicket(),
                'objet'         => $donnerForm->getObjetDemande(),
                'detail'        => $donnerForm->getDetailDemande(),
                'userConnecter' => $user->getPersonnels()->getNom() . ' ' . $user->getPersonnels()->getPrenoms(),
            ]);

            $this->historiqueOperation->sendNotificationCreation('Votre demande a été enregistrée', $supportInfo->getNumeroTicket(), 'liste_tik_index', true);
        }

        $this->logUserVisit('demande_support_informatique'); // historisation du page visité par l'utilisateur

        self::$twig->display('tik/demandeSupportInformatique/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * INITIALISER LA VALEUR DE LA FORMULAIRE
     *
     * @param DemandeIntervention $demandeIntervention
     * @param User $user
     * @return void
     */
    private function initialisationForm(DemandeSupportInformatique $supportInfo, User $user)
    {
        $agenceService = $this->agenceServiceIpsObjet();
        $supportInfo->setAgenceEmetteur($agenceService['agenceIps']->getCodeAgence() . ' ' . $agenceService['agenceIps']->getLibelleAgence());
        $supportInfo->setServiceEmetteur($agenceService['serviceIps']->getCodeService() . ' ' . $agenceService['serviceIps']->getLibelleService());
        $supportInfo->setAgence(self::$em->getRepository(Agence::class)->find('08'));    // agence Administration
        $supportInfo->setService(self::$em->getRepository(Service::class)->find('13'));   // service Informatique
        $supportInfo->setDateFinSouhaiteeAutomatique();
        $supportInfo->setCodeSociete($user->getSociettes()->getCodeSociete());
    }

    private function ajoutDonnerDansEntity($donnerForm, DemandeSupportInformatique $supportInfo, User $user)
    {
        $agenceEmetteur = self::$em->getRepository(Agence::class)->findOneBy(['codeAgence' => explode(' ', $donnerForm->getAgenceEmetteur())[0]]);
        $serviceEmetteur = self::$em->getRepository(Service::class)->findOneBy(['codeService' => explode(' ', $donnerForm->getServiceEmetteur())[0]]);

        $statut = self::$em->getRepository(StatutDemande::class)->find('58');

        $supportInfo
            ->setAgenceDebiteurId($donnerForm->getAgence())
            ->setServiceDebiteurId($donnerForm->getService())
            ->setAgenceEmetteurId($agenceEmetteur)
            ->setServiceEmetteurId($serviceEmetteur)
            ->setHeureCreation($this->getTime())
            ->setUtilisateurDemandeur($user->getNomUtilisateur())
            ->setUserId($user)
            ->setMailDemandeur($user->getMail())
            ->setAgenceServiceEmetteur($agenceEmetteur->getCodeAgence() . '-' . $serviceEmetteur->getCodeService())
            ->setAgenceServiceDebiteur($donnerForm->getAgence()->getCodeAgence() . '-' . $donnerForm->getService()->getCodeService())
            ->setNumeroTicket($this->autoINcriment('TIK'))
            ->setIdStatutDemande($statut)
            ->setCodeSociete($user->getSociettes()->getCodeSociete())
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
        $chemin = $_ENV['BASE_PATH_FICHIER'].'/tik/fichiers';
        $fileUploader = new FileUploaderService($chemin);
        if ($files) {
            foreach ($files as $file) {
                // Définissez le préfixe pour chaque fichier, par exemple "DS_" pour "Demande de Support"
                $prefix = $supportInfo->getNumeroTicket() . '_detail_';
                $fileName = $fileUploader->upload($file, $prefix);
                // Obtenir la taille du fichier dans l'emplacement final
                $fileSize = $this->tailleFichier($chemin, $fileName);

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

    private function tailleFichier(string $chemin, string $fileName): int
    {
        $filePath = $chemin . '/' . $fileName;
        $fileSize = round(filesize($filePath) / 1024, 2); // Taille en Ko avec 2 décimales
        if (file_exists($filePath)) {
            $fileSize = round(filesize($filePath) / 1024, 2);
        } else {
            $fileSize = 0; // ou autre valeur par défaut ou message d'erreur
        }
        return $fileSize;
    }

    /** 
     * Fonctions pour envoyer un mail aux validateurs
     */
    private function envoyerMailAuxValidateurs(array $tab)
    {
        $email       = new EmailService;

        $emailValidateurs = array_map(function ($validateur) {
            return $validateur->getMail();
        }, self::$em->getRepository(User::class)->findByRole('VALIDATEUR')); // tous les validateurs

        $content = [
            'to'        => $emailValidateurs[0],
            'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'tik/email/emailTik.html.twig',
            'variables' => [
                'statut'     => "newTik",
                'subject'    => "{$tab['numTik']} - Nouveau ticket créé",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}")
            ]
        ];
        $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
    }
}
