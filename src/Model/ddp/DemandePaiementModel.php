<?php

namespace App\Model\ddp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\TableauEnStringService;

class DemandePaiementModel extends Model
{
    use ConversionModel;

    public function recupInfoFournissseur()
    {
        // shortcut: MIN ignore les RIB NULL mais choisit arbitrairement entre deux RIB renseignés, à revoir si IPS indique un RIB principal
        $statement = " SELECT
                    FBSE_NUMFOU AS num_fournisseur,
                    UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur,  -- Prend un seul nom fournisseur
                    MIN(fbse_devise) AS devise,                  -- Prend une seule devise
                    MIN(CASE
                        WHEN ffou_modp = 'CB' THEN 'CARTE BANCAIRE'
                        WHEN ffou_modp = 'CD' THEN 'CHEQUE DIFFERE'
                        WHEN ffou_modp = 'CH' THEN 'CHEQUE COMPTANT'
                        WHEN ffou_modp = 'CO' THEN 'ESPECES COMPTANT'
                        WHEN ffou_modp = 'TA' THEN 'TRAITE'
                        WHEN ffou_modp = 'VI' THEN 'VIREMENT'
                        ELSE ffou_modp
                    END) AS mode_paiement,
                    MIN(TRIM(fbqe_bqcode) ||' '|| TRIM(fbqe_bqguich) ||' '|| TRIM(fbqe_bqcpte) ||' '|| TRIM(fbqe_bqrib)) AS rib
                FROM
                    informix.FRN_BSE
                JOIN 
                    informix.FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                JOIN
                    informix.fou_bqe ON fbqe_numfou = fbse_numfou
                WHERE 
                    FFOU_SOC = 'HF'
                GROUP BY 
                    FBSE_NUMFOU
                ORDER BY 
                    nom_fournisseur
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getFournisseur()
    {
        $statement = "SELECT 
                        FBSE_NUMFOU AS num_fournisseur,
                        UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur
                    FROM 
                        FRN_BSE
                    GROUP BY 
                        FBSE_NUMFOU
                    ORDER BY 
                        nom_fournisseur 
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function findListeGcot(string $numeroFournisseur, string  $numCdesString, string $numFacString): array
    {
        $sql = " SELECT
            TRZT_Dossier_Douane.Code_Fournisseur, 
            TRZT_Dossier_Douane.Libelle_Fournisseur,
            TRZT_Dossier_Douane.Numero_Dossier_Douane, 
            TRZT_Dossier_Douane.Numero_LTA, 
            TRZT_Dossier_Douane.Numero_HAWB,
            TRZT_Facture.Numero_Facture, 
            TRZT_Facture.Montant_FOB as Montant_Facture,
            GCOT_Facture_Ligne.Numero_PO
            from TRZT_Dossier_Douane
            INNER JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            INNER JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            INNER JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%'
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and TRZT_Facture.Numero_Facture not in ({$numFacString})
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            group by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, TRZT_Facture.Montant_FOB, GCOT_Facture_Ligne.Numero_PO
            order by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
            OPTION (RECOMPILE)
            ";

        return $this->retournerResultGcot04FiltreSurColonne($sql, 'Numero_PO', $numCdesString);
    }

    /**
     * Commandes (Numero_PO) et montant de chacune des factures GCOT données
     *
     * @param string $numFacString factures au format 'FAC1','FAC2'
     * @return array<array{Numero_Facture: string, Montant_Facture: float, Numero_PO: string, Numero_Dossier_Douane: ?string, Numero_LTA: ?string, Numero_HAWB: ?string}>
     */
    public function getCommandesParFacture(string $numFacString): array
    {
        $sql = "SELECT DISTINCT
                TRZT_Facture.Numero_Facture,
                TRZT_Facture.Montant_FOB as Montant_Facture,
                GCOT_Facture_Ligne.Numero_PO,
                TRZT_Dossier_Douane.Numero_Dossier_Douane,
                TRZT_Dossier_Douane.Numero_LTA,
                TRZT_Dossier_Douane.Numero_HAWB
            from TRZT_Facture
            LEFT JOIN TRZT_Dossier_Douane on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            INNER JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            INNER JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Facture.Numero_Facture in ({$numFacString})";

        return $this->retournerResultGcot04($sql);
    }

    /**
     * Factures GCOT dont au moins une commande ne fait pas partie des commandes données
     *
     * @param string[] $numFactures
     * @param string[] $numCdes
     * @return array<string, string> [numéro de facture => première commande hors liste]
     */
    public function getFacturesAvecCommandeHorsListe(array $numFactures, array $numCdes): array
    {
        $factures = array_unique(array_map(fn($facture) => trim((string) $facture), $numFactures));

        $horsListe = [];
        // par lots : un IN de plusieurs milliers de valeurs fait échouer SQL Server
        foreach (array_chunk($factures, 500) as $lot) {
            foreach ($this->getCommandesParFacture(TableauEnStringService::TableauEnString(',', $lot)) as $ligne) {
                $numFacture = trim((string) $ligne['Numero_Facture']);
                $numCde = trim((string) $ligne['Numero_PO']);
                if ($numCde !== '' && !in_array($numCde, $numCdes, true) && !isset($horsListe[$numFacture])) {
                    $horsListe[$numFacture] = $numCde;
                }
            }
        }

        return $horsListe;
    }

    public function getMontantFacGcot(string $numeroFournisseur, string  $numCdesString, string $numfacture): array
    {
        $sql = "SELECT sum(montant_fob) as montantfacture  from TRZT_Facture where Numero_Facture in ({$numfacture})";

        return array_column($this->retournerResultGcot04($sql), 'montantfacture');
    }



    public function finListFacGcot(string $numeroFournisseur, string  $numCdesString): array
    {
        $sql = " SELECT  
          distinct 
            TRZT_Facture.Numero_Facture,
            GCOT_Facture_Ligne.Numero_PO
            from TRZT_Dossier_Douane
            INNER JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            INNER JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            INNER JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%'
            and TRZT_Facture.Numero_Facture like 'PDV_%'
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
        ";

        return array_column($this->retournerResultGcot04FiltreSurColonne($sql, 'Numero_PO', $numCdesString, ['Numero_Facture']), 'Numero_Facture');
    }

    public function getNumDossierGcot(string $numeroFournisseur, string  $numCdesString, ?string $numFactString): array
    {
        if (!empty($numFactString)) {
            $numFac = " and TRZT_Facture.Numero_Facture in ({$numFactString})";
        } else {
            $numFac = '';
        }
        $sql = " SELECT  DISTINCT
            TRZT_Dossier_Douane.Numero_Dossier_Douane,
            GCOT_Facture_Ligne.Numero_PO
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            INNER JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            INNER JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%'
            $numFac
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            ";
        return $this->retournerResultGcot04FiltreSurColonne($sql, 'Numero_PO', $numCdesString, ['Numero_Dossier_Douane']);
    }

    public function getNumCommande(string $numeroFournisseur, string $numCdesString, ?string $numFactString): string
    {
        $result = $this->getNumCommandes($numeroFournisseur, $numCdesString, $numFactString);

        // Retourner la première valeur ou une chaîne vide
        return !empty($result) ? (string) $result[0] : '';
    }

    /**
     * Toutes les commandes (Numero_PO) des factures données, limitées aux
     * commandes réceptionnées : une facture peut couvrir plusieurs commandes.
     *
     * @return string[]
     */
    public function getNumCommandes(string $numeroFournisseur, string $numCdesString, ?string $numFactString): array
    {
        if (!empty($numFactString)) {
            $numFac = "and TRZT_Facture.Numero_Facture in ({$numFactString})";
        } else {
            $numFac = '';
        }

        $sql = " SELECT DISTINCT
        GCOT_Facture_Ligne.Numero_PO as numerocde
        from TRZT_Dossier_Douane
        LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
        INNER JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
        INNER JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
        where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%'
        $numFac
        and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
        ";

        $result = array_column($this->retournerResultGcot04FiltreSurColonne($sql, 'numerocde', $numCdesString), 'numerocde');

        return array_values(array_unique(array_map(fn($el) => trim((string) $el), $result)));
    }


    public function findListeDoc($numeroDossier)
    {
        $numeroDossier = str_replace("'", "''", (string) $numeroDossier);
        $sql = " SELECT  Nom_Fichier, Date_Fichier, Numero_PO
            from GCOT_Gestion_Document
            where Numero_PO='{$numeroDossier}'
            and (Nom_Fichier like '%\PDV%' or Nom_Fichier like '%\LTA%' or Nom_Fichier like '%\HAWB%')
        ";

        return $this->retournerResultGcot04($sql);
    }

    public function cdeFacOuNonFac(string  $numCdesString)
    {
        $statement = "SELECT ffac_facext  
                    FROM  frn_fac 
                    WHERE ffac_numfac 
                    IN( SELECT DISTINCT fllf_numfac FROM  frn_llf WHERE fllf_numcde = $numCdesString ) 
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function getNumCdeDw()
    {
        $sql = " SELECT DISTINCT numero_cde as numcde
                FROM DW_Commande where path is not null
        ";
        return array_column($this->retournerResult28($sql), 'numcde');
    }


    /**
     * OR validés enregistrés lors de la soumission des commandes fournisseur
     * (cdefnr_soumis_a_validation_ligne, type_document 'OR', dernière version de chaque commande).
     *
     * @param string[] $numCdes numéros de commande (numériques uniquement)
     *
     * @return array<int,array{numero_cde:string,numero_or:string}>
     */
    public function getNumerosOrParCommandes(array $numCdes): array
    {
        $numCdes = array_values(array_filter(array_map('trim', $numCdes), 'ctype_digit'));
        if (empty($numCdes)) return [];

        $inCdes = "'" . implode("','", $numCdes) . "'";

        $sql = " SELECT DISTINCT csavl.numero_cde, csavl.numero_document as numero_or
                from cdefnr_soumis_a_validation_ligne csavl
                where csavl.numero_cde in ($inCdes)
                and csavl.type_document = 'OR'
                and csavl.numero_version = (
                    select max(c2.numero_version) from cdefnr_soumis_a_validation_ligne c2 where c2.numero_cde = csavl.numero_cde
                )
        ";

        return $this->retournerResult28($sql);
    }

    /**
     * Fichier (path) de la dernière version de chaque OR dans DW_Ordre_De_Reparation.
     *
     * @param string[] $numerosOr numéros d'OR (numériques uniquement)
     *
     * @return array<int,array{numero_or:string,path:string}>
     */
    public function getPathsDwOrdreReparation(array $numerosOr): array
    {
        $numerosOr = array_values(array_filter(array_map('trim', $numerosOr), 'ctype_digit'));
        if (empty($numerosOr)) return [];

        $inOrs = "'" . implode("','", $numerosOr) . "'";

        $sql = " SELECT DISTINCT dodr.numero_or, dodr.path
                from DW_Ordre_De_Reparation dodr
                where dodr.numero_or in ($inOrs)
                and dodr.numero_version = (
                    select max(d2.numero_version) from DW_Ordre_De_Reparation d2 where d2.numero_or = dodr.numero_or
                )
        ";

        return $this->retournerResult28($sql);
    }

    public function getPathDwCommande(string $numCde): array
    {
        $sql = " SELECT DISTINCT  path, numero_cde from DW_Commande where numero_cde='{$numCde}' and date_creation = (select max(date_creation) from DW_Commande where numero_cde='{$numCde}' )";

        return $this->retournerResult28($sql);
    }

    public function getMontantCdeAvance(string $numCde)
    {
        $statement = " SELECT  sum(fcdl_pxach * fcdl_qte) as montantCde from frn_cdl where fcdl_numcde in ({$numCde})
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    /**
     * Livraisons (avec montant réceptionné) par commande pour les factures fournisseur données.
     *
     * @param string[] $numCdes
     * @param string[] $numFactures n° de facture du select (préfixe "PDV_", absent de ffac_facext)
     * @return array<array{numero_commande: string, numero_livraison: string, montant: float}>
     */
    public function getLivraisonsReceptionnees(string $numeroFournisseur, array $numCdes, array $numFactures, string $codeSociete = 'HF'): array
    {
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
        $numFacExtString = TableauEnStringService::TableauEnString(',', array_map(fn($f) => preg_replace('/^PDV_/', '', trim($f)), $numFactures));

        $statement = " SELECT fllf_numcde as numero_commande, fllf_numliv as numero_livraison, SUM(fllf_qteliv * fllf_achnet) as montant
                        from Informix.frn_llf
                        where fllf_numcde in ({$numCdesString})
                        and fllf_numfac in (select ffac_numfac from Informix.frn_fac where ffac_numfou = '{$numeroFournisseur}' and REPLACE(ffac_facext, '/', '') in ({$numFacExtString}) and ffac_soc = '{$codeSociete}')
                        and fllf_soc = '{$codeSociete}'
                        group by fllf_numcde, fllf_numliv
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        return array_map(fn($ligne) => [
            'numero_commande'  => trim((string) $ligne['numero_commande']),
            'numero_livraison' => trim((string) $ligne['numero_livraison']),
            'montant'          => (float) $ligne['montant'],
        ], $this->convertirEnUtf8($data));
    }

    /**
     * Montant réceptionné par commande pour les factures fournisseur données
     *
     * @return array<string, float> [numCde => montant]
     */
    public function getMontantCommandeReceptionnee(string $numeroFournisseur, array $numCdes, array $numFactures, string $codeSociete = 'HF'): array
    {
        $montants = [];
        foreach ($this->getLivraisonsReceptionnees($numeroFournisseur, $numCdes, $numFactures, $codeSociete) as $ligne) {
            $montants[$ligne['numero_commande']] = ($montants[$ligne['numero_commande']] ?? 0) + $ligne['montant'];
        }
        return $montants;
    }

    public function getModePaiement()
    {
        $statement = " SELECT TRIM(atab_lib) as atablib 
                        from agr_tab 
                        where atab_nom='PAI'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'atablib');
    }

    public function getDevise()
    {
        $statement = " SELECT adev_code as adevcode, 
                            TRIM(adev_lib)as adevlib 
                        from agr_dev
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    /**
     * Recupère le numero de dossier de douane
     *
     * @param string $numeroFournisseur
     * @param string $numCdesString
     * @return array retourne une ou plusieurs valeur
     */
    public function getNumDossierDouane(string $numeroFournisseur, string  $numCdesString, string $numFacture): array
    {
        $sql = " SELECT  DISTINCT

                TRZT_Dossier_Douane.Numero_Dossier_Douane,
                GCOT_Facture_Ligne.Numero_PO

                from TRZT_Dossier_Douane
                LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
                LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
                LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
                where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
                and TRZT_Facture.Numero_Facture in ({$numFacture})
                and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            ";
        return $this->retournerResultGcot04FiltreSurColonne($sql, 'Numero_PO', $numCdesString, ['Numero_Dossier_Douane']);
    }

    public function getFactureNonReglee(string $numeroFournisseur, string $codeSociete = 'HF'): array
    {
        $statement = " SELECT 'PDV_'||''||trim(tecr_nopiec) as facture_non_lettree
                        FROM regix.trs_ecr 
                        WHERE tecr_nocpp = '{$numeroFournisseur}'
                        and tecr_codsoc = '{$codeSociete}'
                        and tecr_codjou = 'Achmag'
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'facture_non_lettree');
    }

    public function getCommandeReceptionnee(string $numeroFournisseur, string $codeSociete = 'HF', int $typeId = 5, string $numCdeValide = ''): array
    {
        $statement = " SELECT distinct fllf_numcde as commande_receptionnee from frn_llf
                inner join frn_liv 
                    on fliv_numliv = fllf_numliv 
                    and fliv_soc = fllf_soc 
                    and fliv_succ = fllf_succ 
                    and fliv_soc = '$codeSociete'
                where fliv_numfou = '{$numeroFournisseur}'
        ";

        // if ($typeId === 1) {
        //     if (trim($numCdeValide) === '') {
        //         // Aucune commande validée pour ce fournisseur : aucun résultat
        //         // possible (et "IN ()" est une erreur de syntaxe SQL/Informix).
        //         return [];
        //     }
        //     $statement .= " And fllf_numcde in ({$numCdeValide})";
        // }

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $data = $this->convertirEnUtf8($data);

        return array_column($data, 'commande_receptionnee');
    }

    public function recupInfoPourDa(string $numeroFournisseur, string $numCde)
    {
        $statement = " SELECT 
                    FBSE_NUMFOU AS num_fournisseur,
                    UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur,  -- Prend un seul nom fournisseur (Beneficiaire)
                    MIN(fbse_devise) AS devise,                  -- Prend une seule devise
                    TRIM(MIN(CASE
                        WHEN ffou_modp = 'CB' THEN 'CARTE BANCAIRE'
                        WHEN ffou_modp = 'CD' THEN 'CHEQUE DIFFERE'
                        WHEN ffou_modp = 'CH' THEN 'CHEQUE COMPTANT'
                        WHEN ffou_modp = 'CO' THEN 'ESPECES COMPTANT'
                        WHEN ffou_modp = 'TA' THEN 'TRAITE'
                        WHEN ffou_modp = 'VI' THEN 'VIREMENT'
                        ELSE ffou_modp
                    END)) AS mode_paiement,
                    TRIM(fbqe_bqcode) ||' '|| TRIM(fbqe_bqguich) ||' '|| TRIM(fbqe_bqcpte) ||' '|| TRIM(fbqe_bqrib) AS rib_fournisseur,
                    fcde_succ as code_agence, 
                    fcde_serv as code_service,
                    fcde_numcde as numero_cde,
                    'N°TVA : '||TRIM(fbse_asstva)||' - SIRET : '||fbse_siret
                    --case when ffou_modp = 'CD' or ffou_modp = 'CH'
                        --then 'N°TVA : '||TRIM(fbse_asstva)||' - SIRET : '||fbse_siret
                        --else null
                    --END 
                    as cif
                FROM 
                    informix.FRN_BSE
                LEFT JOIN 
                    informix.FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                LEFT JOIN
                    informix.fou_bqe ON fbqe_numfou = fbse_numfou
               	LEFT JOIN
                    informix.frn_cde ON fcde_numfou = fbse_numfou
                WHERE 
                    FFOU_SOC = 'HF'
                    AND fcde_numcde = '{$numCde}'
                    AND fbse_numfou = '{$numeroFournisseur}'
                GROUP BY 
                    FBSE_NUMFOU, code_agence, code_service, numero_cde, cif, rib_fournisseur
                ORDER BY 
                    nom_fournisseur
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getCodeAgenceService(?int $numOr)
    {
        $statement = " SELECT 
                    seor_succ as code_agence, 
                    seor_servcrt as code_service 
                    from informix.sav_eor 
                    where seor_numor = '$numOr'
        ";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * Retourne les montants HT et TTC d'une commande.
     *
     * @param string $numCde
     * @param string $codeSociete
     *
     * @return array{
     *     montant_total_cde_ht: float,
     *     montant_total_cde_ttc: float
     * }
     */
    public function getMontantCde(string $numCde, string $codeSociete): array
    {
        $statement = " SELECT 
                    fcde_mtn as montant_total_cde_ht,
                    fcde_ttc as montant_total_cde_ttc
                from informix.frn_cde 
                where fcde_numcde ='$numCde'
                and fcde_soc = '$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        if (empty($data)) {
            return [
                'montant_total_cde_ht'  => 0.00,
                'montant_total_cde_ttc' => 0.00,
            ];
        }

        return [
            'montant_total_cde_ht'  => (float) $data[0]['montant_total_cde_ht'],
            'montant_total_cde_ttc' => (float) $data[0]['montant_total_cde_ttc'],
        ];
    }


    /**
     * Montant total d'une commande dans IPS (null si le couple fournisseur/commande n'existe pas)
     */
    public function getMontantTotalCommande(string $numeroFournisseur, string $numCde): ?float
    {
        $statement = " SELECT fcde_mtn as montant_total_commande
                from Informix.frn_cde
                where fcde_numfou = '" . (int) $numeroFournisseur . "'
                and fcde_numcde = '" . (int) $numCde . "'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        return empty($data) ? null : (float) $data[0]['montant_total_commande'];
    }

    public function recupInfoComamnde(string $numCde, string $numeroFournisseur)
    {
        $statement = " SELECT 
                    FBSE_NUMFOU AS num_fournisseur,
                    UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur,  -- Prend un seul nom fournisseur (Beneficiaire)
                    MIN(fbse_devise) AS devise,                  -- Prend une seule devise
                    TRIM(MIN(CASE
                        WHEN ffou_modp = 'CB' THEN 'CARTE BANCAIRE'
                        WHEN ffou_modp = 'CD' THEN 'CHEQUE DIFFERE'
                        WHEN ffou_modp = 'CH' THEN 'CHEQUE COMPTANT'
                        WHEN ffou_modp = 'CO' THEN 'ESPECES COMPTANT'
                        WHEN ffou_modp = 'TA' THEN 'TRAITE'
                        WHEN ffou_modp = 'VI' THEN 'VIREMENT'
                        ELSE ffou_modp
                    END)) AS mode_paiement,
                    TRIM(fbqe_bqcode) ||' '|| TRIM(fbqe_bqguich) ||' '|| TRIM(fbqe_bqcpte) ||' '|| TRIM(fbqe_bqrib) AS rib_fournisseur,
                    fcde_succ as code_agence, 
                    fcde_serv as code_service,
                    fcde_numcde as numero_cde,
                    fcde_mtn as montant_total_cde
                FROM 
                    informix.FRN_BSE
                JOIN 
                    informix.FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                JOIN
                    informix.fou_bqe ON fbqe_numfou = fbse_numfou
               	JOIN
                    informix.frn_cde ON fcde_numfou = fbse_numfou
                WHERE 
                    FFOU_SOC = 'HF'
                    AND fcde_numcde = '{$numCde}'
                    AND fbse_numfou = '{$numeroFournisseur}'
                GROUP BY 
                    FBSE_NUMFOU, code_agence, code_service, numero_cde, montant_total_cde, rib_fournisseur
                ORDER BY 
                    nom_fournisseur
        ";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getInfoDdpDa(string $numeroDa, string $numeroCde)
    {
        $sql = " SELECT  
            FORMAT(dp.date_creation, 'dd/MM/yyyy HH:mm:ss') as date_soumission,
            case 
                when dsfb.numero_bap is null then dp.numero_demande_paiement
                else dsfb.numero_bap
            end as numero,
            td.libelle_type_demande as type,
            dp.motif,
            FORMAT(dp.montant_a_payer, 'N2') as montant_ht,
            dp.statut,
            dp.code_societe 
            from demande_paiement dp 
            left join da_soumission_facture_bl dsfb 
            on dsfb.numero_demande_paiement = dp.numero_demande_paiement
            left join type_demande td ON td.id = dp.type_demande_id 
            where dp.numero_demande_appro ='{$numeroDa}'
            and dp.numero_commande = '{$numeroCde}'
            order by dp.date_creation  desc
        ";
        $resultStmt = $this->connexion->query($sql);
        $data = [];
        while ($result = odbc_fetch_array($resultStmt)) {
            $data[] = $this->convertirEnUtf8($result);
        }
        return $data;
    }

    /**
     * @param string|null $numeroCommande une ou plusieurs commandes séparées par ";" (ex : "26221200;26221500")
     * @return string|null numéros de facture IPS séparés par ";"
     */
    public function getNumeroFactureIps(?string $numeroCommande): ?string
    {
        $numeroCommandes = array_filter(explode(';', $numeroCommande), fn($valeur) => $valeur !== '');

        if (empty($numeroCommandes)) {
            return null;
        }

        $numeroCommandesString = TableauEnStringService::TableauEnString(',', $numeroCommandes);

        $statement = "  SELECT FIRST 1 fllf_numfac
                        from informix.frn_llf
                        where fllf_numcde in ($numeroCommandesString)
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));
        $numFacs = array_filter(array_map('trim', array_column($data, 'fllf_numfac')));

        return empty($numFacs) ? null : implode(';', $numFacs);
    }

    public function getFilePathDdp(string $numeroDdp, string $table, string $columnName): string
    {
        $sql = " SELECT top 1 d.path from $table d where d.$columnName = '$numeroDdp' ORDER BY d.numero_version desc";

        $queryResult = $this->connexion->query($sql);

        if (!$queryResult) throw new \RuntimeException("Échec de la requête pour le DDP : $numeroDdp");

        $result = odbc_fetch_array($queryResult);

        return $result['path'] ?? '';
    }

    public function getAllDdpByDa(string $numeroDa): array
    {
        $sql = "SELECT 
                    dp.numero_demande_paiement AS numero_ddp,
                    dp.date_creation,
                    dp.numero_commande,
                    td.code_type_demande AS code_type,
                    CASE
                        WHEN td.code_type_demande = 'BAP' THEN (
                            SELECT TOP 1 t1.path
                                FROM DW_bon_a_payer t1
                                WHERE t1.numero_bap = dp.numero_demande_paiement
                                ORDER BY t1.date_derniere_modification DESC, t1.heure_derniere_modification DESC
                        )
                        WHEN td.code_type_demande = 'DPR' THEN (
                            SELECT TOP 1 t2.path
                                FROM DW_regularisation_ddp t2
                                WHERE t2.numero_ddr = dp.numero_demande_paiement
                                ORDER BY t2.date_derniere_modification DESC, t2.heure_derniere_modification DESC
                        )
                        WHEN td.code_type_demande IN ('DPA', 'DPL') THEN (
                            SELECT TOP 1 t3.path
                                FROM DW_demande_de_paiement t3
                                WHERE t3.numero_ddp = dp.numero_demande_paiement
                                ORDER BY t3.date_derniere_modification DESC, t3.heure_derniere_modification DESC
                        )
                    END AS path
                FROM demande_paiement dp
                INNER JOIN type_demande td
                    ON dp.type_demande_id = td.id
                WHERE dp.numero_demande_appro = '$numeroDa'
                ORDER BY dp.date_creation ASC;";

        $resultStmt = $this->connexion->query($sql);
        $data = [];

        while ($result = odbc_fetch_array($resultStmt)) {
            $data[] = [
                'numero_ddp'      => $result['numero_ddp'],
                'numero_commande' => $result['numero_commande'],
                'code_type'       => $result['code_type'],
                'path'            => $result['path'],
            ];
        }

        return $data;
    }

    /**
     * Dates des 3 étapes (transmission, validation service EM, validation compta) de chaque DDP d'une DA,
     * lues dans la dernière version de la table DW correspondant au type de demande.
     *
     * @return array<int,array{numero_ddp:string,code_type:string,date_transmission_ddp:?string,date_validation_ser_em:?string,date_validation_compta:?string}>
     */
    public function getDdpLifecycleSteps(string $numeroDa): array
    {
        $sql = "WITH dw_all AS (
                    SELECT 'BAP' AS code_type, numero_bap AS numero, id_bap AS id_doc, date_transmission_ddp, date_validation_ser_em, date_validation_compta
                        FROM DW_bon_a_payer
                    UNION ALL
                    SELECT 'DPR', numero_ddr, id_ddr, date_transmission_ddp, date_validation_ser_em, date_validation_compta
                        FROM DW_regularisation_ddp
                    UNION ALL
                    SELECT 'DPA', numero_ddp, id_ddp, date_transmission_ddp, date_validation_ser_em, date_validation_compta
                        FROM DW_demande_de_paiement
                ),
                dw_max AS (
                    SELECT code_type, numero, MAX(id_doc) AS max_id
                    FROM dw_all
                    GROUP BY code_type, numero
                )
                SELECT
                    dp.numero_demande_paiement AS numero_ddp,
                    td.code_type_demande AS code_type,
                    dw.date_transmission_ddp,
                    dw.date_validation_ser_em,
                    dw.date_validation_compta
                FROM demande_paiement dp
                INNER JOIN type_demande td
                    ON dp.type_demande_id = td.id
                INNER JOIN dw_max m
                    ON m.code_type = CASE WHEN td.code_type_demande = 'DPL' THEN 'DPA' ELSE td.code_type_demande END
                    AND m.numero = dp.numero_demande_paiement
                INNER JOIN dw_all dw
                    ON dw.code_type = m.code_type
                    AND dw.numero = m.numero
                    AND dw.id_doc = m.max_id
                WHERE dp.numero_demande_appro = '$numeroDa'
                ORDER BY dp.date_creation ASC;";

        $resultStmt = $this->connexion->query($sql);
        $data = [];

        while ($result = odbc_fetch_array($resultStmt)) {
            $data[] = $result;
        }

        return $data;
    }

    public function isFrnNonImmatricule(int $numFrn): bool
    {
        $statement = "SELECT  1 FROM frn_bse WHERE (fbse_asstva IS NULL OR TRIM(fbse_asstva) = '') AND (fbse_siret  IS NULL OR TRIM(fbse_siret)  = '') AND fbse_numfou ='$numFrn' LIMIT 1";
        $result = $this->connect->executeQuery($statement, [$numFrn]);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));
        return count($data) > 0;
    }
}
