<?php


namespace App\Model\magasin\devis;

use App\Model\Model;
use App\Service\GlobalVariablesService;
use Symfony\Component\Finder\Glob;

class ListeDevisMagasinModel extends Model
{
    public function getDevis(array $criteria = [],  string $vignette = 'magasin', string $codeAgenceAutoriser, bool $adminMulti = false): array
    {
        $statement = "SELECT FIRST 100 DISTINCT
            nent_numcde as numero_devis
            ,nent_datecde as date_creation
            ,nent_succ || ' - ' || nent_servcrt as emmeteur
            ,nent_numcli || ' - ' || nent_nomcli as client
            ,TRIM(nent_refcde) as reference_client
            ,nent_cdeht as montant
            ,nent_posl as statut_ips
            ,nent_devise as devise
            ,nlig_constp as constructeur

            FROM informix.neg_ent
            left JOIN informix.neg_lig on nlig_numcde = nent_numcde
            WHERE nent_natop = 'DEV'
            AND nent_soc = 'HF'
            AND CAST(nent_numcli AS VARCHAR(20)) NOT LIKE '199%'
            AND year(Nent_datecde) = year(TODAY)
        ";

        if (array_key_exists('statutIps', $criteria) && ($criteria['statutIps'] == 'RE' || $criteria['statutIps'] == 'TR')) {
            $statement .= " AND nent_posl in ('--','AC','DE', 'RE', 'TR')";
        } else {
            $statement .= " AND nent_posl in ('--','AC','DE', 'TR')";
        }

        // if ($vignette === 'magasin' && $codeAgenceUser === '01' && !$admin) {
        //     // entrer par le vignette MAGASIN - agence tana
        //     $piecesMagasin = GlobalVariablesService::get('pieces_magasin');
        //     $statement .= " AND nlig_constp IN ($piecesMagasin) AND nent_succ <> '60' ";
        // } elseif ($vignette === 'magasin_pol' && $codeAgenceUser !== '01' && !$admin) {
        //     //entrer par le vignette MAGASIN - autres agence 
        //     $piecesMagasinPol = GlobalVariablesService::get('pieces_magasin') . GlobalVariablesService::get('pieces_pneumatique');
        //     $statement .= " AND nlig_constp IN ($piecesMagasinPol) AND nent_succ = '$codeAgenceUser' ";
        // } elseif ($vignette === 'pneumatique' && $codeAgenceUser === '60' && !$admin) {
        //     // entrer par le vignette POL - agence pneumatique
        //     $piecesPneumatique = GlobalVariablesService::get('pneumatique');
        //     $statement .= " AND nlig_constp IN ($piecesPneumatique) AND nent_succ = '60' ";
        // }

        // if ($vignette === 'pneumatique' && !$adminMulti) {
        //     // entrer par le vignette POL - agence pneumatique
        //     $piecesPneumatique = GlobalVariablesService::get('pneumatique');
        //     $statement .= " AND nlig_constp IN ($piecesPneumatique) AND nent_succ in ($codeAgenceAutoriser) ";
        // } elseif (!$adminMulti) {
        //     // entrer par le vignette MAGASIN - agence tana et autres agence
        //     $piecesMagasinPol = GlobalVariablesService::get('pieces_magasin') . ',' . GlobalVariablesService::get('pneumatique');
        //     $statement .= " AND nlig_constp IN ($piecesMagasinPol) AND nent_succ in ($codeAgenceAutoriser) ";
        // }

        $statement .= " ORDER BY nent_datecde DESC";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * Récupère les informations du devis IPS
     * 
     * cette méthode utilise la table neg_lig pour récupérer les informations du devis IPS
     * 
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return array Les informations du devis IPS
     */
    public function getInfoDev(string $numeroDevis)
    {
        $statement = "SELECT nent_devise as devise
                        ,SUM(nlig_qtecde *nlig_pxnreel) as montant_total
                        ,SUM(nlig_nolign) as somme_numero_lignes 
                    from informix.neg_lig 
                    left JOIN informix.neg_ent on nent_numcde = nlig_numcde 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV' 
                    and nlig_constp <> 'Nmc'
                    and nlig_numcde = '$numeroDevis'
                    group by nent_devise
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * Récupère le montant total du devis IPS
     * 
     * cette méthode utilise la table neg_lig pour récupérer le montant total du devis IPS
     * 
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return float Le montant total du devis IPS
     */
    public function getMontantTotalDevisIps(string $numeroDevis): float
    {
        $statement = "SELECT SUM(nlig_qtecde *nlig_pxnreel) as montant_total
                    from informix.neg_lig 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV' 
                    and nlig_constp <> 'Nmc'
                    and nlig_numcde = '$numeroDevis'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'montant_total')[0];
    }

    /**
     * Récupère le nombre de lignes du devis IPS
     * 
     * cette méthode utilise la table neg_lig pour récupérer le nombre de lignes du devis IPS
     * 
     * @param string $numeroDevis Le numéro de devis à vérifier
     * @return int Le nombre de lignes du devis IPS
     */
    public function getLignesTotalDevisIps(string $numeroDevis): int
    {
        $statement = "SELECT SUM(nlig_nolign) as somme_numero_lignes 
                    from informix.neg_lig 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV' 
                    and nlig_constp <> 'Nmc'
                    and nlig_numcde = '$numeroDevis'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'somme_numero_lignes')[0];
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
                    END AS retour

                    from informix.neg_lig 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV'
                    and nlig_constp <> 'Nmc' 
                    and nlig_numcde = '$numeroDevis'
            ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'retour')[0];
    }

    /**
     * Récupère le code et le libellé du client
     * 
     * cette méthode utilise la table neg_ent pour récupérer le code et le libellé du client
     * 
     * @return array Les informations du client
     */
    public function getCodeLibelleClient()
    {
        $statement = "SELECT DISTINCT nent_numcli as code_client, nent_nomcli as nom_client
                        from neg_ent
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getUtilisateurCreateurDevis(string $numeroDevis): string
    {
        $statement = "SELECT TRIM(ausr_nom) as utilisateur_createur_devis
            FROM informix.neg_ent
            inner join informix.agr_usr on ausr_num = nent_usr and ausr_soc = nent_soc
            WHERE nent_numcde = '$numeroDevis'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'utilisateur_createur_devis')[0];
    }

    public function getClientAndModePaiement(string $numeroDevis): array
    {
        $statement = " SELECT nent_numcli as code_client
                    ,nent_nomcli as nom_client
                    ,TRIM(cpai_libelle) as mode_paiement
                    from informix.neg_ent 
                    inner join neg_cli on ncli_numcli = nent_numcli and ncli_soc = nent_soc
                    inner join agr_tab on atab_nom = 'PAI' and ncli_modp = atab_code
                    left join informix.cpt_pai on cpai_codpai = nent_modp 
                    where nent_numcde ='$numeroDevis'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getConstructeur(string $numeroDevis)
    {
        $statement = " SELECT 
                CASE 
                    WHEN COUNT(*) = 0 THEN 'AUCUNE CONSTRUCTEUR'
                    WHEN COUNT(CASE WHEN nlig_constp = 'CAT' THEN 1 END) = COUNT(*) THEN 'TOUT CAT'
                    ELSE 'TOUS NEST PAS CAT'
                END as resultat
            FROM informix.neg_lig 
            WHERE nlig_numcde = '$numeroDevis' 
            AND nlig_constp NOT LIKE 'Nmc%'
            AND nlig_constp IN (" . GlobalVariablesService::get('pieces_magasin') . ")
    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'resultat')[0];
    }
}
