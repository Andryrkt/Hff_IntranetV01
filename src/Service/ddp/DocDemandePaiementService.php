<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DocDemandePaiementMapper;

class DocDemandePaiementService
{
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
}
