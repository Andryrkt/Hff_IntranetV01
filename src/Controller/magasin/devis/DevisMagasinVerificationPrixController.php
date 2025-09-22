<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\autres\VersionService;
use Symfony\Component\Form\FormInterface;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\devis\DevisMagasinType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Service\genererPdf\GeneratePdfDevisMagasin;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Service\magasin\devis\DevisMagasinValidationVpService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinVerificationPrixController extends Controller
{
    private const TYPE_SOUMISSION_VERIFICATION_PRIX = 'VP';
    private const STATUT_PRIX_A_CONFIRMER = 'Prix à confirmer';
    private const MESSAGE_DE_CONFIRMATION = 'verification prix';

    use AutorisationTrait;

    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private string $cheminBaseUpload;
    private GeneratePdfDevisMagasin $generatePdfDevisMagasin;
    private DevisMagasinRepository $devisMagasinRepository;

    public function __construct()
    {
        parent::__construct();
        global $container;
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
        $this->historiqueOperationDeviMagasinService = $container->get(HistoriqueOperationDevisMagasinService::class);
        $this->cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/';
        $this->generatePdfDevisMagasin = new GeneratePdfDevisMagasin();
        $this->devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
    }

    /**
     * @Route("/soumission-devis-magasin-verification-de-prix/{numeroDevis}", name="devis_magasin_soumission_verification_prix", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $numeroDevis = null, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        // Instantiation et validation de la présence du numéro de devis
        $validationService = new DevisMagasinValidationVpService($this->historiqueOperationDeviMagasinService, $numeroDevis ?? '');
        if (!$validationService->checkMissingIdentifier($numeroDevis)) {
            // Le service a envoyé la notification, on arrête le traitement ici.
            return;
        }

        // Validation du statut du devis pour la verification de prix (encours de validation)
        if (!$validationService->checkBlockingStatusOnSubmission($this->devisMagasinRepository, $numeroDevis)) {
            return; // Arrête le traitement si le statut est bloquant
        }
        

        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        //traitement du formualire
        $this->traitementFormualire($form, $request,  $devisMagasin, $validationService);

        //affichage du formulaire
        return $this->render('magasin/devis/soumission.html.twig', [
            'form' => $form->createView(),
            'message' => self::MESSAGE_DE_CONFIRMATION,
            'numeroDevis' => $devisMagasin->getNumeroDevis()
        ]);
    }

    private function traitementFormualire($form, Request $request,  DevisMagasin $devisMagasin, DevisMagasinValidationVpService $validationService)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Validation du fichier soumis 
            if (!$validationService->validateSubmittedFile($form)) {
                return; // Arrête le traitement si la validation échoue
            }

            $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());

            //recupération des informations utile dans IPS
            $devisIps = $this->listeDevisMagasinModel->getInfoDev($devisMagasin->getNumeroDevis());

            if (!empty($devisIps)) {
                $firstDevisIps = reset($devisIps);

                // Validation de la somme des lignes
                $newSumOfLines = (int)$firstDevisIps['somme_numero_lignes'];

                // Validation du statut du devis pour la validation de devis (doit passer par validation devis)
                if (!$validationService->checkBlockingStatusOnSubmissionForVd($this->devisMagasinRepository, $devisMagasin->getNumeroDevis(), $newSumOfLines)) {
                    return; // Arrête le traitement si le statut est bloquant
                }


                if ($validationService->estSommeDeLigneInChanger($this->devisMagasinRepository, $devisMagasin->getNumeroDevis(), $newSumOfLines)) {
                    return; // Arrête le traitement si la somme des lignes est identique
                }
                // recupération de numero version max
                $numeroVersion = $this->devisMagasinRepository->getNumeroVersionMax($devisMagasin->getNumeroDevis());

                //TODO: creation de pdf (à specifier par Antsa)

                /** @var User $utilisateur */
                $utilisateur = $this->getUser();
                $email = method_exists($utilisateur, 'getMail') ? $utilisateur->getMail() : (method_exists($utilisateur, 'getNomUtilisateur') ? $utilisateur->getNomUtilisateur() : '');
                /** @var array  enregistrement du fichier*/
                $fichiersEnregistrer = $this->enregistrementFichier($form, $devisMagasin->getNumeroDevis(), VersionService::autoIncrement($numeroVersion), $suffixConstructeur, explode('@', $email)[0]);
                $nomFichier = !empty($fichiersEnregistrer) ? $fichiersEnregistrer[0] : '';

                //ajout des informations de IPS et des informations manuelles comme nombre de lignes, cat, nonCat dans le devis magasin
                $devisMagasin
                    ->setNumeroDevis($devisMagasin->getNumeroDevis())
                    ->setMontantDevis($firstDevisIps['montant_total'])
                    ->setDevise($firstDevisIps['devise'])
                    ->setSommeNumeroLignes($firstDevisIps['somme_numero_lignes'])
                    ->setUtilisateur($this->getUser()->getNomUtilisateur())
                    ->setNumeroVersion(VersionService::autoIncrement($numeroVersion))
                    ->setStatutDw(self::STATUT_PRIX_A_CONFIRMER)
                    ->setTypeSoumission(self::TYPE_SOUMISSION_VERIFICATION_PRIX)
                    ->setCat($suffixConstructeur === 'C' || $suffixConstructeur === 'CP' ? true : false)
                    ->setNonCat($suffixConstructeur === 'P' || $suffixConstructeur === 'CP' ? true : false)
                    ->setNomFichier($nomFichier)
                ;

                //enregistrement du devis magasin
                $this->getEntityManager()->persist($devisMagasin);
                $this->getEntityManager()->flush();

                //envoie du fichier dans DW
                $this->generatePdfDevisMagasin->copyToDWDevisMagasin($nomFichier);
            } else {
                //message d'erreur
                $message = "Aucune information trouvé dans IPS pour le devis numero : " . $devisMagasin->getNumeroDevis();
                $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), 'devis_magasin_liste', false);
            }

            //HISTORISATION DE L'OPERATION
            $message = "la vérification de prix du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), 'devis_magasin_liste', true);
        }
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion, string $suffix, string $mail): array
    {
        return (new UploderFileService($this->cheminBaseUpload))->getNomsFichiers($form, [
            'repertoire' => $this->cheminBaseUpload,
            'format_nom' => 'verificationprix_{numDevis}-{numeroVersion}#{suffix}!{mail}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix,
                'mail' => $mail
            ]
        ]);
    }
}
