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
use App\Service\magasin\devis\DevisMagasinValidationVdService;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use App\Service\magasin\devis\Validator\DevisMagasinValidationOrchestrator;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinValidationDevisController extends Controller
{
    private const TYPE_SOUMISSION_VALIDATION_DEVIS = 'VD';
    private const MESSAGE_DE_CONFIRMATION = 'validation';
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
     * @Route("/soumission-devis-magasin-validation-devis/{numeroDevis}", name="devis_magasin_soumission_validation_devis", defaults={"numeroDevis"=null})
     */
    public function soumission(?string $numeroDevis = null, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //recupération des informations utile dans IPS
        $devisIps = $this->listeDevisMagasinModel->getInfoDev($numeroDevis);
        $firstDevisIps = reset($devisIps);
        $newSumOfMontant = (float)$firstDevisIps['montant_total'];
        $newSumOfLines = (int)$firstDevisIps['somme_numero_lignes'];
        $orchestrator = new DevisMagasinValidationOrchestrator($this->historiqueOperationDeviMagasinService, $numeroDevis);
        // Validation avant soumission - utilise la nouvelle méthode qui retourne un booléen
        $orchestrator->validateBeforeSubmission($this->devisMagasinRepository, $this->listeDevisMagasinModel, $numeroDevis, $newSumOfLines, $newSumOfMontant);
            



        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        //création du formulaire
        $form = $this->getFormFactory()->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        //traitement du formualire
        $this->traitementFormualire($form, $request, $devisMagasin, $orchestrator, $devisIps, $firstDevisIps);

        //affichage du formulaire
        return $this->render('magasin/devis/soumission.html.twig', [
            'form' => $form->createView(),
            'message' => self::MESSAGE_DE_CONFIRMATION,
            'numeroDevis' => $devisMagasin->getNumeroDevis()
        ]);
    }

    private function traitementFormualire($form, Request $request, DevisMagasin $devisMagasin, DevisMagasinValidationOrchestrator $orchestrator, array $devisIps, array $firstDevisIps)
    {

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Validation du fichier soumis via le service dédié
            if (!$orchestrator->validateSubmittedFile($form)) {
                return; // Arrête le traitement si la validation échoue
            }

            $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());


            if (!empty($devisIps)) {

                // recupération de numero version max
                $numeroVersion = $this->devisMagasinRepository->getNumeroVersionMax($devisMagasin->getNumeroDevis());

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
                    ->setStatutDw(DevisMagasin::STATUT_A_VALIDER_CHEF_AGENCE)
                    ->setTypeSoumission(self::TYPE_SOUMISSION_VALIDATION_DEVIS)
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
            $message = "la validation du devis numero : " . $devisMagasin->getNumeroDevis() . " a été envoyée avec succès .";
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), 'devis_magasin_liste', true);
        }
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion, string $suffix, string $mail): array
    {
        return (new UploderFileService($this->cheminBaseUpload))->getNomsFichiers($form, [
            'repertoire' => $this->cheminBaseUpload,
            'format_nom' => 'validationdevis_{numDevis}-{numeroVersion}#{suffix}!{mail}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix,
                'mail' => $mail
            ]
        ]);
    }
}
