<?php

namespace App\Service\da\CdeFrn\FacBl;

use App\Constants\da\ddp\BonApayerConstants;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DaAfficher;
use App\Entity\da\DaSoumissionFacBl;
use App\Entity\da\DemandeAppro;
use App\Entity\dw\DwBcAppro;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlFactory;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Mapper\ddp\CommandeLivraisonMapper;
use App\Mapper\ddp\DemandePaiementCommandeMapper;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Model\da\DaSoumissionFacBlModel;
use App\Model\dit\DitModel;
use App\Repository\da\DaSoumissionFacBlRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\dw\DwBcApproRepository;
use App\Service\dataPdf\ordreReparation\Recapitulation;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\bap\GenererPdfBonAPayer;
use App\Service\genererPdf\GeneratePdf;
use App\Service\historiqueOperation\HistoriqueOperationDaBcService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TraitementSoumissionBAPService
{
    use PdfConversionTrait;

    private EntityManagerInterface $entityManager;
    private HistoriqueOperationDaBcService $historiqueOperation;
    private DaSoumissionFacBlRepository $daSoumissionFacBlRepository;
    private DaSoumissionFacBlFactory $daSoumissionFacBlFactory;
    private DaSoumissionFacBlMapper $daSoumissionfacBlMapper;
    private GeneratePdf $generatePdf;
    private DaSoumissionFacBlModel $daSoumissionFacBlModel;
    private TraitementDeFichier $traitementDeFichier;
    private string $cheminDeBaseDa;
    private DemandeApproRepository $demandeApproRepository;
    private DwBcApproRepository $dwBcApproRepository;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->historiqueOperation         = new HistoriqueOperationDaBcService($this->entityManager);
        $this->daSoumissionFacBlRepository = $this->entityManager->getRepository(DaSoumissionFacBl::class);
        $this->daSoumissionFacBlFactory    = new DaSoumissionFacBlFactory($this->entityManager);
        $this->daSoumissionfacBlMapper     = new DaSoumissionFacBlMapper();
        $this->generatePdf                 = new GeneratePdf();
        $this->daSoumissionFacBlModel      = new DaSoumissionFacBlModel();
        $this->traitementDeFichier         = new TraitementDeFichier();
        $this->cheminDeBaseDa              = $_ENV['BASE_PATH_FICHIER'] . '/da/';
        $this->demandeApproRepository      = $this->entityManager->getRepository(DemandeAppro::class);
        $this->dwBcApproRepository         = $this->entityManager->getRepository(DwBcAppro::class);
    }

    public function traitementSoumissionBAP($form, $dto, ?string $mail)
    {
        $sucess = false;

        if ($this->verifierConditionDeBlocage($dto)) {
            $numCde  = $dto->numeroCde;
            $numDa   = $dto->numeroDemandeAppro;
            $numLiv = $dto->numLiv;

            // Traitement du fichier
            [$nomAvecCheminPdfFusionner, $nomPdfFusionner] = $this->traitementDeFichier($form, $dto, $mail);

            // enrichissement Dto
            $dto  = $this->daSoumissionFacBlFactory->EnrichissementDtoApresSoumission($dto, $nomPdfFusionner);

            /** ENREGISTREMENT DANS LA BASE DE DONNEE */
            $this->enregistrementDansBD($dto);

            /** COPIER DANS DW */
            $this->generatePdf->copyToDWFacBlDa($nomPdfFusionner, $numDa);

            /** MODIFICATION DA AFFICHER */
            $this->modificationDaAfficher($numDa, $numCde, $numLiv);



            $sucess = true;
        }
        return $sucess;
    }

    public function enregistrementDansBD(DaSoumissionFacBlDto $dto)
    {
        // enregistrement dans la table da_soumission_fac_bl
        $daSoumissionFacBl = DaSoumissionFacBlMapper::mapBap($dto);
        $this->entityManager->persist($daSoumissionFacBl);
        $this->entityManager->flush();

        // enregistrement dans la table demande_paiement
        $ddp = DemandePaiementMapper::mapBap($dto->demandePaiementDto);
        $this->entityManager->persist($ddp);
        $this->entityManager->flush();

        // enregistremenet dans la table demande_paiement_commande
        $ddpCommande = DemandePaiementCommandeMapper::map($dto->demandePaiementDto, $ddp);
        $this->entityManager->persist($ddpCommande);
        $this->entityManager->flush();

        // enregistremenet dans la table commande_livraison
        $commande_livraison = CommandeLivraisonMapper::map($dto->demandePaiementDto, $ddp);
        $this->entityManager->persist($commande_livraison);
        $this->entityManager->flush();
    }

    /**
     * Vérifier les conditions de blocage avant la soumission du document 
     * dans DocuWare pour le demande paiement pas de demande de 
     * paiement à l'avance
     *
     * @param DaSoumissionFacBlDto $dto
     * @return boolean
     */
    private function verifierConditionDeBlocage(DaSoumissionFacBlDto $dto): bool
    {
        $numCde = $dto->numeroCde;
        $numLiv = $dto->numLiv;
        $mttFac = $dto->montantBlFacture;
        $infoLivraison = $dto->infoLiv[$numLiv];

        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        $mttFacFormate = $mttFac ? (float)str_replace(',', '.', str_replace(' ', '', $mttFac)) : 0.0;

        $message = '';
        $okey = true;

        // Blocage si la livraison n'est pas clôturée
        if (!empty($infoLivraison) && isset($infoLivraison['date_clot']) && $infoLivraison['date_clot'] === null) {
            $message = "La livraison n° '$numLiv' associée à la commande n° '$numCde' n'est pas encore clôturée. Merci de clôturer la livraison avant de soumettre le document dans DocuWare.";
            $okey = false;
        }
        // Blocage si le nom de fichier contient des caractères spéciaux
        elseif (preg_match('/[#\-_~]/', $nomOriginalFichier)) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";
            $okey = false;
        }
        // Blocage si montant ne correspond pas au montant de la livraison dans IPS
        elseif ($dto->montantAregulariser > 0.0 && $mttFacFormate !== (float) $infoLivraison['montant_fac_bl']) {
            $message = "Le montant de la facture <b>{$mttFac}</b> ne correspond pas au montant de la livraison dans IPS. Merci de vérifier le montant de la facture avant de le soumettre dans DocuWare.";
            $okey = false;
        }
        // Blocage si le type de demande de paiement n'est pas régularisation mais le montant à payer est égal à 0
        elseif ($dto->typeDdp !== 'regul' && $dto->montantAregulariser <= 0.0) {
            $message = " le type de traitement de paiement doit être régularisation car le montant à payer est égal à 0 ";
            $okey = false;
        }

        if (!$okey) $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');

        return $okey;
    }

    /**
     * Modification du colonne est_facture_bl_soumis dans la table da_afficher
     *
     * @param string $numDa
     * @param int $numeroVersionMax
     */
    private function modificationDaAfficher(string $numDa, string $numCde, $numLiv): void
    {
        $daAfficherRepository = $this->entityManager->getRepository(DaAfficher::class);
        $numeroVersionMax = $daAfficherRepository->getNumeroVersionMax($numDa);
        $typeDa = $daAfficherRepository->getTypeDaSelonNumDa($numDa);
        $daAffichers = [];

        if (in_array((int)$typeDa, [DemandeAppro::TYPE_DA_AVEC_DIT, DemandeAppro::TYPE_DA_REAPPRO_MENSUEL, DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL])) {
            $refDesiSavLors = $this->daSoumissionFacBlModel->getRefDesiSavLor($numLiv);
            foreach ($refDesiSavLors as  $refDesiSavLor) {
                $daAffichers[] = $this->entityManager->getRepository(DaAfficher::class)
                    ->findOneBy(
                        [
                            'numeroDemandeAppro' => $numDa,
                            'numeroVersion' => $numeroVersionMax,
                            'numeroCde' => $numCde,
                            'artRefp' => $refDesiSavLor['reference'],
                            'artDesi' => $refDesiSavLor['designation']
                        ]
                    );
            }
        } else {
            $refDesiFrnCdls = $this->daSoumissionFacBlModel->getRefDesiFrnCdl($numLiv);
            foreach ($refDesiFrnCdls as  $refDesiFrnCdl) {
                $daAffichers[] = $this->entityManager->getRepository(DaAfficher::class)
                    ->findOneBy(
                        [
                            'numeroDemandeAppro' => $numDa,
                            'numeroVersion' => $numeroVersionMax,
                            'numeroCde' => $numCde,
                            'artRefp' => $refDesiFrnCdl['reference'],
                            'artDesi' => $refDesiFrnCdl['designation']
                        ]
                    );
            }
        }


        foreach ($daAffichers as  $daAfficher) {
            if (!$daAfficher instanceof DaAfficher) {
                throw new Exception('Erreur: L\'objet DaAfficher est invalide.');
            }
            $daAfficher->setEstFactureBlSoumis(true);
            $this->entityManager->persist($daAfficher);
        }
        $this->entityManager->flush();
    }

    private function traitementDeFichier($form, DaSoumissionFacBlDto $dto, ?string $mail): array
    {
        $numCde  = $dto->numeroCde;
        $numDa   = $dto->numeroDemandeAppro;
        $numOr   = $dto->numeroOR;
        $numLiv  = $dto->numLiv;
        $infoLiv = $dto->infoLiv[$numLiv];
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        /** ENREGISTREMENT DE FICHIER */
        $nomDeFichiers = $this->enregistrementFichier($form, $numCde, $numDa);

        /** AJOUT DES CHEMINS DANS LE TABLEAU */
        $nomFichierAvecChemins = $this->addPrefixToElementArray($nomDeFichiers, $this->cheminDeBaseDa . $numDa . '/');

        /** CREATION DE LA PAGE DE GARDE */
        $pageDeGarde = $this->genererPageDeGarde($infoLiv, $dto, $mail);

        /** AJOUT DE LA PAGE DE GARDE A LA PREMIERE POSITION */
        $nomFichierAvecChemins = $this->traitementDeFichier->insertFileAtPosition($nomFichierAvecChemins, $pageDeGarde, 0);

        /** CONVERTIR LES PDF */
        $fichierConvertir = $this->ConvertirLesPdf($nomFichierAvecChemins);

        /** GENERATION DU NOM DU FICHIER */
        $numeroVersionMax          = $dto->numeroVersionFacBl;
        $nomPdfFusionner           =  "FACBL$numCde#$numDa-{$numOr}_{$numeroVersionMax}~{$nomOriginalFichier}";
        $nomAvecCheminPdfFusionner = $this->cheminDeBaseDa . $numDa . '/' . $nomPdfFusionner;

        /** FUSION DES PDF */
        $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfFusionner);

        /** GENERATION DU DEUXIÈME NOM DU FICHIER  */
        $nomPdfSecond           = "BAP-$numCde#$numDa.pdf";
        $nomAvecCheminPdfSecond = $this->cheminDeBaseDa . $numDa . '/' . $nomPdfSecond;

        /** FUSION DU DEUXIÈME FICHIER */
        $this->traitementDeFichier->fusionFichers($fichierConvertir, $nomAvecCheminPdfSecond);

        return [$nomAvecCheminPdfFusionner, $nomPdfFusionner];
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
                                $this->cheminDeBaseDa . '/' . $numDa,
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

    private function genererPageDeGarde(array $infoLivraison, DaSoumissionFacBlDto $dto, ?string $mail): string
    {
        $ditModel         = new DitModel();
        $generatePdfBap   = new GenererPdfBonAPayer();
        $recapitulationOR = new Recapitulation();

        $numCde           = $dto->numeroCde;
        $numOr            = $dto->numeroOR;


        $infoValidationBC = $this->dwBcApproRepository->getInfoValidationBC($numCde) ?? [];
        $infoMateriel     = $ditModel->recupInfoMateriel($numOr);
        $dataRecapOR      = $recapitulationOR->getData($numOr);
        $demandeAppro     = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $dto->numeroDemandeAppro]);
        $infoFacBl        = [
            "refBlFac"   => $infoLivraison["ref_fac_bl"],
            "dateBlFac"  => $dto->dateBlFac,
            "numLivIPS"  => $infoLivraison["num_liv"],
            "dateLivIPS" => $infoLivraison["date_clot"],
        ];

        return $generatePdfBap->genererPageDeGarde($infoValidationBC, $infoMateriel, $dataRecapOR, $demandeAppro, $dto, $infoFacBl, $mail);
    }
}
