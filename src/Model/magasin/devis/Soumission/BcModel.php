<?php

namespace App\Model\magasin\devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\BcDto;
use App\Mapper\Magasin\Devis\Soumission\BcMapper;
use App\Model\Informix\InsertQueryBuilder;
use App\Model\Informix\UpdateQueryBuilder;
use App\Model\Model;
use App\Service\GlobalVariablesService;

class BcModel extends Model
{
    /**
     * Récupère le dernière numéro de version
     *
     * @param string $numeroDevis
     * @param string $codeSociete
     * @return integer
     */
    public function getNumeroVersion(string $numeroDevis, string $codeSociete): int
    {
        $statement = "SELECT FIRST 1 MAX(numero_version) as version 
        FROM ir_prod108:Informix.bc_client_soumis_neg  bneg 
        WHERE bneg.numero_devis = '$numeroDevis' AND bneg.code_societe = '$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'version')[0] ?? 0;
    }

    /**
     * Récupère le montant du devis
     *
     * @param string $numeroDevis
     * @param string $codeSociete
     * @return array
     */
    public function getMontantDevis(string $numeroDevis, string $codeSociete): array
    {
        $constructeurPieceMagasin = GlobalVariablesService::get('pieces_magasin');

        $statement = " SELECT ROUND(SUM((COALESCE(nlig_pxvteht,0)*COALESCE(nlig_qtecde,0)) * (1-(COALESCE(nlig_rem1,0)/100))), 2) as montant 
                    FROM informix.neg_lig
                    inner join informix.neg_ent on nent_soc = nlig_soc and nent_succ = nlig_succ and nent_numcde = nlig_numcde and nlig_soc = 'HF' and nent_soc = 'HF'
                    where nent_natop = 'DEV'
                    --year(nlig_datecde) = '2025' and month(nlig_datecde) = '10'
                    and nent_posl <> 'TR'
                    and nent_servcrt = 'NEG'
                    and nlig_numcde = '$numeroDevis'
                    and nlig_soc = '$codeSociete'
                    and nlig_constp in (" . $constructeurPieceMagasin . ") -- ne recuperer que les pièces gérées par le magasin
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'montant');
    }

    /**
     * Recupère l'information du client et son mode de paiement
     *
     * @param string $numeroDevis
     * @return array
     */
    public function getClientAndModePaiement(string $numeroDevis, string $codeSociete): array
    {
        $statement = " SELECT nent_numcli as code_client
                    ,nent_nomcli as nom_client
                    ,TRIM(cpai_libelle) as mode_paiement
                    from informix.neg_ent 
                    inner join neg_cli on ncli_numcli = nent_numcli and ncli_soc = nent_soc
                    inner join agr_tab on atab_nom = 'PAI' and ncli_modp = atab_code
                    left join informix.cpt_pai on cpai_codpai = nent_modp 
                    where nent_numcde ='$numeroDevis' and nent_soc = '$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getInfoDevisForValidateBc($numeroDevis, $codeSociete)
    {
        $this->connect->connect();

        try {
            $statement = "SELECT 
                        dneg.numero_devis as numero_devis
                        ,dneg.statut_dw as statut
                        ,dneg.statut_bc as statut_bc

                    from ir_prod108:Informix.devis_soumis_a_validation_neg dneg
                    where dneg.code_societe = '$codeSociete'
                    and dneg.numero_devis = '$numeroDevis'
                    order by dneg.numero_version desc
                    limit 1
            ";

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchScalarResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }

    /**
     * Methode pour enregistrer les données du formulaire Bc
     *  dans la base de donnée
     *
     * @param BcDto $dto
     * @return void
     */
    public function enregistrerBc(BcDto $dto): void
    {
        // Convertir le DTO en tableau associatif pour l'insertion
        $donnees = BcMapper::toArrayBc($dto);

        // Construire la requête d'insertion et l'exécuter
        $builder = new InsertQueryBuilder('ir_prod108:Informix.bc_client_soumis_neg');
        $builder->setData($donnees);
        $result = $builder->build();

        // Exécuter la requête d'insertion
        // S'assurer que la connexion est ouverte
        $this->connect->connect();
        try {
            $this->connect->executeQuery($result['sql'], $result['params']);
        } finally {
            // ne fermez ici que si vous êtes sûr que c'est la dernière opération
            $this->connect->close();
        }
    }

    /**
     * MOdification du statut BC dans la 
     * table devis_soumis_a_validation_neg
     *
     * @param BcDto $dto
     * @return void
     */
    public function updateDevis(BcDto $dto)
    {
        $donnees = BcMapper::toArrayUpdateDevis($dto);

        $updateBuilder = new UpdateQueryBuilder('ir_prod108:Informix.devis_soumis_a_validation_neg');

        // Définir les données à mettre à jour
        $updateBuilder->setData($donnees);

        // Ajouter les conditions WHERE
        $updateBuilder->where('numero_devis', $dto->numeroDevis);
        $updateBuilder->where('numero_version', $dto->numeroVersionDevis);

        // Changer l'opérateur des conditions (optionnel)
        // $updateBuilder->setConditionOperator('AND');

        // Construire et exécuter la requête
        try {
            $result = $updateBuilder->build();

            $this->connect->connect();
            try {
                $this->connect->executeQuery($result['sql'], $result['params']);
            } finally {
                $this->connect->close();
            }
        } catch (\Exception $e) {
            // Vous pouvez logger l'erreur ici
            throw $e;
        }
    }
}
