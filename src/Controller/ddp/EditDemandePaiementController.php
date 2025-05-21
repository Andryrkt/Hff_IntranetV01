<?php

namespace App\Controller\ddp;

use Exception;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Controller\Traits\ddp\DdpTrait;
use App\Entity\admin\Application;
use App\Entity\ddp\DemandePaiement;
use App\Entity\admin\ddp\TypeDemande;
use App\Form\ddp\DemandePaiementType;
use App\Entity\ddp\HistoriqueStatutDdp;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use App\Entity\ddp\DemandePaiementLigne;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Entity\admin\ddp\DocDemandePaiement;
use App\Service\fichier\TraitementDeFichier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ddp\DemandePaiementRepository;
use App\Repository\admin\ddp\TypeDemandeRepository;
use App\Repository\cde\CdefnrSoumisAValidationRepository;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;

class EditDemandePaiementController extends Controller
{
    use DdpTrait;

    const STATUT_MODIFICATION = 'mModification soumis à validation';

    private $cdeFnrRepository;
    private $demandePaiementModel;
    private string $cheminDeBase;
    private HistoriqueOperationDDPService $historiqueOperation;
    private GeneratePdfDdp $generatePdfDdp;
    private TraitementDeFichier $traitementDeFichier;
    private $agenceRepository;
    private $serviceRepository;
    private TypeDemandeRepository $typeDemandeRepository;
    private DemandePaiementRepository $ddpRepository;

    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->cdeFnrRepository = self::$em->getRepository(CdefnrSoumisAValidation::class);
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $this->historiqueOperation = new HistoriqueOperationDDPService();
        $this->generatePdfDdp = new GeneratePdfDdp();
        $this->traitementDeFichier = new TraitementDeFichier();
        $this->agenceRepository = Controller::getEntity()->getRepository(Agence::class);
        $this->serviceRepository = Controller::getEntity()->getRepository(Service::class);
        $this->typeDemandeRepository = self::$em->getRepository(TypeDemande::class);
        $this->ddpRepository = self::$em->getRepository(DemandePaiement::class);
    }

    /**
     * @Route("/edit-demande-paiement/{numDdp}/{numVersion}", name="edit_demande_paiement")
     */
    public function afficheEdit(Request $request, $numDdp, $numVersion)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $demandePaiement = $this->ddpRepository->findOneBy(['numeroDdp' => $numDdp, 'numeroVersion' => $numVersion]);
        $demandePaiement->setMontantAPayer($demandePaiement->getMontantAPayers());
        $demandePaiement = $demandePaiement->dupliquer();
        $id = $demandePaiement->getTypeDemandeId()->getId();
        $form = self::$validator->createBuilder(DemandePaiementType::class, $demandePaiement, ['id_type' => $id])->getForm();
        $this->traitementFormulaire($form, $request, $numDdp, $demandePaiement, $id);
        $numeroFournisseur = $demandePaiement->getNumeroFournisseur();
        $listeGcot = $this->listGcot($numeroFournisseur, $id);
        self::$twig->display('ddp/EditdemandePaiement.html.twig', [
            'id_type' => $id,
            'form' => $form->createView(),
            'listeGcot' => $listeGcot,
            'numDdp' => $numDdp
        ]);
    }

    public function traitementFormulaire($form, $request, $numDdp, $demandePaiement, $id)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); //recupération des donnnées

            $numeroversion = $this->autoIncrement($this->ddpRepository->findNumeroVersionMax($numDdp));
            /** ENREGISTREMENT DU FICHIER */
            $nomDesFichiers = $this->enregistrementFichier($form, $numDdp, $numeroversion);

            $numCdes = $this->recuperationCdeFacEtNonFac($demandePaiement->getTypeDemandeId()->getId());
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
            $numFacString = TableauEnStringService::TableauEnString(',', $data->getNumeroFacture());
            $numeroCommandes = $this->demandePaiementModel->getNumCommande($data->getNumeroFournisseur(), $numCdesString, $numFacString);

            if ($id == 2) {
                $data->setNumeroCommande($numeroCommandes);
            }
            $nomDufichierCde = $this->recupCdeDw($data, $numDdp, $numeroversion);
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
            /** AJOUT DES INFO NECESSAIRE  A L'ENTITE DDP */
            $this->ajoutDesInfoNecessaire($data, $numDdp, $demandePaiement->getTypeDemandeId()->getId(), $nomDesFichiers, $numeroversion, $nomDufichierCde);
            /** ENREGISTREMENT DANS BD */
            $this->EnregistrementBdDdp($data); // enregistrement des données dans la table demande_paiement
            $this->EnregistrementBdDdpl($data, $numeroversion); // enregistrement des données dans la table demande_paiement_ligne
            $this->enregisterDdpF($data, $numeroversion); // enregistrement des données dans la table doc_demande_paiement
            $this->enregistrementBdHistoriqueStatut($data); // enregistrement des données dans la table historique_statut_ddp
            /** COPIER LES FICHIERS */
            if ($demandePaiement->getTypeDemandeId()->getId() == 2) {
                $this->copierFichierDistant($data, $numDdp, $numeroversion);
            }
            /** GENERATION DE PDF */
            $nomPageDeGarde = $numDdp . '.pdf';
            $cheminEtNom = $this->cheminDeBase . '/' . $numDdp . '_New_' . $numeroversion . '/' . $nomPageDeGarde;
            $this->generatePdfDdp->genererPDF($data, $cheminEtNom);
            /** FUSION DES PDF */
            $nomFichierAvecChemin = $this->addPrefixToElementArray($data->getLesFichiers(), $this->cheminDeBase . '/' . $numDdp . '_New_' . $numeroversion . '/');
            $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemin);
            $tousLesFichersAvecChemin = $this->traitementDeFichier->insertFileAtPosition($fichierConvertir, $cheminEtNom, 0);
            $this->traitementDeFichier->fusionFichers($tousLesFichersAvecChemin, $cheminEtNom);
            /** ENVOYER DANS DW */
            $this->generatePdfDdp->copyToDwDdp($nomPageDeGarde, $numDdp, $numeroversion);
            /** HISTORISATION */
            $this->historiqueOperation->sendNotificationSoumission('Le document a été modifié avec succès', $numDdp, 'ddp_liste', true);
        }
    }




    /**
     * methode qui permet d'enregestrer les données dans la table demande_paiement
     */
    private function EnregistrementBdDdp(DemandePaiement $data): void
    {
        self::$em->persist($data);
        self::$em->flush();
    }

    private function EnregistrementBdDdpl($data, $numeroversion): void
    {
        $demandePaiementLigne = $this->recuperationDonnerDdpl($data, $numeroversion);

        if (count($demandePaiementLigne) > 1) {
            foreach ($demandePaiementLigne as $value) {
                self::$em->persist($value);
            }
        } else {
            self::$em->persist($demandePaiementLigne[0]);
        }

        self::$em->flush();
    }

    private function enregisterDdpF(DemandePaiement $data, $numeroversion): void
    {
        $donners = $this->recuperationDonnerDdpF($data, (int) $numeroversion);
        foreach ($donners as $value) {
            self::$em->persist($value);
        }

        self::$em->flush();
    }

    private function recuperationDonnerDdpF(DemandePaiement $data, int $numeroversion): array
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
                ->setNumeroVersion($numeroversion);
        }

        return $donners;
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

    /**
     * recupération des donner à ajoutrer dans la table demande_paiement_ligne
     *
     * @param  DemandePaiement $data
     * @return array
     */
    private function recuperationDonnerDdpl(DemandePaiement $data, int $numeroversion): array
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
                ->setNumeroVersion($numeroversion);
        }

        return $demandePaiementLignes;
    }
    private function enregistrementBdHistoriqueStatut(DemandePaiement $data): void
    {
        $historiqueStatutDdp = new HistoriqueStatutDdp();
        $historiqueStatutDdp
            ->setNumeroDdp($data->getNumeroDdp())
            ->setStatut($data->getStatut())
            ->setDate(new \DateTime())
        ;

        self::$em->persist($historiqueStatutDdp);
        self::$em->flush();
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

    public function listGcot($numeroFournisseur, $typeId)
    {
        // $numComandes = $this->ddpRepository->getnumCde();
        // $excludedCommands = $this->changeStringToArray($numComandes);
        // $numCdes = $this->cdeFnrRepository->findNumCommandeValideNonAnnuler($numeroFournisseur, $typeId, $excludedCommands);
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
        $listeGcot = $this->demandePaiementModel->findListeGcot($numeroFournisseur, $numCdesString);
        return $listeGcot;
    }

    private function changeStringToArray(array $input): array
    {

        $resultCde = [];

        foreach ($input as $item) {
            $decoded = json_decode($item, true); // transforme la string en tableau
            if (is_array($decoded)) {
                $resultCde = array_merge($resultCde, $decoded);
            }
        }

        return $resultCde;
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
                /** @var UploadedFile|UploadedFile[]|null $file */
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

    private function ajoutDesInfoNecessaire(DemandePaiement $data, string $numDdp, int $id, array $nomDesFichiers, int $numeroversion, array $cheminDufichierCde)
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
            ->setAdresseMailDemandeur($this->getEmail())
            ->setDemandeur($this->getUser()->getNomUtilisateur())
            ->setStatut(self::STATUT_MODIFICATION)
            ->setNumeroVersion($numeroversion)
            ->setMontantAPayers((float)$this->transformChaineEnNombre($data->getMontantAPayer()))
            ->setLesFichiers($nomDefichierFusionners)
            ->setNumeroDossierDouane($numDossierDouanne)
        ;
    }
    private function autoIncrement($num)
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }
    /**
     * Copie des fichiers dans un serveur '192.168.0.15' dans le repertoire uplode/ddp/fichiers
     *
     * @param DemandePaiement $data
     * @return void
     */
    private function copierFichierDistant(DemandePaiement $data, $numDdp, $numeroversion): void
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $cheminDeFichiers = $this->recupCheminFichierDistant($data);
        $cheminDestination = $chemin . '/' . $numDdp . '_New_' . $numeroversion;

        foreach ($cheminDeFichiers as $cheminDeFichier) {
            $nomFichier = $this->nomFichier($cheminDeFichier);
            $destinationFinal = $cheminDestination . '/' . $nomFichier;
            copy($cheminDeFichier, $destinationFinal);
        }
    }
    private function transformChaineEnNombre(string $nombre): float
    {
        $nombre = str_replace(' ', '', $nombre); // Supprimer les espaces
        $nombre = str_replace(',', '.', $nombre); // Remplacer la virgule par un point pour le format décimal

        $nombre_formaté = number_format((float)$nombre, 2, '.', ''); // Conversion en float et formatage
        return  $nombre_formaté; // Affiche : 11124522.46
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
}
