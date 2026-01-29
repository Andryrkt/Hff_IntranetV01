<?php

namespace App\Service\ddp;

use App\Controller\Traits\ddp\DocDdpTrait;
use App\Dto\ddp\DemandePaiementDto;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DocDemandePaiementMapper;

class DocDemandePaiementService
{
    use DocDdpTrait;

    private EntityManagerInterface $em;
    private DemandePaiementModel $ddpModel;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->ddpModel  = new DemandePaiementModel();
    }

    /**
     * Undocumented function
     *
     * @param DemandePaiementDto $dto
     * @return void
     */
    public function createDocDdp(DemandePaiementDto $dto)
    {
        $cheminDeFichiers = $this->recupCheminFichierDistant($dto);
        $documents = DocDemandePaiementMapper::map($dto, $cheminDeFichiers);

        foreach ($documents as $doc) {
            $this->em->persist($doc);
        }
        $this->em->flush();
    }

    /**
     * Récupération de numero de dossier de douane
     *
     * @param DemandePaiementDto $dto
     * @return array
     */
    public function recupNumDossierDouane(DemandePaiementDto $dto): array
    {
        $numFrs = $dto->numeroFournisseur;
        if ($numFrs === null) {
            return [];
        }
        $numCde = $dto->numeroCommande;
        $numFactures = $dto->numeroFacture;

        $numCdesString = TableauEnStringService::TableauEnString(',', $numCde);
        $numFactString = TableauEnStringService::TableauEnString(',', $numFactures);

        $numDossiers = array_column($this->ddpModel->getNumDossierGcot($numFrs, $numCdesString, $numFactString), 'Numero_Dossier_Douane');

        return $numDossiers;
    }

    /**
     * Recupération des chemins des fichiers distant 192.168.0.15
     *
     * @param DemandePaiementDto $data
     * @return array
     */
    private function recupCheminFichierDistant(DemandePaiementDto $dto): array
    {
        $numDossiers = $this->recupNumDossierDouane($dto);

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
     * Copie des fichiers dans un serveur '192.168.0.15' dans le repertoire uplode/ddp/fichiers
     *
     * @param DemandePaiementDto $dto
     * @return void
     */
    public function copierFichierDistant(DemandePaiementDto $dto): void
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $cheminDeFichiers = $this->recupCheminFichierDistant($dto);
        $cheminDestination = $chemin . '/' . $dto->numeroDdp . '_New_1';

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
     * ajout  de chemin pour les noms de fichier choisi
     *
     * @param DemandePaiementDto $dto
     * @return array
     */
    public function fichierChoisiAvecChemin(DemandePaiementDto $dto): array
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/da/' . $dto->numeroDa . '/';
        $fichierChoisiAvecChemins = [];
        foreach ($dto->fichiersChoisis as $value) {
            $fichierChoisiAvecChemins[] = $chemin . $value;
        }
        return $fichierChoisiAvecChemins;
    }

    /**
     * copie des fichiers choisi dans le repertoir 'da' ver 'ddp' 
     *
     * @param DemandePaiementDto $dto
     * @return void
     */
    public function copieFichierChoisi(DemandePaiementDto $dto)
    {
        $chemin = $_ENV['BASE_PATH_FICHIER'] . '/ddp';
        $cheminDestination = $chemin . '/' . $dto->numeroDdp . '_New_1';

        // S'assurer que le répertoire de destination existe
        if (!is_dir($cheminDestination)) {
            mkdir($cheminDestination, 0777, true);
        }

        foreach ($dto->fichiersChoisis as $fichier) {
            $cheminDeFichier = $_ENV['BASE_PATH_FICHIER'] . '/da/' . $dto->numeroDa . '/' . $fichier;
            // Vérifier si le fichier source existe et est lisible avant de continuer
            if (file_exists($cheminDeFichier) && is_readable($cheminDeFichier)) {
                $destinationFinal = $cheminDestination . '/' . $fichier;

                // Copier le fichier et vérifier le succès (le ! supprime l'avertissement en cas d'échec)
                @copy($cheminDeFichier, $destinationFinal);
            }
        }
    }

    public function ajoutDesFichiers(DemandePaiementDto $dto, array $nomFichiersTelecharger): array
    {
        $lesCheminsFichiers = $this->recupCheminFichierDistant($dto);

        $lesFichiers = [];
        foreach ($lesCheminsFichiers as $value) {
            $lesFichiers[] = $this->nomFichier($value);
        }

        $ensembleDesNomDeFichiers = array_merge($lesFichiers, $nomFichiersTelecharger);

        return $ensembleDesNomDeFichiers;
    }

    private function recupCdeDw(DemandePaiementDto $dto): array
    {
        $pathAndCdes = [];
        foreach ($dto->numeroCommande as  $numcde) {
            $pathAndCdes[] = $this->ddpModel->getPathDwCommande($numcde);
        }

        $nomDufichierCde = [];
        foreach ($pathAndCdes as  $pathAndCde) {
            if (!empty($pathAndCde) && $pathAndCde[0]['path'] != null) {
                $cheminDufichierInitial = $_ENV['BASE_PATH_FICHIER'] . "/" . $pathAndCde[0]['path'];

                if (!file_exists($cheminDufichierInitial)) {
                    // Le fichier n'existe pas, on passe au suivant
                    continue;
                }

                $nomFichierInitial = basename($pathAndCde[0]['path']);

                $cheminDufichierDestinataire = $_ENV['BASE_PATH_FICHIER'] . '/ddp/' . $dto->numeroDdp . '_New_' . $dto->numeroVersion . '/' . $nomFichierInitial;

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

    public function fusionDesFichiersDansUnTableau(DemandePaiementDto $dto, array $nomFichiersTelecharger)
    {
        $nomDufichierCde = $this->recupCdeDw($dto);
        $desFichiers = $this->ajoutDesFichiers($dto, $nomFichiersTelecharger);

        return array_merge($nomDufichierCde, $desFichiers);
    }
}
