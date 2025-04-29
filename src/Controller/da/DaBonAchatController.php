<?php

namespace App\Controller\da;

use DateTime;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\da\DemandeApproL;
use App\Controller\Traits\lienGenerique;
use App\Repository\da\DemandeApproLRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaBonAchatController extends Controller
{
    use lienGenerique;

    private DemandeApproLRepository $demandeApproLRepository;

    public function __construct()
    {
        parent::__construct();
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
    }

    /**
     * @Route("/bon-achat/{numDa}", name="da_bon_achat")
     */
    public function bonAchat(string $numDa)
    {

        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa]);

        // Convertir les entités en tableau de données
        $dataExel = $this->transformationEnTableauAvecEntet($dals);

        //creation du fichier excel
        $date = new DateTime();
        $formattedDate = $date->format('Ymd_His');
        $fileName = $numDa . '_' . $formattedDate . '.xlsx';
        $filePath = $_ENV['BASE_PATH_FICHIER'] . '/da/ba/' . $fileName;
        $this->excelService->createSpreadsheetEnregistrer($dataExel, $filePath);

        $this->envoyerMailAuxAppros([
            'numDa'        => $numDa,
            'fileName'         => $fileName,
            'filePath'        => $filePath,
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->sessionService->set('notification', ['type' => 'success', 'message' => "Bon d'achat généré avec succès."]);
        $this->redirectToRoute("da_list");
    }

    private function transformationEnTableauAvecEntet($entities): array
    {
        $data = [];
        $data[] = ['constructeur', 'reference', 'quantité'];

        foreach ($entities as $entity) {
            $data[] = [
                $entity->getArtConstp(),
                $entity->getArtRefp(),
                $entity->getQteDem(),
            ];
        }

        return $data;
    }

    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAppros(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => 'hasina.andrianadison@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "bonAchatDa",
                'subject'    => "{$tab['numDa']} - demande d'approvisionnement BON D'ACHAT",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/demande-appro/list")
            ],
            'attachments' => [
                $tab['filePath'] => $tab['fileName'],
            ],
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables'], $content['attachments']);
    }
}
