<?php

namespace App\Controller\tik;

use App\Controller\AbstractController;
use App\Controller\Traits\lienGenerique;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Form\admin\tik\TkiCommentairesType;
use App\Form\tik\DetailTikType;
use App\Repository\admin\StatutDemandeRepository;
use App\Service\EmailService;
use App\Service\fichier\FileUploaderService;
use App\Service\tik\HandleRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DetailTikController extends AbstractController
{
    use lienGenerique;

    /**
     * @Route("/tik-detail/{id<\d+>}", name="detail_tik")
     */
    public function detail($id, Request $request, HandleRequestService $handleRequestService, EntityManagerInterface $em, SessionInterface $sessionService)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /**
         * @var DemandeSupportInformatique $supportInfo l'entité du DemandeSupportInformatique correspondant à l'id $id
         */
        $supportInfo = $em->getRepository(DemandeSupportInformatique::class)->find($id);

        /**
         * @var User $connectedUser l'utilisateur connecté
         */
        $connectedUser = $em->getRepository(User::class)->find($sessionService->get('user_id'));

        $handleRequestService->setSupportInfo($supportInfo);

        if (! $supportInfo) {
            $this->render('404.html.twig');
        } else {
            $formDetail = self::$validator->createBuilder(DetailTikType::class, $supportInfo)->getForm();

            $formDetail->handleRequest($request);

            if ($formDetail->isSubmitted() && $formDetail->isValid()) {
                /**
                 * @var array $button tableau associatif contenant "action" => l'action de la requête (refuser, valider, ...); "statut" => code statut (79, 80, ...) de la demande selon l'action
                 */
                $button = $this->getButton($request);

                $handleRequestService->handleTheRequest($button, $formDetail);

                if ($button['action'] === 'planifier') {
                    $this->redirectToRoute("tik_calendar_planning");
                }

                $this->redirectToRoute("liste_tik_index");
            }

            $commentaire = new TkiCommentaires($supportInfo->getNumeroTicket(), $connectedUser->getNomUtilisateur());

            $formCommentaire = self::$validator->createBuilder(TkiCommentairesType::class, $commentaire)->getForm();

            $formCommentaire->handleRequest($request);

            if ($formCommentaire->isSubmitted() && $formCommentaire->isValid()) {
                $handleRequestService->commenterTicket($formCommentaire, $commentaire);
            }

            $statutOuvert = $supportInfo->getIdStatutDemande()->getId() == 58;
            $isIntervenant = $supportInfo->getIntervenant() !== null && ($supportInfo->getIntervenant()->getId() == $connectedUser->getId());

            $this->logUserVisit('detail_tik', [
                'id' => $id,
            ]); // historisation du page visité par l'utilisateur

            $template = in_array("VALIDATEUR", $connectedUser->getRoleNames()) && ! $statutOuvert ? "detail-2" : "detail-1";

            self::$twig->display("tik/demandeSupportInformatique/$template.html.twig", [
                'tik' => $supportInfo,
                'form' => $formDetail->createView(),
                'formCommentaire' => $formCommentaire->createView(),
                'canComment' => $this->canComment($connectedUser, $supportInfo),
                'statutOuvert' => $statutOuvert,
                'autoriser' => ! empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $connectedUser->getRoleNames())),  // vérifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur' => in_array("VALIDATEUR", $connectedUser->getRoleNames()),                                  // vérifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
                'intervenant' => ! $statutOuvert && $isIntervenant,                   // statut différent de ouvert et l'utilisateur connecté est l'intervenant
                'connectedUser' => $connectedUser,
                'commentaires' => self::$em->getRepository(TkiCommentaires::class)
                    ->findBy(
                        ['numeroTicket' => $supportInfo->getNumeroTicket()],
                        ['dateCreation' => 'ASC']
                    ),
                'historiqueStatut' => self::$em->getRepository(TkiStatutTicketInformatique::class)
                    ->findBy(
                        ['numeroTicket' => $supportInfo->getNumeroTicket()],
                        ['dateStatut' => 'DESC']
                    ),
            ]);
        }
    }

    /**
     * fonction qui retourne l'action du bouton cliqué dans le formulaire
     */
    private function getButton(Request $request)
    {
        $actions = [
            'REF' => 'refuser',      // statut refusé
            'ENC' => 'valider',      // statut en cours
            'PLA' => 'planifier',    // statut planifié
            'RES' => 'resoudre',     // statut résolu
            'ENA' => 'commenter',    // statut en attente
            '00' => 'transferer',
        ];

        /**
         * @var StatutDemandeRepository $statutDemande repository pour StatutDemande
         */
        $statutDemande = self::$em->getRepository(StatutDemande::class);

        // Trouver la clé correspondante
        foreach ($actions as $code => $action) {
            if ($request->request->has($action)) {
                return [
                    'statut' => $statutDemande->findByCodeStatut($code), // l'entité StatutDemande ayant un id=$code
                    'action' => $action,
                ];
            }
        }
    }

    /**
     * Vérifie si l'utilisateur connecté peut commenter sur le ticket
     *
     * @param User $connectedUser l'utilisateur connecté
     * @param DemandeSupportInformatique $tik le ticket en question
     *
     * @return bool
     */
    private function emailTikRefuse($tab): array
    {
        return [
            'to' => $tab['emailUserDemandeur'],
            'template' => $tab['template'],
            'variables' => [
                'statut' => "refuse",
                'subject' => "{$tab['numTik']} - Ticket refusé",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * email pour un ticket suspendu
     */
    private function emailTikSuspendu($tab): array
    {
        return [
            'to' => $tab['emailUserDemandeur'],
            'template' => $tab['template'],
            'variables' => [
                'statut' => "suspendu",
                'subject' => "{$tab['numTik']} - Ticket suspendu",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * email pour un ticket validé
     */
    private function emailTikValide($tab): array
    {
        return [
            'to' => $tab['emailUserDemandeur'],
            'cc' => [$tab['emailIntervenant']],
            'template' => $tab['template'],
            'variables' => [
                'statut' => "valide",
                'subject' => "{$tab['numTik']} - Ticket validé",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * email pour un ticket commenté
     */
    private function emailTikCommente($tab, $emailUserConnected): array
    {
        if (isset($tab['emailValidateur'])) {
            $tabEmail = array_filter([$tab['emailValidateur'], $tab['emailUserDemandeur'], $tab['emailIntervenant']]);
            $cc = array_values(array_diff($tabEmail, [$emailUserConnected]));
            $to = $cc[0];
            $cc = ! empty($cc[1]) ? [$cc[1]] : [];
        } else {
            $emailValidateurs = array_map(function ($validateur) {
                return $validateur->getMail();
            }, self::$em->getRepository(User::class)->findByRole('VALIDATEUR')); // tous les validateurs
            $to = $emailValidateurs[0];
            $cc = array_slice($emailValidateurs, 1);
        }

        return [
            'to' => $to,
            'cc' => $cc,
            'template' => $tab['template'],
            'variables' => [
                'statut' => "comment",
                'subject' => "{$tab['numTik']} - Commentaire émis",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * email pour un ticket résolu
     */
    private function emailTikResolu($tab): array
    {
        $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur']]));

        return [
            'to' => $tabEmail[0],
            'cc' => ! empty($tabEmail[1]) ? [$tabEmail[1]] : [],
            'template' => $tab['template'],
            'variables' => [
                'statut' => "resolu",
                'subject' => "{$tab['numTik']} - Ticket résolu",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * email pour un ticket planifié
     */
    private function emailTikPlanifie($tab): array
    {
        $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur']]));

        return [
            'to' => $tabEmail[0],
            'cc' => ! empty($tabEmail[1]) ? [$tabEmail[1]] : [],
            'template' => $tab['template'],
            'variables' => [
                'statut' => "planifie",
                'subject' => "{$tab['numTik']} - Ticket planifié",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * email pour un ticket transferé
     */
    private function emailTikTransfere($tab): array
    {
        $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur'], $tab['emailIntervenant']]));

        return [
            'to' => $tabEmail[0],
            'cc' => array_slice($tabEmail, 1),
            'template' => $tab['template'],
            'variables' => [
                'statut' => "transfere",
                'subject' => "{$tab['numTik']} - Ticket transféré",
                'tab' => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT']."/tik-detail/{$tab['id']}"),
            ],
        ];
    }

    /**
     * fonction pour vérifier l'envoi du mail ou non
     */
    private function envoyerEmail(array $content)
    {
        $email = new EmailService();

        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.ticketing');

        $content['cc'] = $content['cc'] ?? [];

        $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
    }

    /**
     * Fonction pour le traitement de fichier
     */
    private function traitementEtEnvoiDeFichier($form, TkiCommentaires $commentaire)
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
                $prefix = $commentaire->getNumeroTicket() . '_commentaire_';
                $fileName = $fileUploader->upload($file, $prefix);
                // Obtenir la taille du fichier dans l'emplacement final
                $filePath = $chemin . '/' . $fileName;
                $fileSize = round(filesize($filePath) / 1024, 2); // Taille en Ko avec 2 décimales
                if (file_exists($filePath)) {
                    $fileSize = round(filesize($filePath) / 1024, 2);
                } else {
                    $fileSize = 0; // ou autre valeur par défaut ou message d'erreur
                }

                $fileNames[] = [
                    'name' => $fileName,
                    'size' => $fileSize,
                ];
            }
        }

        // Enregistrez les noms des fichiers dans votre entité
        $commentaire->setFileNames($fileNames);
    }
}
