<?php


namespace App\Model\magasin\devis;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;

class ListeDevisMagasinModel extends Model
{
    use ConversionModel;

    public function getDevis(array $criteria = [],  string $vignette = 'magasin', string $codeAgenceAutoriser, bool $adminMulti = false, $numDeviAExclureString): array
    {
        if (!empty($criteria) && array_key_exists('numeroDevis', $criteria) && !empty($criteria['numeroDevis'])) {
            $numeroDevis = $criteria['numeroDevis'];
            $statementNumDevis = " AND nent_numcde = $numeroDevis ";
        } else {
            $statementNumDevis = "";
        }

        $statement = "SELECT DISTINCT
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
            AND nent_servcrt <> 'ASS'
            $statementNumDevis
            AND (CAST(nent_numcli AS VARCHAR(20)) NOT LIKE '199%' and nent_numcli not in ('1101222', '1990000'))
            AND nent_numcde not in ($numDeviAExclureString)
            AND nent_numcde not in ('19407989','19407991','19408971','19410383','19409906','19409996')
            AND nent_datecde >= MDY(9, 1, 2025)
            --AND year(Nent_datecde) = year(TODAY)
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

        if ($vignette === 'pneumatique' && !$adminMulti) {
            // entrer par le vignette POL - agence pneumatique
            $piecesPneumatique = GlobalVariablesService::get('pneumatique');
            $statement .= " AND nlig_constp IN ($piecesPneumatique) AND nent_succ in ($codeAgenceAutoriser) ";
        } else {
            // entrer par le vignette MAGASIN - agence tana et autres agence
            $piecesMagasin = GlobalVariablesService::get('pieces_magasin');
            $statement .= " AND nlig_constp IN ($piecesMagasin) AND nent_succ <> '60' ";
        }

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
                    -- sinon
                        ELSE 'N'
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
        $cstMagasin = GlobalVariablesService::get('pieces_magasin');
        $statement = " SELECT 
                CASE 
                    WHEN COUNT(*) = 0 THEN 'AUCUNE CONSTRUCTEUR'
                    WHEN COUNT(CASE WHEN nlig_constp = 'CAT' THEN 1 END) = COUNT(*) THEN 'TOUT CAT'
                    ELSE 'TOUS NEST PAS CAT'
                END as resultat
            FROM informix.neg_lig 
            WHERE nlig_numcde = '$numeroDevis' 
            AND nlig_constp NOT LIKE 'Nmc%'
            AND nlig_constp IN ($cstMagasin)
    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'resultat')[0];
    }



    public function getNumeroDevisExclure()
    {
        $sql = " SELECT distinct Numero_Devis_ERP as numDevis
                from GCOT_Devis
                ";

        $statement = $this->connexion04Gcot->query($sql);
        $data = [];
        while ($List = odbc_fetch_array($statement)) {
            $data[] = $List;
        }

        return array_column($data, 'numDevis');
    }

    //** ==========================  Migration ==========================================*/
    public function getDevisMagasinToMigrationPdf($numDevis): array
    {
        $statement = " SELECT nent_numcde as numero_devis
                        ,nent_nomcli as nom_client
                        ,nent_succ as  succursale
                        ,nent_servcrt as service
                        ,TO_CHAR(nent_datecde, '%d/%m/%Y') as date
                        ,CAST(nent_cdeht AS VARCHAR(20)) as total_ht
                        ,CAST(nent_cdettc AS VARCHAR(20)) as total_ttc
                    from informix.neg_ent 
                    where nent_numcde in ($numDevis)
                    AND nent_soc ='HF'
                    order by nent_numcde ASC
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), null, 'numero_devis');
    }


    public function constructeurPieceMagasinMigration(string $numeroDevis)
    {
        $constructeurMagasinSansCat = "'AGR','ATC','AUS','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO'";
        $constructeurPneumatique = "'ATG','BET','DOG','GDY','HER','PND','TIP','TKI'";
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


    public function getStatutRelance(string $numeroDevis): ?array
    {
        $sql = " SET NOCOUNT ON;
                DECLARE @date_limite DATE = '2026-02-26';
                WITH relance_stats AS (
                    SELECT 
                        dsavn.numero_devis,
                        dsavn.date_envoye_devis_client,
                        dsavn.statut_bc,
                        dsavn.numero_version,
                        
                        -- Champs de stop global avec historique
                        dsavn.stop_progression_global,
                        dsavn.date_stop_global,
                        dsavn.motif_stop_global,
                        dsavn.date_reprise_manuel, -- Date de la dernière reprise
                        
                        COUNT(pr.numero_devis) AS nb_relances,
                        MAX(pr.date_de_relance) AS derniere_relance,
                        
                        -- Dates historiques (toujours conservées)
                        MAX(CASE WHEN pr.numero_relance = 1 THEN pr.date_de_relance END) AS date_relance_1,
                        MAX(CASE WHEN pr.numero_relance = 2 THEN pr.date_de_relance END) AS date_relance_2,
                        MAX(CASE WHEN pr.numero_relance = 3 THEN pr.date_de_relance END) AS date_relance_3,
                        
                        -- Stops par niveau (peuvent être désactivés)
                        MAX(CASE WHEN pr.numero_relance = 1 AND pr.stop_progression_niveau = 1 THEN 1 ELSE 0 END) AS stop_niveau_1,
                        MAX(CASE WHEN pr.numero_relance = 2 AND pr.stop_progression_niveau = 1 THEN 1 ELSE 0 END) AS stop_niveau_2,
                        MAX(CASE WHEN pr.numero_relance = 3 AND pr.stop_progression_niveau = 1 THEN 1 ELSE 0 END) AS stop_niveau_3,
                        
                        -- Calcul du délai (toujours basé sur la réalité, pas sur les stops)
                        CASE 
                            WHEN COUNT(pr.numero_devis) = 0 
                            THEN DATEDIFF(DAY, dsavn.date_envoye_devis_client, GETDATE())
                            ELSE DATEDIFF(DAY, MAX(pr.date_de_relance), GETDATE())
                        END AS delai_jours
                        
                    FROM devis_soumis_a_validation_neg dsavn
                    LEFT JOIN pointage_relance pr 
                        ON pr.numero_devis = dsavn.numero_devis
                    WHERE dsavn.numero_devis = '$numeroDevis'
                    GROUP BY 
                        dsavn.numero_devis,
                        dsavn.date_envoye_devis_client,
                        dsavn.statut_bc,
                        dsavn.numero_version,
                        dsavn.stop_progression_global,
                        dsavn.date_stop_global,
                        dsavn.motif_stop_global,
                        dsavn.date_reprise_manuel
                )

                SELECT TOP 1
                    -- Statut relance 1 - Les dates historiques restent
                    CASE 
                        -- TOUJOURS afficher la date si elle existe (même après réactivation)
                        WHEN rs.date_relance_1 IS NOT NULL 
                            THEN FORMAT(rs.date_relance_1, 'dd/MM/yyyy')
                        
                        -- Si pas de date, on regarde si on peut relancer
                        -- La condition de stop est basée sur l'état actuel (0 = réactivé)
                        WHEN rs.statut_bc = 'En attente bc' 
                            AND rs.nb_relances = 0 
                            AND rs.delai_jours >= 7
                            AND (rs.stop_progression_global = 0 OR rs.stop_progression_global IS NULL) -- Si réactivé, c'est 0
                            AND (rs.stop_niveau_1 = 0) -- Si réactivé, c'est 0
                        THEN 'A relancer'
                        
                        WHEN rs.date_relance_1 < @date_limite THEN NULL
                        ELSE null
                        -- ELSE FORMAT(rs.date_envoye_devis_client, 'dd/MM/yyyy')
                    END AS statut_relance_1,
                    
                    -- Statut relance 2
                    CASE 
                        -- Date historique toujours visible
                        WHEN rs.date_relance_2 IS NOT NULL 
                            THEN FORMAT(rs.date_relance_2, 'dd/MM/yyyy')
                        
                        -- Logique de relance avec état actuel des stops
                        WHEN rs.statut_bc = 'En attente bc' 
                            AND rs.nb_relances = 1 
                            AND rs.delai_jours >= 7
                            AND (rs.stop_progression_global = 0 OR rs.stop_progression_global IS NULL)
                            AND (rs.stop_niveau_2 = 0)
                        THEN 'A relancer'
                        
                        WHEN rs.statut_bc = 'En attente bc' 
                            AND rs.nb_relances = 1 
                            AND rs.delai_jours < 7 
                        THEN NULL

                        WHEN rs.statut_bc = 'En attente bc'
                            AND rs.stop_progression_global = 1
                        THEN NULL
                        
                        WHEN rs.date_relance_2 < @date_limite THEN NULL
                        ELSE FORMAT(COALESCE(rs.date_relance_2, rs.derniere_relance), 'dd/MM/yyyy')
                    END AS statut_relance_2,
                    
                    -- Statut relance 3
                    CASE 
                        WHEN rs.date_relance_3 IS NOT NULL 
                            THEN FORMAT(rs.date_relance_3, 'dd/MM/yyyy')
                        
                        WHEN rs.statut_bc = 'En attente bc' 
                            AND rs.nb_relances = 2 
                            AND rs.delai_jours >= 7
                            AND (rs.stop_progression_global = 0 OR rs.stop_progression_global IS NULL)
                            AND (rs.stop_niveau_3 = 0)
                        THEN 'A relancer'
                        
                        WHEN rs.statut_bc = 'En attente bc' 
                            AND (rs.nb_relances < 2 OR (rs.nb_relances = 2 AND rs.delai_jours < 7))
                        THEN NULL
                        
                        WHEN rs.statut_bc = 'En attente bc'
                            AND rs.stop_progression_global = 1
                        THEN NULL
                        
                        WHEN rs.derniere_relance < @date_limite THEN NULL
                        ELSE FORMAT(rs.derniere_relance, 'dd/MM/yyyy')
                    END AS statut_relance_3,
                    
                    -- Traçabilité : montrer l'historique des stops/reprises
                    CONCAT(
                        CASE 
                            WHEN rs.date_reprise_manuel IS NOT NULL 
                            THEN CONCAT('Réactivé le ', FORMAT(rs.date_reprise_manuel, 'dd/MM/yyyy'), ' | ')
                            ELSE '' 
                        END,
                        CASE 
                            WHEN rs.stop_progression_global = 0 AND rs.date_stop_global IS NOT NULL 
                            THEN 'Ancien stop global (réactivé) | '
                            WHEN rs.stop_progression_global = 1 
                            THEN 'Stop global actif | '
                            ELSE '' 
                        END,
                        CASE 
                            WHEN rs.stop_niveau_1 = 0 AND EXISTS(
                                SELECT 1 FROM pointage_relance 
                                WHERE numero_devis = rs.numero_devis 
                                AND numero_relance = 1 
                                AND stop_progression_niveau IS NOT NULL
                            ) THEN 'Niv.1 réactivé | '
                            WHEN rs.stop_niveau_1 = 1 THEN 'Niv.1 stoppé | '
                            ELSE '' 
                        END,
                        CASE 
                            WHEN rs.stop_niveau_2 = 0 AND EXISTS(
                                SELECT 1 FROM pointage_relance 
                                WHERE numero_devis = rs.numero_devis 
                                AND numero_relance = 2 
                                AND stop_progression_niveau IS NOT NULL
                            ) THEN 'Niv.2 réactivé | '
                            WHEN rs.stop_niveau_2 = 1 THEN 'Niv.2 stoppé | '
                            ELSE '' 
                        END,
                        CASE 
                            WHEN rs.stop_niveau_3 = 0 AND EXISTS(
                                SELECT 1 FROM pointage_relance 
                                WHERE numero_devis = rs.numero_devis 
                                AND numero_relance = 3 
                                AND stop_progression_niveau IS NOT NULL
                            ) THEN 'Niv.3 réactivé'
                            WHEN rs.stop_niveau_3 = 1 THEN 'Niv.3 stoppé'
                            ELSE '' 
                        END
                    ) AS historique_stops_reprises

                FROM relance_stats rs
                ORDER BY rs.numero_version DESC
        ";

        $exec = $this->connexion->query($sql);
        $fetchedRow = @odbc_fetch_array($exec);
        if ($fetchedRow === false) {
            return [];
        }

        return $fetchedRow;
    }

    public function stopRelance(string $numeroDevis, ?string $motif = null, string $utilisateur = 'inconnu'): bool
    {
        // On récupère l'état actuel pour savoir si on stoppe ou si on réactive
        $sqlCheck = "SELECT TOP 1 stop_progression_global 
                     FROM devis_soumis_a_validation_neg 
                     WHERE numero_devis = '$numeroDevis' 
                     ORDER BY numero_version DESC";
        $execCheck = $this->connexion->query($sqlCheck);
        $row = @odbc_fetch_array($execCheck);

        $currentState = $row ? (int)$row['stop_progression_global'] : 0;
        $newState = ($currentState === 1) ? 0 : 1;

        $utilisateurSql = str_replace("'", "''", $utilisateur);

        if ($newState === 1) {
            // On stoppe
            $motifStop = $motif ? $this->convertirEnUtf8(str_replace("'", "''", $motif)) : "";
            $sql = "UPDATE devis_soumis_a_validation_neg 
                    SET stop_progression_global = 1, 
                        date_stop_global = GETDATE(),
                        motif_stop_global = '$motifStop',
                        utilisateur_stop = '$utilisateurSql',
                        date_reprise_manuel = NULL,
                        utilisateur_reprise = NULL
                    WHERE numero_devis = '$numeroDevis' 
                    AND numero_version = (SELECT MAX(numero_version) FROM devis_soumis_a_validation_neg WHERE numero_devis = '$numeroDevis')";
        } else {
            // On réactive : on efface le motif et on note l'utilisateur qui réactive
            $sql = "UPDATE devis_soumis_a_validation_neg 
                    SET stop_progression_global = 0, 
                        motif_stop_global = NULL,
                        date_reprise_manuel = GETDATE(),
                        utilisateur_reprise = '$utilisateurSql'
                    WHERE numero_devis = '$numeroDevis' 
                    AND numero_version = (SELECT MAX(numero_version) FROM devis_soumis_a_validation_neg WHERE numero_devis = '$numeroDevis')";
        }

        $exec = $this->connexion->query($sql);
        return $exec !== false;
    }
}
