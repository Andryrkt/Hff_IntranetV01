<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Service\genererPdf\GeneratePdfDevisMagasin;
use App\Entity\admin\Application;
use Symfony\Component\Form\FormInterface;
use App\Entity\magasin\devis\DevisMagasin;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\devis\DevisMagasinType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinController extends Controller
{
    private const TYPE_SOUMISSION_VERIFICATION_PRIX = 'VP';
    private const STATUT_SOUMISSION_A_VALIDATION = 'Soumis à validation';

    use AutorisationTrait;

    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private HistoriqueOperationDevisMagasinService $historiqueOperationDeviMagasinService;
    private string $cheminBaseUpload;
    private GeneratePdfDevisMagasin $generatePdfDevisMagasin;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
        $this->historiqueOperationDeviMagasinService = new HistoriqueOperationDevisMagasinService();
        $this->cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/';
        $this->generatePdfDevisMagasin = new GeneratePdfDevisMagasin();
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

        if ($numeroDevis === null) {
            $message = "Le numero de devis est obligatoire pour la soumission.";
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
        }

        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        //création du formulaire
        $form = self::$validator->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        //traitement du formualire
        $this->traitementFormualire($form, $request,  $devisMagasin);

        //affichage du formulaire
        self::$twig->display('magasin/devis/soumission.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function postControle() {}

    private function traitementFormualire($form, Request $request,  DevisMagasin $devisMagasin)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());
            //recupération des informations utile dans IPS
            $devisIps = $this->listeDevisMagasinModel->getInfoDev($devisMagasin->getNumeroDevis());

            if (!empty($devisIps)) {
                $firstDevisIps = reset($devisIps);

                // recupération de numero version max
                $numeroVersion = self::$em->getRepository(DevisMagasin::class)->getNumeroVersionMax($devisMagasin->getNumeroDevis());

                //TODO: creation de pdf (à specifier par Antsa)

                /** @var array  enregistrement du fichier*/
                $fichiersEnregistrer = $this->enregistrementFichier($form, $devisMagasin->getNumeroDevis(), $this->autoIncrement($numeroVersion), $suffixConstructeur);
                $nomFichier = !empty($fichiersEnregistrer) ? $fichiersEnregistrer[0] : '';

                //ajout des informations de IPS et des informations manuel comment nombre de lignes, cat, nonCatdans le devis magasin
                $devisMagasin
                    ->setNumeroDevis($devisMagasin->getNumeroDevis())
                    ->setMontantDevis($firstDevisIps['montant_total'])
                    ->setDevise($firstDevisIps['devise'])
                    ->setSommeNumeroLignes($firstDevisIps['somme_numero_lignes'])
                    ->setUtilisateur($this->getUser()->getNomUtilisateur())
                    ->setNumeroVersion($this->autoIncrement($numeroVersion))
                    ->setStatutDw(self::STATUT_SOUMISSION_A_VALIDATION)
                    ->setTypeSoumission(self::TYPE_SOUMISSION_VERIFICATION_PRIX)
                    ->setCat($suffixConstructeur === 'C' || $suffixConstructeur === 'CP' ? true : false)
                    ->setNonCat($suffixConstructeur === 'P' || $suffixConstructeur === 'CP' ? true : false)
                    ->setNomFichier($nomFichier)
                ;

                //enregistrement du devis magasin
                self::$em->persist($devisMagasin);
                self::$em->flush();

                //envoie du fichier dans DW
                $this->generatePdfDevisMagasin->copyToDWDevisMagasin($nomFichier);
            } else {
                //message d'erreur
                $message = "Aucune information trouvé dans IPS pour le devis numero : " . $devisMagasin->getNumeroDevis();
                $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), 'devis_magasin_liste', false);
            }

            //HISTORISATION DE L'OPERATION
            $message = "Le devis numero : " . $devisMagasin->getNumeroDevis() . " a été soumis avec succès.";
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $devisMagasin->getNumeroDevis(), 'devis_magasin_liste', true);
        }
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion, string $suffix): array
    {
        return (new UploderFileService($this->cheminBaseUpload))->getNomsFichiers($form, [
            'repertoire' => $this->cheminBaseUpload,
            'format_nom' => 'verificationprix_{numDevis}-{numeroVersion}#{suffix}.{extension}',
            'variables' => [
                'numDevis' => $numDevis,
                'numeroVersion' => $numeroVersion,
                'suffix' => $suffix
            ]
        ]);
    }

    private function autoIncrement(?int $num)
    {
        if ($num === null || $num === 0) {
            $num = 0;
        }
        return (int) $num + 1;
    }
}
