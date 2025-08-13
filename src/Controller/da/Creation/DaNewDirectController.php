<?php

namespace App\Controller\da\Creation;

use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\ApplicationTrait;
use App\Controller\Traits\da\creation\DaNewDirectTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproDirectFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/demande-appro")
 */
class DaNewDirectController extends Controller
{
    use DaNewDirectTrait;
    use ApplicationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaNewDirectTrait();
    }

    /**
     * @Route("/new-da-direct", name="da_new_direct")
     */
    public function newDADirect(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $demandeAppro = $this->initialisationDemandeApproDirect();

        $form = self::$validator->createBuilder(DemandeApproDirectFormType::class, $demandeAppro)->getForm();
        $this->traitementFormDirect($form, $request, $demandeAppro);

        self::$twig->display('da/new-da-direct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormDirect($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** 
             * @var DemandeAppro $demandeAppro
             */
            $demandeAppro = $form->getData();

            $numDa = $demandeAppro->getNumeroDemandeAppro();

            $formDAL = $form->get('DAL');
            /** 
             * Ajout de ligne de demande appro dans la table Demande_Appro_L 
             * @var DemandeApproL $DAL la demande appro l à enregistrer dans la BDD
             **/
            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                if (null === $DAL->getNumeroFournisseur()) {
                    $this->sessionService->set('notification', ['type' => 'danger', 'message' => 'Erreur : Le nom du fournisseur doit correspondre à l’un des choix proposés.']);
                    $this->redirectToRoute("list_da");
                }

                $DAL->setNumeroDemandeAppro($numDa)
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal(DemandeAppro::STATUT_A_VALIDE_DW)
                    ->setJoursDispo($this->getJoursRestants($DAL));
                $this->traitementFichiers($DAL, $formDAL[$ligne + 1]->get('fileNames')->getData()); // traitement des fichiers uploadés pour chaque ligne DAL
                self::$em->persist($DAL);
            }

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            self::$em->persist($demandeAppro);

            /** Modifie la colonne dernière_id dans la table applications */
            $this->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            self::$em->flush();

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro);

            // ajout des données dans la table DaSoumisAValidation
            $this->ajouterDansDaSoumisAValidation($demandeAppro);

            /** création de pdf et envoi dans docuware */
            $this->creationPdfSansDitAvaliderDW($demandeAppro);

            // TODO: mail après la création d'une DA directe
            // $this->envoyerMailAuxAppros([
            //     'id'            => $demandeAppro->getId(),
            //     'numDa'         => $numDa,
            //     'objet'         => $demandeAppro->getObjetDal(),
            //     'detail'        => $demandeAppro->getDetailDal(),
            //     'dals'          => $demandeAppro->getDAL(),
            //     'service'       => strtolower($demandeAppro->getServiceEmetteur()->getLibelleService()),
            //     'observation'   => $demandeAppro->getObservation() !== null ? $demandeAppro->getObservation() : '-',
            //     'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
            // ]);

            $this->sessionService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }

    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAppros(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => DemandeAppro::MAIL_APPRO,
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "newDa",
                'subject'    => "{$tab['numDa']} - Nouvelle demande d'approvisionnement créé",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD pour chaque ligne de la demande appro (DAL)
     */
    private function traitementFichiers(DemandeApproL $dal, $files): void
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->daFileUploader->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
    }
}
