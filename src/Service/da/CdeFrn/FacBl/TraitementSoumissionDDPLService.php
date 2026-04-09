<?php

namespace App\Service\da\CdeFrn\FacBl;

use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DaAfficher;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlFactory;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\da\DaAfficherRepository;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Service\historiqueOperation\HistoriqueOperationDaFacBlService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TraitementSoumissionDDPLService
{
    use PdfConversionTrait;

    private DaSoumissionFacBlFactory $daSoumissionFacBlFactory;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBase;
    private string $cheminDeBaseDdp;
    private HistoriqueOperationDaFacBlService $historiqueOperation;
    private DaAfficherRepository $daAfficherRepository;
    private DaSoumissionFacBlMapper $daSoumissionfacBlMapper;
    private GeneratePdfDdp $generatePdfDdp;
    private GeneratePdf $generatePdf;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager                  = $entityManager;
        $this->daSoumissionFacBlFactory       = new DaSoumissionFacBlFactory($this->entityManager);
        $this->traitementDeFichier            = new TraitementDeFichier();
        $this->cheminDeBase                   = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->cheminDeBaseDdp                = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $this->historiqueOperation            = new HistoriqueOperationDaFacBlService($this->entityManager);
        $this->daAfficherRepository           = $this->entityManager->getRepository(DaAfficher::class);
        $this->daSoumissionfacBlMapper        = new DaSoumissionFacBlMapper();
        $this->generatePdfDdp                 = new GeneratePdfDdp();
        $this->generatePdf                    = new GeneratePdf();
    }

    public function traitementSoumissionDDPL($form, $dto)
    {
        $sucess = false;
        if ($this->verifierConditionDeBlocage($dto)) {
            $numCde  = $dto->numeroCde;
            $numDa   = $dto->numeroDemandeAppro;

            // Traitement du fichier
            [$nomAvecCheminPdfFusionner, $nomPdfFusionner] = $this->traitementDeFichier($form, $dto);

            // enrichissement Dto
            $dto  = $this->daSoumissionFacBlFactory->enrichissementDtoApresSoumission($dto, $nomPdfFusionner);

            /** ENREGISTREMENT DANS LA BASE DE DONNEE */
            $daSoumissionFacBl = $this->daSoumissionfacBlMapper->mapDaDdp($dto);
            $this->entityManager->persist($daSoumissionFacBl);
            $this->entityManager->flush();

            /** COPIER DANS DW */
            $this->generatePdf->copyToDWFacBlDa($nomPdfFusionner, $numDa);

            /** MODIFICATION DA AFFICHER */
            $this->modificationDaAfficher($numDa, $numCde);

            // generation de demande de paiement
            $this->traitementPourDdp($dto, $nomAvecCheminPdfFusionner);

            $sucess = true;
        }
        return $sucess;
    }

    private function traitementPourDdp($dto, string $nomAvecCheminPdfFusionner)
    {
        // GENERATION DE PDF pour le demnade de paiement
        $nomPageDeGarde = $dto->numeroDdp . '.pdf';
        $cheminEtNom = $this->cheminDeBaseDdp . '/' . $dto->numeroDdp . '_New_1/' . $nomPageDeGarde;
        $this->generatePdfDdp->genererPdfDto($dto, $cheminEtNom);

        // fusion du page de garde du demande de paiement et le facture Bl
        $pdfAFusionner = [$cheminEtNom, $nomAvecCheminPdfFusionner];
        $fichierConvertir = $this->ConvertirLesPdf($pdfAFusionner);
        $this->traitementDeFichier->fusionFichers($fichierConvertir, $cheminEtNom);

        // enregisstrement dans la table demande de paiement
        $ddp = DemandePaiementMapper::map($dto);
        $this->entityManager->persist($ddp);
        $this->entityManager->flush();
    }

    private function traitementDeFichier($form, $dto): array
    {
        $numCde  = $dto->numeroCde;
        $numDa   = $dto->numeroDemandeAppro;
        $numOr   = $dto->numeroOR;
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        /** ENREGISTREMENT DE FICHIER */
        $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

        /** AJOUT DES CHEMINS DANS LE TABLEAU */
        $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBase . $numDa . '/');

        /** CONVERTIR LES PDF */
        $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);

        /** GENERATION DU NOM DU FICHIER */
        $numeroVersionMax          = $dto->numeroVersion;
        $nomPdfFusionner           =  "FACBL$numCde#$numDa-{$numOr}_{$numeroVersionMax}~{$nomOriginalFichier}";
        $nomAvecCheminPdfFusionner = $this->cheminDeBase . $numDa . '/' . $nomPdfFusionner;

        /** FUSION DES PDF */
        $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

        return [$nomAvecCheminPdfFusionner, $nomPdfFusionner];
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
    private function enregistrementFichier($form, $numCde, $numDa): array
    {
        $fieldPattern = '/^pieceJoint(\d{1})$/';
        $nomDesFichiers = [];
        $compteur = 1; // Pour l’indexation automatique

        foreach ($form->all() as $fieldName => $field) {
            if (preg_match($fieldPattern, $fieldName, $matches)) {
                /** @var UploadedFile|UploadedFile[]|null $file */
                $file = $field->getData();

                if ($file !== null) {
                    $fichiers = is_array($file) ? $file : [$file];

                    foreach ($fichiers as $singleFile) {
                        if ($singleFile !== null) {
                            // Ensure $singleFile is an instance of Symfony's UploadedFile
                            if (!$singleFile instanceof UploadedFile) {
                                throw new \InvalidArgumentException('Expected instance of Symfony\Component\HttpFoundation\File\UploadedFile.');
                            }

                            $extension = $singleFile->guessExtension() ?? $singleFile->getClientOriginalExtension();
                            $nomDeFichier = sprintf('FACBL_%s-%04d.%s', $numCde, $compteur, $extension);

                            $this->traitementDeFichier->upload(
                                $singleFile,
                                $this->cheminDeBase . '/' . $numDa,
                                $nomDeFichier
                            );

                            $nomDesFichiers[] = $nomDeFichier;
                            $compteur++;
                        }
                    }
                }
            }
        }

        return $nomDesFichiers;
    }

    /**
     * Modification du colonne est_facture_bl_soumis dans la table da_afficher
     *
     * @param string $numDa
     * @param int $numeroVersionMax
     */
    private function modificationDaAfficher(string $numDa, string $numCde): void
    {
        $numeroVersionMax = $this->entityManager->getRepository(DaAfficher::class)->getNumeroVersionMax($numDa);
        $daAffichers = $this->entityManager->getRepository(DaAfficher::class)->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax, 'numeroCde' => $numCde]);

        foreach ($daAffichers as  $daAfficher) {
            if (!$daAfficher instanceof DaAfficher) {
                throw new Exception('Erreur: L\'objet DaAfficher est invalide.');
            }
            $daAfficher->setEstFactureBlSoumis(true);
            $this->entityManager->persist($daAfficher);
        }
        $this->entityManager->flush();
    }

    private function verifierConditionDeBlocage(DaSoumissionFacBlDto $dto): bool
    {
        $numCde = $dto->numeroCde;
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        $nonReceptionnes = array_filter($dto->receptions, function ($item) {
            return $item->statutRecep === 'Non receptionnee';
        });

        $message = '';
        $okey = true;

        // Blocage si le nom de fichier contient des caractères spéciaux
        if (preg_match('/[#\-_~]/', $nomOriginalFichier)) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";
            $okey = false;
        } elseif (!empty($nonReceptionnes)) {
            $message = " il y des quantités non réceptionné sur la commande a fait objet d'une demande de paiement à l'avance (non refusé) ";
            $okey = false;
        }

        if (!$okey) $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');

        return $okey;
    }
}
