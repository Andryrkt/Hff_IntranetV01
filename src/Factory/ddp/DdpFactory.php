<?php

namespace App\Factory\ddp;

use App\Constants\ddp\StatutConstants;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Dto\ddp\DdpDto;
use App\Entity\admin\Agence;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\admin\Service;
use App\Entity\dw\DwCommande;
use App\Model\ddp\DdpModel;
use App\Repository\dw\DwCommandeRepository;
use App\Service\da\NumeroGenerateurService;
use App\Service\ddp\DdpGeneratorNameService;
use App\Service\fichier\UploderFileService;
use App\Service\security\SecurityService;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class DdpFactory
{
    private DdpModel $ddpModel;
    private EntityManagerInterface $em;
    private NumeroGenerateurService $numeroGenerateur;
    private SecurityService $securityService;

    public function __construct(
        DdpModel $ddpModel,
        EntityManagerInterface $em,
        NumeroGenerateurService $numeroGenerateur,
        SecurityService $securityService
    ) {
        $this->ddpModel = $ddpModel;
        $this->em = $em;
        $this->numeroGenerateur = $numeroGenerateur;
        $this->securityService = $securityService;
    }

    /**
     * Initialisation du DdpDto avec les données nécessaires
     *
     * @param int $idTypeDdp
     * @return DdpDto
     */
    public function initialisation(int $idTypeDdp): DdpDto
    {
        $dto = new DdpDto();

        $dto->typeDdp = $this->getTypeDdp($idTypeDdp);

        // initialisation formulaire
        $dto->choiceModePaiement = $this->modePaiement();
        $dto->choiceDevise = $this->devise();
        $dto->numeroCommande = $this->numeroCmd($idTypeDdp);
        $dto->numeroFacture = [];

        // Agence et Service par défaut
        $dto->debiteur = [
            'agence' => $this->em->getRepository(Agence::class)->find(1),
            'service' => $this->em->getRepository(Service::class)->find(1)
        ];

        return $dto;
    }

    public function apresSoumission(FormInterface $form, DdpDto $dto): DdpDto
    {
        $dto->numeroDdp = $this->numeroGenerateur->genererNumeroDdp();
        [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger,  $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto);


        if ($dto->typeDdp->getId() == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            // Récupération des numéros de commande pour le type DDP après arrivage
            $numeroCommandes = $this->recuperationNumCommande($dto->numeroFournisseur, $dto->numeroFacture);
            $dto->numeroCommande = [$numeroCommandes];
            // copie des fichiers distant dans le dossier DDP
            $this->copierFichierDistant($dto);
        }

        // Autres DOC -------
        //Piece joint 04
        if ($dto->pieceJoint04 != null) {
            $dto->estAutreDoc = true;
            $dto->nomAutreDoc = $dto->pieceJoint04->getClientOriginalName();
        }

        // Piece Joint 03
        if (!empty($dto->pieceJoint03)) {
            $dto->estCdeClientExterneDoc = true;
            $dto->nomCdeClientExterneDoc = $this->recuperationNomOriginalPieceJointe03($dto);
        }

        // chemin, nom des fichier uploder (pieceJoint01, pieceJoint02, pieceJoint03, pieceJoint04)
        $dto->nomEtCheminFichiersEnregistrer = $nomEtCheminFichiersEnregistrer;
        $dto->nomFichierTelecharger = $nomFichierTelecharger;
        $dto->nomAvecCheminFichier = $nomAvecCheminFichier;
        $dto->nomFichier = $nomFichier;

        // recuperation des noms des fichiers dw commande
        $dto->nomDesFichiersDwCommande = $this->recupNomDesFichiersDwCommande($dto);

        // fichier distant
        $dto->nomDesFichiersDistant = $this->recupNomDesFichiersDistant($dto);


        // info sur l'utilisateur ---------------------
        $dto->adresseMailDemandeur = $this->securityService->getUserEmail();
        $dto->demandeur = $this->securityService->getUserName();

        // info utile -----------------------
        $dto->statut = StatutConstants::SOUMIS_A_VALIDATION;
        $dto->numeroVersion = 1;
        $dto->numeroDossierDouane = $this->ddpModel->getNumDossierGcot($dto->numeroFournisseur, $dto->getNumeroCommandeString(), $dto->getNumeroFactureString());

        return $dto;
    }


    /**
     * Copie des fichiers dans un serveur '192.168.0.15' dans le repertoire uplode/ddp/fichiers
     *
     * @param DdpDto $dto
     * @return void
     */
    private function copierFichierDistant(DdpDto $dto): void
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $cheminDeFichiers = $this->recupCheminFichierDistant($dto);
        $cheminDestination = $chemin . '/' . $dto->numeroDdp;

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

    /**
     * Récupération des noms des fichiers distant
     * 
     * @param DdpDto $dto
     * 
     * @return array
     */
    private function recupNomDesFichiersDistant(DdpDto $dto): array
    {
        $lesCheminsFichiers = $this->recupCheminFichierDistant($dto);

        $nomDesFichiersDistant = [];
        foreach ($lesCheminsFichiers as $value) {
            $nomDesFichiersDistant[] = $this->nomFichier($value);
        }

        return $nomDesFichiersDistant;
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
     * Recupération des chemins des fichiers distant 192.168.0.15
     *
     * @param DdpDto $dto
     * @return array
     */
    private function recupCheminFichierDistant(DdpDto $dto): array
    {
        $numDossiers = $this->ddpModel->getNumDossierGcot($dto->numeroFournisseur, $dto->getNumeroCommandeString(), $dto->getNumeroFactureString());

        $cheminDeFichiers = [];
        foreach ($numDossiers as $value) {
            $dossiers = $this->ddpModel->findListeDoc($value);

            foreach ($dossiers as  $dossier) {
                $cheminDeFichiers[] = $dossier['Nom_Fichier'];
            }
        }

        return $cheminDeFichiers;
    }

    /**
     * Récupération des noms des fichiers dw commande 
     * en copiant les fichiers de dw_commande vers le dossier DDP
     *
     * @param DdpDto $dto
     * 
     * @return array
     */
    private function recupNomDesFichiersDwCommande(DdpDto $dto): array
    {
        $pathAndCdes = $this->recupPathDwCommande($dto);

        return $this->copieFichierDwCommande($pathAndCdes, $dto);
    }

    /**
     * Copie des fichiers dw commande dans le dossier DDP
     * 
     * @param array $pathAndCdes
     * @param DdpDto $dto
     * 
     * @return array
     */
    private function copieFichierDwCommande(array $pathAndCdes, DdpDto $dto): array
    {
        $nomDufichierCde = [];
        foreach ($pathAndCdes as  $pathAndCde) {
            if ($pathAndCde[0]['path'] != null) {
                $cheminDufichierInitial = $_ENV['BASE_PATH_FICHIER'] . "/" . $pathAndCde[0]['path'];

                if (!file_exists($cheminDufichierInitial)) {
                    // Le fichier n'existe pas, on passe au suivant
                    continue;
                }

                $nomFichierInitial = basename($pathAndCde[0]['path']);

                $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddp/';
                $numDdp = $dto->numeroDdp;
                $cheminDufichierDestinataire = $cheminBaseUpload . '/' . $numDdp . '/' . $nomFichierInitial;

                $destinationDir = dirname($cheminDufichierDestinataire);
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0777, true);
                }

                if (copy($cheminDufichierInitial, $cheminDufichierDestinataire)) {
                    $nomDufichierCde[] =  $nomFichierInitial;
                }
            }
        }

        return $nomDufichierCde;
    }

    /**
     * Recupération des chemins des fichiers et numeroCommande 
     * dans la table dw_commande en se basant sur les numéros de commande
     * 
     * @param DdpDto $dto
     * 
     * @return array
     */
    private function recupPathDwCommande(DdpDto $dto): array
    {
        $dwCommandeRepo = $this->em->getRepository(DwCommande::class);
        $pathAndCdes = [];
        foreach ($dto->numeroCommande as  $numcde) {
            $pathAndCdes[] = $dwCommandeRepo->findPathByNumeroCde($numcde);
        }

        return $pathAndCdes;
    }

    /**
     * Enregistrement des fichiers uploder dans le dossier DDP
     * Et retourn les chemins, nom sur les fichier uploder 
     * 
     * @param FormInterface $form
     * @param DdpDto $dto
     * 
     * @return array [
     *      nomEtCheminFichiersEnregistrer, 
     *      nomFichierTelecharger, 
     *      nomAvecCheminFichier, 
     *      nomFichier
     * ]
     */
    private function enregistrementFichier(FormInterface $form, DdpDto $dto): array
    {
        $nameGenerator = new DdpGeneratorNameService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddp/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $numDdp = $dto->numeroDdp;
        $path = $cheminBaseUpload . $numDdp . '/';
        if (!is_dir($path)) mkdir($path, 0777, true);

        [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'conserver_nom_original' => false,
            'generer_nom_callback' => function ($file, $index, $extension, $variables) use ($numDdp) {
                $fieldName = $variables['field_name'] ?? '';

                $mapping = [
                    'pieceJoint01' => 'PROFORMA',
                    'pieceJoint02' => 'RIB',
                    'pieceJoint03' => 'BC',
                    'pieceJoint04' => 'AUTRES FICHIERS',
                ];

                $baseName = $mapping[$fieldName] ?? 'Document';

                // Si c'est le champ multiple pieceJoint03 ou s'il y a plusieurs fichiers, on ajoute l'index
                if ($fieldName === 'pieceJoint03') {
                    return sprintf("%s_%s_%02d.%s", $baseName, $numDdp, $index, $extension);
                }

                return sprintf("%s_%s.%s", $baseName, $numDdp, $extension);
            }
        ]);

        $nomFichier = $nameGenerator->generateNamePrincipal($dto->numeroDdp);
        $nomAvecCheminFichier = $path . '/' . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger,  $nomAvecCheminFichier, $nomFichier];
    }

    /**
     * Récupération des numéros de commande
     * 
     * @param string $numeroFournisseur
     * @param array $numeroFacture
     * @return string
     */
    private function recuperationNumCommande(string $numeroFournisseur, array $numeroFacture): string
    {
        $numCdes = $this->ddpModel->getCommandeReceptionnee($numeroFournisseur);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
        $numFacString = TableauEnStringService::TableauEnString(',', $numeroFacture);
        $numeroCommandes = $this->ddpModel->getNumCommande($numeroFournisseur, $numCdesString, $numFacString);
        return $numeroCommandes;
    }

    /**
     * Récupération du nom original des pièces jointes 03
     * 
     * @param DdpDto $dto
     * @return array
     */
    private function recuperationNomOriginalPieceJointe03(DdpDto $dto): array
    {
        $nomFichierBCs = [];
        foreach ($dto->pieceJoint03 as $value) {
            $nomFichierBCs[] = $value->getClientOriginalName();
        }
        return $nomFichierBCs;
    }

    /**
     * TODO: encore à reflechire
     * Récupération des numéros de facture
     * 
     * @param string $numeroFournisseur
     * @param int $typeId
     * @return array
     */
    private function numeroFac(string $numeroFournisseur, int $typeId): array
    {
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

        $listeGcot = $this->ddpModel->finListFacGcot($numeroFournisseur, $numCdesString);
        return array_combine($listeGcot, $listeGcot);
    }

    /**
     * Récupération des numéros de commande
     * 
     * @param int $typeId
     * @return array
     */
    private function numeroCmd(int $typeId): array
    {
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        return array_combine($numCdes, $numCdes);
    }

    /**
     * Récupération des numéros de commande 
     * facturé et non facturé
     * selon le type de demande
     * 
     * @param int $typeId
     * @return array
     */
    private function recuperationCdeFacEtNonFac(int $typeId): array
    {
        /** @var DwCommandeRepository $dwCommandeRepo  */
        $dwCommandeRepo = $this->em->getRepository(DwCommande::class);
        $numCdeDws = $dwCommandeRepo->findNumCdeDw();
        $numCdes1 = [];
        $numCdes2 = [];
        foreach ($numCdeDws as $numCdeDw) {
            $numfactures = $this->ddpModel->cdeFacOuNonFac($numCdeDw);
            if (!empty($numfactures)) {
                $numCdes2[] = $numCdeDw;
            } else {
                $numCdes1[] = $numCdeDw;
            }
        }

        $numCdes = [];
        if ($typeId == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $numCdes = $numCdes2;
        } else {
            $numCdes = $numCdes1;
        }
        return $numCdes;
    }

    /**
     * Récupération du type de demande par id
     * 
     * @param int $typeDdp
     * @return TypeDemande
     */
    private function getTypeDdp(int $typeDdp): TypeDemande
    {
        return $this->em->getRepository(TypeDemande::class)->find($typeDdp);
    }

    /**
     * Récupération des modes de paiement
     * 
     * @return array
     */
    private function modePaiement(): array
    {
        $modePaiement = $this->ddpModel->getModePaiement();
        return array_combine($modePaiement, $modePaiement);
    }

    /**
     * Récupération des devises
     * 
     * @return array
     */
    private function devise(): array
    {
        $devisess = $this->ddpModel->getDevise();

        $devises = [
            '' => '',
        ];

        foreach ($devisess as $devise) {
            $devises[$devise['adevlib']] = $devise['adevcode'];
        }

        return $devises;
    }
}
