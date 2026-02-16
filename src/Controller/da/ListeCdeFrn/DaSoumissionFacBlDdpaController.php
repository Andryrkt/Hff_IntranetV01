<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use App\Entity\da\DaAfficher;
use App\Entity\ddp\DemandePaiement;
use App\Factory\da\CdeFrnDto\DaSoumissionFacBlDdpaFactory;
use App\Form\da\daCdeFrn\DaSoumissionFacBlDdpaType;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlDdpaMapper;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DaSoumissionFacBlDdpaController extends Controller
{
    use PdfConversionTrait;

    private DaSoumissionFacBlDdpaFactory $daSoumissionFacBlDdpaFactory;

    public function __construct()
    {
        $this->daSoumissionFacBlDdpaFactory = new DaSoumissionFacBlDdpaFactory($this->getEntityManager());
    }

    /**
     * @Route("/soumission-facbl-ddpa/{numCde}/{numDa}/{numOr}", name="da_soumission_facbl_ddpa", defaults={"numOr"=0})
     */
    public function index(int $numCde, ?string $numDa, ?int $numOr)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //initialisation 
        $dto = $this->daSoumissionFacBlDdpaFactory->initialisation($numCde, $numDa, $numOr, $this->getUserName());

        // creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DaSoumissionFacBlDdpaType::class, $dto, [
            'method'  => 'POST'
        ])->getForm();

        // recuperation des demandes de paiement déjà payer
        $ddpa = $this->getDdpa($numCde, $dto);

        $montant = $this->getMontant($numCde, $dto);

        return $this->render('da/soumissionFacBlDdpa.html.twig', [
            'form' => $form->createView(),
            'ddpa' => $ddpa,
            'montant' => $montant,
            'dto' => $dto
        ]);
    }

    public function TraitementFormualire(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DaSoumissionFacBlDdpaDto $dto */
            $dto = $form->getData();

            $numCde  = $dto->numeroCde;
            $numDa   = $dto->numeroDemandeAppro;

            if ($this->verifierConditionDeBlocage($dto)) {
                // Traitement du fichier
                [$nomAvecCheminPdfFusionner, $nomPdfFusionner] = $this->traitementDeFichier($form, $dto);

                // enrichissement Dto
                $dto  = $this->daSoumissionFacBlDdpaFactory->enrichissementDtoApresSoumission($dto, $nomPdfFusionner);
                /** ENREGISTREMENT DANS LA BASE DE DONNEE */
                $daSoumissionFacBl = $this->daSoumissionfacBlMapper->map($dto);
                $this->getEntityManager()->persist($daSoumissionFacBl);
                $this->getEntityManager()->flush();

                /** COPIER DANS DW */
                $this->generatePdf->copyToDWFacBlDa($nomPdfFusionner, $numDa);

                /** MODIFICATION DA AFFICHER */
                $this->modificationDaAfficher($numDa, $numCde);

                /** HISTORISATION */
                $message = 'Le document est soumis pour validation';
                $criteria = $this->getSessionService()->get('criteria_for_excel_Da_Cde_frn');
                $nomDeRoute = 'da_list_cde_frn'; // route de redirection après soumission
                $nomInputSearch = 'cde_frn_list'; // initialistion de nom de chaque champ ou input
                $this->historiqueOperation->sendNotificationSoumission($message, $numCde, $nomDeRoute, true, $criteria, $nomInputSearch);
            }
        }
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
        $numeroVersionMax = $this->getEntityManager()->getRepository(DaAfficher::class)->getNumeroVersionMax($numDa);
        $daAffichers = $this->getEntityManager()->getRepository(DaAfficher::class)->findBy(['numeroDemandeAppro' => $numDa, 'numeroVersion' => $numeroVersionMax, 'numeroCde' => $numCde]);

        foreach ($daAffichers as  $daAfficher) {
            if (!$daAfficher instanceof DaAfficher) {
                throw new Exception('Erreur: L\'objet DaAfficher est invalide.');
            }
            $daAfficher->setEstFactureBlSoumis(true);
            $this->getEntityManager()->persist($daAfficher);
        }
        $this->getEntityManager()->flush();
    }

    private function verifierConditionDeBlocage(DaSoumissionFacBlDdpaDto $dto): bool
    {
        $numCde = $dto->numeroCde;
        $nomOriginalFichier = $dto->pieceJoint1->getClientOriginalName();

        $message = '';
        $okey = true;

        // Blocage si le nom de fichier contient des caractères spéciaux
        if (preg_match('/[#\-_~]/', $nomOriginalFichier)) {
            $message = "Le nom de fichier ('{$nomOriginalFichier}') n'est pas valide. Il ne doit pas contenir les caractères suivants : #, -, _ ou ~. Merci de renommer votre fichier avant de le soumettre dans DocuWare.";
            $okey = false;
        }

        if (!$okey) $this->historiqueOperation->sendNotificationSoumission($message, $numCde, 'da_list_cde_frn');

        return $okey;
    }

    public function getDdpa(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $ddpRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);

        $ddpa = [];
        $runningCumul = 0; // Variable pour maintenir le total cumulé

        foreach ($ddps as  $ddp) {
            // Crée un nouveau DTO pour chaque élément afin d'avoir des objets distincts
            $itemDto = new DaSoumissionFacBlDdpaDto();

            // Copie les propriétés nécessaires du DTO initial qui sont communes à tous les éléments
            $itemDto->totalMontantCommande = $dto->totalMontantCommande;

            // Mappe l'entité vers le nouveau DTO (le mapper ne s'occupe plus du cumul)
            DaSoumissionFacBlDdpaMapper::mapDdp($itemDto, $ddp);

            // Calcule et définit la valeur cumulative ici dans la logique du contrôleur
            $runningCumul += $itemDto->ratio;
            $itemDto->cumul = $runningCumul;

            $ddpa[] = $itemDto;
        }

        return $ddpa;
    }

    public function getMontant(int $numCde, DaSoumissionFacBlDdpaDto $dto)
    {
        $montantpayer = 0;
        $ddpRepository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($numCde);
        foreach ($ddps as $item) {
            $montantpayer = $montantpayer + $item->getMontantAPayers();
        }

        $ratioTotalPayer = ($montantpayer / $dto->totalMontantCommande) * 100;

        $montantAregulariser = $dto->totalMontantCommande - $montantpayer;
        $ratioMontantARegul = ($montantAregulariser /  $dto->totalMontantCommande) * 100;

        $dto = DaSoumissionFacBlDdpaMapper::mapTotalPayer($dto, $montantpayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul);

        return $dto;
    }
}
