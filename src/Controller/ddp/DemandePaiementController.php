<?php

namespace App\Controller\ddp;

use Exception;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Entity\admin\ddp\TypeDemande;
use App\Form\ddp\DemandePaiementType;
use App\Controller\Traits\ddp\DdpTrait;
use App\Entity\ddp\HistoriqueStatutDdp;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\ddp\DemandePaiementLigne;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\ddp\DocDemandePaiement;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;
use App\Repository\admin\ddp\TypeDemandeRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\cde\CdefnrSoumisAValidationRepository;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DemandePaiementController extends Controller
{
    use DdpTrait;
    use AutorisationTrait;

    const STATUT_CREATION = 'Soumis à validation';

    private TypeDemandeRepository $typeDemandeRepository;
    private DemandePaiementModel $demandePaiementModel;
    private CdefnrSoumisAValidationRepository $cdeFnrRepository;
    private DemandePaiementRepository $demandePaiementRepository;
    private DemandePaiementLigne $demandePaiementLigne;
    private HistoriqueOperationDDPService $historiqueOperation;
    private GeneratePdfDdp $generatePdfDdp;
    private DocDemandePaiement $docDemandePaiement;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private $agenceRepository;
    private $serviceRepository;

    public function __construct()
    {
        parent::__construct();

        $this->typeDemandeRepository = $this->getEntityManager()->getRepository(TypeDemande::class);
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->cdeFnrRepository = $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class);
        $this->demandePaiementRepository  = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $this->demandePaiementLigne = new DemandePaiementLigne();
        $this->historiqueOperation = new HistoriqueOperationDDPService($this->getEntityManager());
        $this->generatePdfDdp = new GeneratePdfDdp();
        $this->docDemandePaiement = new DocDemandePaiement();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $this->agenceRepository = $this->getEntityManager()->getRepository(Agence::class);
        $this->serviceRepository = $this->getEntityManager()->getRepository(Service::class);
    }

    /**
     * @Route("/new/{id}", name="demande_paiement")
     */
    public function afficheForm(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_DDP);
        /** FIN AUtorisation acées */

        // creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DemandePaiementType::class, null, ['id_type' => $id])->getForm();

        // traitement du formulaire
        $this->traitementForm($request, $form, $id);

        return $this->render('ddp/demandePaiementNew.html.twig', [
            'id_type' => $id,
            'form' => $form->createView()
        ]);
    }

    private function traitementForm(Request $request, $form, int $id): void
    {

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numDdp = $this->autoINcriment('DDP'); // decrementation du numero DDP
            $this->modificationDernierIdApp($numDdp); //modification de la dernière numero DDP
            $data = $form->getData(); //recupération des donnnées

            // $numCdes = $this->recuperationCdeFacEtNonFac($id);
            $numCdes = $this->demandePaiementModel->getCommandeReceptionnee($data->getNumeroFournisseur());
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
            $numFacString = TableauEnStringService::TableauEnString(',', $data->getNumeroFacture());
            $numeroCommandes = $this->demandePaiementModel->getNumCommande($data->getNumeroFournisseur(), $numCdesString, $numFacString);

            /** TRAITEMENT FICHIER  AUTRE DOCUMENT ET BC client externe / BC client magasin*/
            if ($data->getPieceJoint04() != null) {
                $data->setEstAutreDoc(true)
                    ->setNomAutreDoc($data->getPieceJoint04()->getClientOriginalName())
                ;
            }

            if ($data->getPieceJoint03() != null || !empty($data->getPieceJoint03())) {
                $nomFichierBCs = [];
                foreach ($data->getPieceJoint03() as $value) {
                    $nomFichierBCs[] = $value->getClientOriginalName();
                }
                $data->setEstCdeClientExterneDoc(true)
                    ->setNomCdeClientExterneDoc($nomFichierBCs)
                ;
            }

            /** ENREGISTREMENT DU FICHIER */
            $nomDesFichiers = $this->enregistrementFichier($form, $numDdp);
            if ($id == 2) {
                $data->setNumeroCommande($numeroCommandes);
            }
            $nomDufichierCde = $this->recupCdeDw($data, $numDdp, 1); //recupération de fichier cde dans DW
            /** AJOUT DES INFO NECESSAIRE  A L'ENTITE DDP */
            $this->ajoutDesInfoNecessaire($data, $numDdp, $id, $nomDesFichiers, $nomDufichierCde);

            /** ENREGISTREMENT DANS BD */
            $this->EnregistrementBdDdp($data); // enregistrement des données dans la table demande_paiement
            $this->EnregistrementBdDdpl($data); // enregistrement des données dans la table demande_paiement_ligne
            $this->enregisterDdpF($data); // enregistrement des données dans la table doc_demande_paiement
            $this->enregistrementBdHistoriqueStatut($data); // enregistrement des données dans la table historique_statut_ddp

            /** COPIER LES FICHIERS DISTANT 192.168.0.15 vers uplode/ddp/... */
            if ($id == 2) {
                $this->copierFichierDistant($data, $numDdp);
            }

            /** GENERATION DE PDF */
            $nomPageDeGarde = $numDdp . '.pdf';
            $cheminEtNom = $this->cheminDeBase . '/' . $numDdp . '_New_1/' . $nomPageDeGarde;
            $this->generatePdfDdp->genererPDF($data, $cheminEtNom);

            /** FUSION DES PDF */
            $nomFichierAvecChemin = $this->addPrefixToElementArray($data->getLesFichiers(), $this->cheminDeBase . '/' . $numDdp . '_New_1/');
            $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemin);
            $tousLesFichersAvecChemin = $this->traitementDeFichier->insertFileAtPosition($fichierConvertir, $cheminEtNom, 0);
            $this->traitementDeFichier->fusionFichers($tousLesFichersAvecChemin, $cheminEtNom);

            /** ENVOYER DANS DW */
            // $this->generatePdfDdp->copyToDwDdp($nomPageDeGarde, $numDdp, '1');

            /** HISTORISATION */
            $this->historiqueOperation->sendNotificationSoumission('Le document a été généré avec succès', $numDdp, 'ddp_liste', true);
        }
    }


    private function enregistrementBdHistoriqueStatut(DemandePaiement $data): void
    {
        $historiqueStatutDdp = new HistoriqueStatutDdp();
        $historiqueStatutDdp
            ->setNumeroDdp($data->getNumeroDdp())
            ->setStatut($data->getStatut())
            ->setDate(new \DateTime())
        ;

        $this->getEntityManager()->persist($historiqueStatutDdp);
        $this->getEntityManager()->flush();
    }


    /**
     * Decrementation de Numero_Applications (DOMAnnéeMoisNuméro)
     *
     * @param string $nomDemande
     * @return string
     */
    protected function autoDecrementDDP(string $nomDemande): string
    {
        //NumDOM auto
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        //$MonthOfcours = "08"; //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        //var_dump($AnneMoisOfcours);
        // dernier NumDOM dans la base

        //$Max_Num = $this->casier->RecupereNumCAS()['numCas'];

        if ($nomDemande === 'DDP') {
            $Max_Num = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP'])->getDerniereId();
        } else {
            $Max_Num = $nomDemande . $AnneMoisOfcours . '9999';
        }

        //var_dump($Max_Num);
        //$Max_Num = 'CAS24040000';
        //num_sequentielless
        $vNumSequential =  substr($Max_Num, -4); // lay 4chiffre msincrimente
        //dump($vNumSequential);
        $DateAnneemoisnum = substr($Max_Num, -8);
        //dump($DateAnneemoisnum);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        //dump($DateYearsMonthOfMax);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential - 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 9999;
            }
        }

        //dump($vNumSequential);
        //var_dump($vNumSequential);
        $Result_Num = $nomDemande . $AnneMoisOfcours . $vNumSequential;
        //var_dump($Result_Num);
        //dd($Result_Num);
        return $Result_Num;
    }

    private function ConvertirLesPdf(array $tousLesFichersAvecChemin)
    {
        $tousLesFichiers = [];
        foreach ($tousLesFichersAvecChemin as $filePath) {
            $tousLesFichiers[] = $this->convertPdfWithGhostscript($filePath);
        }

        return $tousLesFichiers;
    }


    private function convertPdfWithGhostscript($filePath)
    {
        $gsPath = 'C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe'; // Modifier selon l'OS
        $tempFile = $filePath . "_temp.pdf";

        // Vérifier si le fichier existe et est accessible
        if (!file_exists($filePath)) {
            throw new Exception("Fichier introuvable : $filePath");
        }

        if (!is_readable($filePath)) {
            throw new Exception("Le fichier PDF ne peut pas être lu : $filePath");
        }

        // Commande Ghostscript
        $command = "\"$gsPath\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$tempFile\" \"$filePath\"";
        // echo "Commande exécutée : $command<br>";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Sortie Ghostscript : " . implode("\n", $output);
            throw new Exception("Erreur lors de la conversion du PDF avec Ghostscript");
        }

        // Remplacement du fichier
        if (!rename($tempFile, $filePath)) {
            throw new Exception("Impossible de remplacer l'ancien fichier PDF.");
        }

        return $filePath;
    }





    /**
     * Ajout de suffix pour chaque element du tableau files
     *
     * @param array $files
     * @param string $suffix
     * @return array
     */
    private function addSuffixToElementArray(array $files, string $suffix): array
    {
        return array_map(function ($file) use ($suffix) {
            return $file . $suffix;
        }, $files);
    }

    /**
     * Ajout de prefix pour chaque element du tableau files
     *
     * @param array $files
     * @param string $prefix
     * @return array
     */
    private function addPrefixToElementArray(array $files, string $prefix): array
    {
        return array_map(function ($file) use ($prefix) {
            return $prefix . $file;
        }, $files);
    }

    /**
     * Enregistrement des fichiers téléchagrer dans le dossier de destination
     *
     * @param [type] $form
     * @return array
     */
    private function enregistrementFichier($form, $numDdp): array
    {
        $nomDesFichiers = [];
        $fieldPattern = '/^pieceJoint(\d{2})$/';

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile[]|null $file */
                $file = $field->getData();

                if ($file !== null) {
                    if (is_array($file)) {
                        // Cas où c'est un tableau de fichiers
                        foreach ($file as $singleFile) {
                            if ($singleFile !== null) {
                                $nomDeFichier = $singleFile->getClientOriginalName();
                                $this->traitementDeFichier->upload(
                                    $singleFile,
                                    $this->cheminDeBase . '/' . $numDdp . '_New_1',
                                    $nomDeFichier
                                );
                                $nomDesFichiers[] = $nomDeFichier;
                            }
                        }
                    } else {
                        // Cas où c'est un seul fichier
                        $nomDeFichier = $file->getClientOriginalName();
                        $this->traitementDeFichier->upload(
                            $file,
                            $this->cheminDeBase . '/' . $numDdp . '_New_1',
                            $nomDeFichier
                        );
                        $nomDesFichiers[] = $nomDeFichier;
                    }
                }
            }
        }

        return $nomDesFichiers;
    }

    private function ajoutDesInfoNecessaire(DemandePaiement $data, string $numDdp, int $id, array $nomDesFichiers, array $cheminDufichierCde)
    {
        $data = $this->ajoutTypeDemande($data, $id);

        $numDossierDouanne = $this->recupNumDossierDouane($data);

        $lesFichiers = $this->ajoutDesFichiers($data, $nomDesFichiers);
        $nomDefichierFusionners = array_merge($lesFichiers, $cheminDufichierCde);

        $data
            ->setNumeroDdp($numDdp) // ajout du numero DDP dans l'entity DDP
            // ->setAgenceDebiter($data->getAgence()->getCodeAgence())
            // ->setServiceDebiter($data->getService()->getCodeService())
            ->setAgenceDebiter($this->agenceRepository->find(1)->getCodeAgence())
            ->setServiceDebiter($this->serviceRepository->find(1)->getCodeService())
            ->setAdresseMailDemandeur($this->getUserMail())
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setStatut(self::STATUT_CREATION)
            ->setNumeroVersion('1')
            ->setMontantAPayers((float)$this->transformChaineEnNombre($data->getMontantAPayer()))
            ->setLesFichiers($nomDefichierFusionners)
            ->setNumeroDossierDouane($numDossierDouanne)
        ;
    }

    /**
     * Copie des fichiers dans un serveur '192.168.0.15' dans le repertoire uplode/ddp/fichiers
     *
     * @param DemandePaiement $data
     * @return void
     */
    private function copierFichierDistant(DemandePaiement $data, $numDdp): void
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $cheminDeFichiers = $this->recupCheminFichierDistant($data);
        $cheminDestination = $chemin . '/' . $numDdp . '_New_1';

        // S'assurer que le répertoire de destination existe
        if (!is_dir($cheminDestination)) {
            mkdir($cheminDestination, 0777, true);
        }

        foreach ($cheminDeFichiers as $cheminDeFichier) {
            // Vérifier si le fichier source existe et est lisible avant de continuer
            if (file_exists($cheminDeFichier) && is_readable($cheminDeFichier)) {
                $nomFichier = $this->nomFichier($cheminDeFichier);
                $destinationFinal = $cheminDestination . '/' . $nomFichier;
                
                // Copier le fichier et vérifier le succès (le ! supprime l'avertissement en cas d'échec)
                @copy($cheminDeFichier, $destinationFinal);
            }
        }
    }

    private function enregisterDdpF(DemandePaiement $data): void
    {
        $donners = $this->recuperationDonnerDdpF($data);
        foreach ($donners as $value) {
            $this->getEntityManager()->persist($value);
        }

        $this->getEntityManager()->flush();
    }

    private function ajoutDesFichiers(DemandePaiement $data, array $fichierTelechargerName): array
    {
        $lesCheminsFichiers = $this->recupCheminFichierDistant($data);

        $lesFichiers = [];
        foreach ($lesCheminsFichiers as $value) {
            $lesFichiers[] = $this->nomFichier($value);
        }

        $ensembleDesNomDeFichiers = array_merge($lesFichiers, $fichierTelechargerName);

        return $ensembleDesNomDeFichiers;
    }

    /**
     * Récupération de numero de dossier de douane
     *
     * @param DemandePaiement $data
     * @return array
     */
    private function recupNumDossierDouane(DemandePaiement $data): array
    {
        $numFrs = $data->getNumeroFournisseur();
        $numCde = $data->getNumeroCommande();

        $numFactures = $data->getNumeroFacture();

        $numCdesString = TableauEnStringService::TableauEnString(',', $numCde);
        $numFactString = TableauEnStringService::TableauEnString(',', $numFactures);

        $numDossiers = array_column($this->demandePaiementModel->getNumDossierGcot($numFrs, $numCdesString, $numFactString), 'Numero_Dossier_Douane');

        return $numDossiers;
    }

    /**
     * Recupération des chemins des fichiers distant 192.168.0.15
     *
     * @param DemandePaiement $data
     * @return array
     */
    private function recupCheminFichierDistant(DemandePaiement $data): array
    {
        $numDossiers = $this->recupNumDossierDouane($data);

        $cheminDeFichiers = [];
        foreach ($numDossiers as $value) {
            $dossiers = $this->demandePaiementModel->findListeDoc($value);

            foreach ($dossiers as  $dossier) {
                $cheminDeFichiers[] = $dossier['Nom_Fichier'];
            }
        }

        return $cheminDeFichiers;
    }

    private function recuperationDonnerDdpF(DemandePaiement $data): array
    {
        $numDdp = $data->getNumeroDdp();
        $cheminDeFichiers = $this->recupCheminFichierDistant($data);

        $donners = [];
        foreach ($cheminDeFichiers as $cheminDeFichier) {
            $nomFichier = $this->nomFichier($cheminDeFichier);
            $docDemandePaiement = new DocDemandePaiement();
            $donners[] = $docDemandePaiement
                ->setNumeroDdp($numDdp)
                ->setTypeDocumentId($data->getTypeDemandeId())
                ->setNomFichier($nomFichier)
                ->setNumeroVersion('1');
        }

        return $donners;
    }

    private function nomFichier(string $cheminFichier): string
    {
        $motExacteASupprimer = [
            '\\\\192.168.0.15',
            '\\GCOT_DATA',
            '\\TRANSIT',
        ];

        $motCommenceASupprimer = ['\\DD'];

        return $this->enleverPartiesTexte($cheminFichier, $motExacteASupprimer, $motCommenceASupprimer);
    }

    private function enleverPartiesTexte(string $texte, array $motsExacts, array $motsCommencent): string
    {
        // Supprimer les correspondances exactes
        foreach ($motsExacts as $mot) {
            $texte = str_replace($mot, '', $texte);
        }

        // Supprimer les parties qui commencent par un mot donné
        foreach ($motsCommencent as $motDebut) {
            $pattern = '/' . preg_quote($motDebut, '/') . '[^\\\\]*/';
            $texte = preg_replace($pattern, '', $texte);
        }

        // Supprimer les éventuels slashes de début
        return ltrim($texte, '\\/');
    }


    private function transformChaineEnNombre(string $nombre): float
    {
        $nombre = str_replace(' ', '', $nombre); // Supprimer les espaces
        $nombre = str_replace(',', '.', $nombre); // Remplacer la virgule par un point pour le format décimal

        $nombre_formaté = number_format((float)$nombre, 2, '.', ''); // Conversion en float et formatage
        return  $nombre_formaté; // Affiche : 11124522.46
    }

    private function EnregistrementBdDdpl($data): void
    {
        $demandePaiementLigne = $this->recuperationDonnerDdpl($data);

        if (count($demandePaiementLigne) > 1) {
            foreach ($demandePaiementLigne as $value) {
                $this->getEntityManager()->persist($value);
            }
        } else {
            $this->getEntityManager()->persist($demandePaiementLigne[0]);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * recupération des donner à ajoutrer dans la table demande_paiement_ligne
     *
     * @param  DemandePaiement $data
     * @return array
     */
    private function recuperationDonnerDdpl(DemandePaiement $data): array
    {
        $demandePaiementLignes = [];

        for ($i = 0; $i < count($data->getNumeroCommande()); $i++) {
            $demandePaiementLigne = new DemandePaiementLigne();
            $demandePaiementLignes[] = $demandePaiementLigne
                ->setNumeroDdp($data->getNumeroDdp())
                ->setNumeroLigne($i + 1)
                ->setNumeroCommande($data->getNumeroCommande()[$i])
                ->setNumeroFacture(
                    is_array($data->getNumeroFacture()) && array_key_exists($i, $data->getNumeroFacture())
                        ? $data->getNumeroFacture()[$i]
                        : '-'
                )
                ->setMontantFacture($this->transformChaineEnNombre($data->getMontantAPayer()))
                ->setNumeroVersion('1');
        }

        return $demandePaiementLignes;
    }



    /**
     * methode qui permet d'enregestrer les données dans la table demande_paiement
     */
    private function EnregistrementBdDdp(DemandePaiement $data): void
    {
        $this->getEntityManager()->persist($data);
        $this->getEntityManager()->flush();
    }

    /**
     * modification du dernier id de l'application dans la table application
     *
     * @param string $numDdp
     * @return void
     */
    private function modificationDernierIdApp(string $numDdp): void
    {
        $application = $this->getEntityManager()->getRepository(Application::class)->findOneBy(['codeApp' => 'DDP']);
        $application->setDerniereId($numDdp);
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }

    /**
     * Permet d'ajouter l'entité type de demande dans l'entité Demande de paiement
     *
     * @param DemandePaiement $data
     * @param integer $id
     * @return DemandePaiement
     */
    private function ajoutTypeDemande(DemandePaiement $data, int $id): DemandePaiement
    {
        $typeDemande = $this->typeDemandeRepository->find($id);
        return  $data->setTypeDemandeid($typeDemande);
    }

    // private function recupererNumCdeFournisseur($numeroFournisseur)
    // {
    //     $nbrLigne = $this->demandePaiementRepository->CompteNbrligne($numeroFournisseur);

    //     if ($nbrLigne <= 0) {
    //         $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur);
    //         $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

    //         $data = [
    //             'numCdes' => $numCdes,
    //         ];
    //     } 
    // }
}
