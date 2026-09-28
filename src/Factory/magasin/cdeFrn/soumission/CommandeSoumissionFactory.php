<?php

namespace App\Factory\magasin\cdeFrn\soumission;

use App\Dto\Magasin\cdeFrn\CommandeSoumissionDTO;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionLigneDTO;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionDetailDTO;

class CommandeSoumissionFactory
{
    /**
     * @param array<int,array{num_cde:string,date_cde:string,type_cde:string,num_frn:string,nom_frn:string,devise:string,agence_lib:string,service_lib:string,cst:string,refp:string,desi:string,qte_cde:string,package_qty:string,prix_unit:string,montant:string,poids_total:string,av_bt:string,fms:string,vte_der_mois:string,nbr_vente:string,stock_dispo:string,stock_min:string,stock_max:string,npr:string}> $data
     * @param array<string,array{cst:string,refp:string,lib:string,num_doc:string,num_cli:string,nom_cli:string,rmq:string,datepla:string}> $detailsData
     * @param string $email
     * 
     * @return CommandeSoumissionDTO|null
     *
     * @throws \RuntimeException si les lignes de $data contiennent des valeurs d'en-tête incohérentes
     */
    public function hydrate(array $data, array $detailsData, string $email): ?CommandeSoumissionDTO
    {
        if (empty($data)) return null;

        $dto = new CommandeSoumissionDTO;

        $headerInfo = $this->assertHeaderConsistency($data);

        $dto->dateCde         = new \DateTime($headerInfo['date_cde']);
        $dto->numeroCommande  = $headerInfo['num_cde'];
        $dto->typeCde         = $headerInfo['type_cde'];
        $dto->delaiExpedition = 0; // TODO: à spécifier plus tard
        $dto->numFrn          = $headerInfo['num_frn'];
        $dto->nomFrn          = $headerInfo['nom_frn'];
        $dto->devise          = $headerInfo['devise'];
        $dto->responsable     = $email;
        $dto->libelleAgence   = $headerInfo['agence_lib'];
        $dto->libelleService  = $headerInfo['service_lib'];
        $dto->lignes          = $this->hydrateLignes($data, $detailsData);

        return $dto;
    }

    /**
     * Vérifie que toutes les lignes de $data partagent les mêmes valeurs
     * pour les champs utilisés au niveau de l'en-tête (num_cde, date_cde, type_cde,
     * num_frn, nom_frn, agence_lib, service_lib).
     *
     * @param array<int,array<string,string>> $data
     * 
     * @return array
     *
     * @throws \RuntimeException si une incohérence est détectée
     */
    private function assertHeaderConsistency(array $data): array
    {
        $headerFields = [
            'num_cde'      => 'Numéro de commande',
            'date_cde'     => 'Date de commande',
            'type_cde'     => 'Type de commande',
            'num_frn'      => 'Numéro du fournisseur',
            'nom_frn'      => 'Nom du fournisseur',
            'devise'       => 'Devise',
            'agence_lib'   => 'Agence',
            'service_lib'  => 'Service',
        ];

        $reference = $data[0];

        foreach ($headerFields as $field => $label) {
            $values = array_map(static fn(array $line): string => (string) ($line[$field] ?? 'N/A'), $data);
            $unique = array_unique($values);

            if (count($unique) > 1) {
                throw new \RuntimeException(sprintf(
                    'Incohérence détectée sur le champ "%s" : plusieurs valeurs distinctes trouvées (%s).',
                    $label,
                    implode(', ', array_unique($unique))
                ));
            }
        }

        return $reference;
    }

    /**
     * @param array<int,array{num_cde:string,date_cde:string,type_cde:string,num_frn:string,nom_frn:string,agence_lib:string,service_lib:string,cst:string,refp:string,desi:string,qte_cde:string,package_qty:string,prix_unit:string,montant:string,poids_total:string,av_bt:string,fms:string,vte_der_mois:string,nbr_vente:string,stock_dispo:string,stock_min:string,stock_max:string,npr:string}> $data
     * @param array<string,array{cst:string,refp:string,lib:string,num_doc:string,num_cli:string,nom_cli:string,rmq:string,datepla:string}> $detailsData
     * 
     * @return list<CommandeSoumissionLigneDTO>
     */
    public function hydrateLignes(array $data, array $detailsData): array
    {
        $lignes = [];

        foreach ($data as $key => $ligne) {
            $dtoLigne = new CommandeSoumissionLigneDTO;

            $cst  = trim($ligne['cst']);
            $refp = trim($ligne['refp']);

            $dtoLigne->numLine        = $key + 1;
            $dtoLigne->const          = $cst;
            $dtoLigne->avBat          = $ligne['av_bt'];
            $dtoLigne->ref            = $refp;
            $dtoLigne->packQty        = $ligne['package_qty'];
            $dtoLigne->designation    = $ligne['desi'];
            $dtoLigne->npr            = $ligne['npr'];
            $dtoLigne->fms            = $ligne['fms'];
            $dtoLigne->ret            = ""; // TODO à spécifier plus tard
            $dtoLigne->qteDem         = (int) $ligne['qte_cde'];
            $dtoLigne->qteDispo       = (int) $ligne['stock_dispo'];
            $dtoLigne->qteDispoMin    = (int) $ligne['stock_min'];
            $dtoLigne->qteDispoMax    = (int) $ligne['stock_max'];
            $dtoLigne->qteVteDer6Mois = (int) $ligne['vte_der_mois'];
            $dtoLigne->nbrVteDer6Mois = (int) $ligne['nbr_vente'];
            $dtoLigne->prixUnitaire   = (float) $ligne['prix_unit'];
            $dtoLigne->prixTotal      = (float) $ligne['montant'];
            $dtoLigne->poids          = (float) $ligne['poids_total'];

            $dtoLigne->details        = $this->hydrateDetails($detailsData["$cst|$refp"] ?? []);

            $lignes[] = $dtoLigne;
        }

        return $lignes;
    }

    /**
     * @param list<array{cst:string,refp:string,lib:string,num_doc:string,num_cli:string,nom_cli:string,rmq:string,datepla:string}> $details
     *
     * @return list<CommandeSoumissionDetailDTO>
     */
    private function hydrateDetails(array $details): array
    {
        $result = [];

        foreach ($details as $detail) {
            $dtoDetail = new CommandeSoumissionDetailDTO;

            $datePla  = null;

            if (!empty($detail['datepla'])) $datePla = new \DateTime($detail['datepla']);

            $dtoDetail->numDoc       = $detail['num_doc'];
            $dtoDetail->refClient    = $detail['lib'];
            $dtoDetail->numClient    = $detail['num_cli'];
            $dtoDetail->nomClient    = $detail['nom_cli'];
            $dtoDetail->rmqClient    = $detail['rmq'];
            $dtoDetail->datePlanning = $datePla;

            $result[] = $dtoDetail;
        }

        return $result;
    }
}
