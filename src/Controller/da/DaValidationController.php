<?php

namespace App\Controller\da;

use DateTime;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Controller\Traits\lienGenerique;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaValidationController extends Controller
{
    use lienGenerique;

    private const ID_ATELIER = 3;

    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DemandeApproRepository $demandeApproRepository;

    public function __construct()
    {
        parent::__construct();
        $this->demandeApproLRepository = self::$em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = self::$em->getRepository(DemandeApproLR::class);
        $this->demandeApproRepository = self::$em->getRepository(DemandeAppro::class);
    }

    /**
     * @Route("/validate/{numDa}", name="da_validate")
     */
    public function validate(string $numDa)
    {
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        $da = $this->modificationDesTable($numDa, $numeroVersionMax);

        /** CREATION EXCEL */
        $nomEtChemin = $this->creationExcel($numDa, $numeroVersionMax);

        /** Ajout non fichier de reference zst */
        $da->setNonFichierRefZst($nomEtChemin['fileName']);

        /** ENVOIE D'EMAIL */
        $dalNouveau = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        if ($this->estUserDansServiceAtelier()) {
            $this->envoyerMailAuxAppro([
                'id'                => $da->getId(),
                'numDa'             => $da->getNumeroDemandeAppro(),
                'objet'             => $da->getObjetDal(),
                'detail'            => $da->getDetailDal(),
                'fileName'          => $nomEtChemin['fileName'],
                'filePath'          => $nomEtChemin['filePath'],
                'dalNouveau'        => $dalNouveau,
                'userConnecter'     => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
            ]);
        } else {
            $this->envoyerMailAuxAte([
                'id'                => $da->getId(),
                'numDa'             => $da->getNumeroDemandeAppro(),
                'objet'             => $da->getObjetDal(),
                'detail'            => $da->getDetailDal(),
                'fileName'          => $nomEtChemin['fileName'],
                'filePath'          => $nomEtChemin['filePath'],
                'dalNouveau'        => $dalNouveau,
                'userConnecter'     => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
            ]);
        }


        /** NOTIFICATION */
        $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("da_list");
    }

    private function estUserDansServiceAtelier(): bool
    {
        $serviceIds = $this->getUser()->getServiceAutoriserIds();
        return in_array(self::ID_ATELIER, $serviceIds);
    }

    private function modificationDesTable(string $numDa, int $numeroVersionMax): DemandeAppro
    {
        /** @var DemandeAppro */
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        if ($da) {
            $da
                ->setEstValidee(true)
                ->setValidePar($this->getUser()->getNomUtilisateur())
                ->setStatutDal('Bon validé')
            ;
        }

        /** @var DemandeApproL */
        $dal = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        if (!empty($dal)) {
            foreach ($dal as $item) {
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setValidePar($this->getUser()->getNomUtilisateur())
                        ->setStatutDal('Bon validé')
                    ;
                }
            }
        }

        /** @var DemandeApproLR */
        $dalr = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa]);
        if (!empty($dalr)) {
            foreach ($dalr as $item) {
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setValidePar($this->getUser()->getNomUtilisateur())
                    ;
                }
            }
        }

        self::$em->flush();

        return $da;
    }

    private function creationExcel(string $numDa, int $numeroVersionMax): array
    {
        $dals = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);

        // Convertir les entités en tableau de données
        $dataExel = $this->transformationEnTableauAvecEntet($dals);

        //creation du fichier excel
        $date = new DateTime();
        $formattedDate = $date->format('Ymd_His');
        $fileName = $numDa . '_' . $formattedDate . '.xlsx';
        $filePath = $_ENV['BASE_PATH_FICHIER'] . '/da/ba/' . $fileName;
        $this->excelService->createSpreadsheetEnregistrer($dataExel, $filePath);

        return [
            'fileName' => $fileName,
            'filePath' => $filePath
        ];
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
     * Fonctions pour envoyer un mail à la service Ate
     */
    private function envoyerMailAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => 'hasina.andrianadison@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "validationDa",
                'subject'    => "{$tab['numDa']} - Validation du demande d'approvisionnement par l'APPRO",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list")
            ],
            'attachments' => [
                $tab['filePath'] => $tab['fileName'],
            ],
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables'], $content['attachments']);
    }

    /** 
     * Fonctions pour envoyer un mail à la service Appro 
     */
    private function envoyerMailAuxAppro(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => 'hasina.andrianadison@hff.mg',
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "validationDa",
                'subject'    => "{$tab['numDa']} - Validation du demande d'approvisionnement par l'ATE",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/list")
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
