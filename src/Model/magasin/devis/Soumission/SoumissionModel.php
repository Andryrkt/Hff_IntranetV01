<?php

namespace App\Model\magasin\devis\Soumission;

use App\Dto\Magasin\Devis\Soumission\SoumissionDto;
use App\Mapper\Magasin\Devis\Soumission\SoumissionMapper;
use App\Model\Informix\InsertQueryBuilder;
use App\Model\Model;
use App\Service\GlobalVariablesService;

class SoumissionModel extends Model
{
    /**
     * Compter le nombre de constructeur CAT
     * si 100% TOUT CAT sion TOUT N'EST PAS CAT
     *
     * @param string $numeroDevis
     * @return void
     */
    public function getConstructeur(string $numeroDevis)
    {
        $cstMagasin = GlobalVariablesService::get('pieces_magasin');
        $statement = " SELECT 
                CASE 
                    WHEN COUNT(*) = 0 THEN 'AUCUNE CONSTRUCTEUR'
                    WHEN COUNT(CASE WHEN nlig_constp = 'CAT' THEN 1 END) = COUNT(*) THEN 'TOUT CAT'
                    ELSE 'TOUS NEST PAS CAT'
                END as resultat
            FROM ips_hffprod:informix.neg_lig 
            WHERE nlig_numcde = '$numeroDevis' 
            AND nlig_constp NOT LIKE 'Nmc%'
            AND nlig_constp IN ($cstMagasin)
    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'resultat')[0];
    }

    /**
     * Récupère le situation de pièce
     * 
     * cette méthode utilise la table neg_lig pour récupérer le constructeur de la pièce magasin
     * 
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return string Le constructeur de la pièce magasin
     */
    public function constructeurPieceMagasin(string $numeroDevis)
    {
        $constructeurMagasinSansCat = GlobalVariablesService::get('pieceMagasinSansCat');
        $constructeurPneumatique = GlobalVariablesService::get('pneumatique');
        $statement = "SELECT 
                    CASE
                    -- si CAT et autre constructeur magasin
                        WHEN COUNT(CASE WHEN nlig_constp = 'CAT' THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp  IN ($constructeurMagasinSansCat) THEN 1 END) > 0
                        THEN TRIM('CP')
                    -- si  CAT
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp  IN ($constructeurMagasinSansCat) THEN 1 END) = 0
                        THEN TRIM('C')
                    -- si ni CAT ni autre constructeur magasin
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp  IN ($constructeurMagasinSansCat) THEN 1 END) = 0
                        THEN TRIM('N')
                    -- si autre constructeur magasin
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp IN ($constructeurMagasinSansCat) THEN 1 END) > 0
                        THEN TRIM('P')
                    -- si constructeur pneumatique
                        WHEN COUNT(CASE WHEN nlig_constp IN($constructeurPneumatique) THEN 1 END) > 0
                        THEN TRIM('O')
                    -- si CAT , autre constructeur magasin et constructeur pneumatique
                        WHEN COUNT(CASE WHEN nlig_constp = 'CAT' THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp  IN ($constructeurMagasinSansCat) THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp IN($constructeurPneumatique) THEN 1 END) > 0
                        THEN TRIM('CPO')
                    -- si CAT et constructeur pneumatique
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp  IN ($constructeurMagasinSansCat) THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp IN($constructeurPneumatique) THEN 1 END) > 0
                        THEN TRIM('CO')
                    -- si autre constructeur magasin et constructeur pneumatique
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp IN ($constructeurMagasinSansCat) THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp IN($constructeurPneumatique) THEN 1 END) > 0
                        THEN TRIM('PO')
                    -- si ni CAT ni autre constructeur magasin ni constructeur pneumatique
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp  IN ($constructeurMagasinSansCat) THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp IN($constructeurPneumatique) THEN 1 END) = 0
                        THEN TRIM('NO')
                    -- sinon
                        ELSE 'N'
                    END AS retour

                    from ips_hffprod:informix.neg_lig 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV'
                    and nlig_constp <> 'Nmc' 
                    and nlig_numcde = '$numeroDevis'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'retour')[0];
    }

    public function getNumeroVersion(string $numeroDevis)
    {
        $statement = "SELECT FIRST 1 MAX(numero_version) as version FROM ir_prod108:Informix.devis_soumis_a_validation_neg dneg WHERE dneg.numero_devis = '$numeroDevis'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'version')[0];
    }

    /**
     * Récupère les informations du devis IPS
     * 
     * cette méthode utilise la table neg_lig et neg_ent pour récupérer les informations du devis IPS
     * 
     * @param string $numeroDevis Le numéro de devis
     * @param string $codeSociete Le code société de l'utilisateur
     * @return array Les informations du devis IPS
     */
    public function getInfoDevis(string $numeroDevis, string $codeSociete)
    {
        $this->connect->connect();

        try {
            $statement = "SELECT 
                        nent_devise as devise
                        ,nent_cdeht as montant_devis
                        ,SUM(nlig_nolign) as somme_numero_lignes 
                    from ips_hffprod:informix.neg_lig 
                    left JOIN ips_hffprod:informix.neg_ent on nent_numcde = nlig_numcde 
                    where nlig_soc='$codeSociete' 
                    and nlig_natop='DEV' 
                    and nlig_constp <> 'Nmc'
                    and nlig_numcde = '$numeroDevis'
                    group by nent_devise, nent_cdeht
            ";

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchScalarResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }

    /**
     * Methode pour récupération des informations du devis 
     * déjà enregistrer dans devis_soumis_a_validation_neg
     *
     * @param string $numeroDevis
     * @param string $codeSociete
     * @return array
     */
    public function getInfoDevisForValidate(string $numeroDevis, string $codeSociete): array
    {
        $this->connect->connect();

        try {
            $statement = "SELECT 
                        dneg.numero_devis as numero_devis
                        ,dneg.statut_dw as statut_devis
                        ,dneg.montant_devis as montant_devis
                        ,dneg.somme_numero_lignes as somme_numero_lignes

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
     * Methode pour enregistrer les données du formulaire Verification prix
     *  dans la base de donnée
     *
     * @param SoumissionDto $dto
     * @param string $nomFichier
     * @param string $nomFichierExcel
     * @return void
     */
    public function enregistrerSoumission(SoumissionDto $dto, string $nomFichier, string $nomFichierExcel): void
    {
        // Convertir le DTO en tableau associatif pour l'insertion
        $donnees = SoumissionMapper::toArrayVerificationPrix($dto, $nomFichier, $nomFichierExcel);

        // Construire la requête d'insertion et l'exécuter
        $builder = new InsertQueryBuilder('ir_prod108:Informix.devis_soumis_a_validation_neg');
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
     * Methode pour enregistrer les données du formulaire Validation de devis
     *  dans la base de donnée
     *
     * @param SoumissionDto $dto
     * @param string $nomFichier
     * @param string $nomFichierExcel
     * @return void
     */
    public function enregistrerSoumissionValidationDevis(SoumissionDto $dto, string $nomFichier): void
    {
        // Convertir le DTO en tableau associatif pour l'insertion
        $donnees = SoumissionMapper::toArrayValidationDevis($dto, $nomFichier);

        // Construire la requête d'insertion et l'exécuter
        $builder = new InsertQueryBuilder('ir_prod108:Informix.devis_soumis_a_validation_neg');
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
}
