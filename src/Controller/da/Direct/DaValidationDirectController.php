<?php

namespace App\Controller\da\Direct;

use DateTime;
use App\Service\EmailService;
use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Controller\Traits\da\DaTrait;
use App\Controller\Traits\EntityManagerAwareTrait;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Controller\Traits\lienGenerique;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaValidationDirectController extends Controller
{
    use DaTrait;
    use lienGenerique;
    use DaAfficherTrait;
    use EntityManagerAwareTrait;

    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaTrait();

        $this->ditOrsSoumisAValidationRepository = self::$em->getRepository(DitOrsSoumisAValidation::class);
    }

    /**
     * @Route("/validate-direct/{numDa}", name="da_validate_direct")
     */
    public function validate(string $numDa, Request $request)
    {
        $daValidationData = $request->request->get('da_proposition_validation');
        $refsValide = json_decode($daValidationData['refsValide'], true) ?? [];
        $prixUnitaire = $request->get('PU', []); // obtenir les PU envoyé par requête

        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        /** @var DemandeAppro $da la demande appro retourné par la fonction */
        $da = $this->modificationDesTable($numDa, $numeroVersionMax, $prixUnitaire, $refsValide);

        /** CREATION EXCEL */
        $nomEtChemin = $this->creationExcel($numDa, $numeroVersionMax);

        /** Ajout nom fichier du bon d'achat (excel) */
        $da->setNomFichierBav($nomEtChemin['fileName']);

        $this->ajouterDansTableAffichageParNumDa($da->getNumeroDemandeAppro()); // enregistrer dans la table Da Afficher

        $dalNouveau = $this->getLignesRectifieesDA($numDa, $numeroVersionMax);

        /** ENVOIE D'EMAIL */
        $this->envoyerMailValidationAuxAppro([
            'id'            => $da->getId(),
            'numDa'         => $da->getNumeroDemandeAppro(),
            'objet'         => $da->getObjetDal(),
            'detail'        => $da->getDetailDal(),
            'dalNouveau'    => $dalNouveau,
            'service'       => 'appro',
            'userConnecter' => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        $this->envoyerMailValidationAuxAte([
            'id'                => $da->getId(),
            'numDa'             => $da->getNumeroDemandeAppro(),
            'mailDemandeur'     => $da->getUser()->getMail(),
            'objet'             => $da->getObjetDal(),
            'detail'            => $da->getDetailDal(),
            'fileName'          => $nomEtChemin['fileName'],
            'filePath'          => $nomEtChemin['filePath'],
            'dalNouveau'        => $dalNouveau,
            'service'           => 'appro',
            'phraseValidation'  => 'Vous trouverez en pièce jointe le fichier contenant les références ZST.',
            'userConnecter'     => $this->getUser()->getPersonnels()->getNom() . ' ' . $this->getUser()->getPersonnels()->getPrenoms(),
        ]);

        /** NOTIFICATION */
        $this->sessionService->set('notification', ['type' => 'success', 'message' => 'La demande a été validée avec succès.']);
        $this->redirectToRoute("list_da");
    }

    private function modificationDesTable(string $numDa, int $numeroVersionMax, array $prixUnitaire, array $refsValide): DemandeAppro
    {
        /** @var DemandeAppro */
        $da = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);
        if ($da) {
            $da
                ->setEstValidee(true)
                ->setValidateur($this->getUser())
                ->setValidePar($this->getUser()->getNomUtilisateur())
                ->setStatutDal(DemandeAppro::STATUT_VALIDE)
            ;
            self::$em->persist($da);
        }

        /** @var DemandeApproL */
        $dal = $this->demandeApproLRepository->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax]);
        if (!empty($dal)) {
            foreach ($dal as $item) {
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setValidePar($this->getUser()->getNomUtilisateur())
                        ->setStatutDal(DemandeAppro::STATUT_VALIDE)
                    ;
                    // vérifier si $prixUnitaire n'est pas vide puis le numéro de la ligne de la DA existe dans les clés du tableau $prixUnitaire
                    if (!empty($prixUnitaire) && array_key_exists($item->getNumeroLigne(), $prixUnitaire)) {
                        $item->setPrixUnitaire($prixUnitaire[$item->getNumeroLigne()]);
                    }

                    self::$em->persist($item);
                }
            }
        }

        $dalr = $this->demandeApproLRRepository->findBy(['numeroDemandeAppro' => $numDa]);
        if (!empty($dalr)) {
            foreach ($dalr as $item) {
                /** @var DemandeApproLR $item le DALR correspondant */
                if ($item) {
                    $item
                        ->setEstValidee(true)
                        ->setChoix(false) // on réinitialise le choix à false
                        ->setValidePar($this->getUser()->getNomUtilisateur())
                        ->setStatutDal(DemandeAppro::STATUT_VALIDE)
                    ;
                    if (key_exists($item->getNumeroLigne(), $refsValide)) {
                        if ($item->getNumLigneTableau() == $refsValide[$item->getNumeroLigne()]) {
                            $item->setChoix(true);
                        }
                    }

                    self::$em->persist($item);
                }
            }
        }

        return $da;
    }

    /** 
     * Fonctions pour envoyer un mail de validation à la service Ate
     */
    private function envoyerMailValidationAuxAte(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => $tab['mailDemandeur'],
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "validationDa",
                'subject'    => "{$tab['numDa']} - Proposition(s) validée(s) par l'APPRO",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail/" . $tab['id']),
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
     * Fonctions pour envoyer un mail de validation à la service Appro 
     */
    private function envoyerMailValidationAuxAppro(array $tab)
    {
        $email       = new EmailService;

        $content = [
            'to'        => DemandeAppro::MAIL_APPRO,
            // 'cc'        => array_slice($emailValidateurs, 1),
            'template'  => 'da/email/emailDa.html.twig',
            'variables' => [
                'statut'     => "validationAteDa",
                'subject'    => "{$tab['numDa']} - Proposition(s) validée(s) par l'APPRO",
                'tab'        => $tab,
                'action_url' => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail/" . $tab['id']),
            ]
        ];
        $email->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');
        // $email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
        $email->sendEmail($content['to'], [], $content['template'], $content['variables']);
    }
}
