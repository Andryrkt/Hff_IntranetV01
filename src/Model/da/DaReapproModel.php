<?php

namespace App\Model\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Model\Model;
use App\Service\TableauEnStringService;

class DaReapproModel extends Model
{
    public function getHistoriqueConsommation(array $date, DemandeAppro $demandeAppro)
    {
        $conditionNumDa = "";
        $codeAgence     = $demandeAppro->getAgenceEmetteur()->getCodeAgence();
        $codeService    = $demandeAppro->getServiceEmetteur()->getCodeService();
        $codeCentrale   = $demandeAppro->getCodeCentrale();

        if (in_array($codeAgence, ['90', '91', '92'])) {
            if (!$codeCentrale) return [];

            $numDa = [];
            // Étape 1 : Obtenir les numéros DA pour le code centrale
            $sql = "SELECT 
                        d.numero_demande_appro 
                    FROM Demande_Appro d WHERE d.code_centrale='$codeCentrale'
                    ORDER by d.numero_demande_appro DESC";

            $exec = $this->connexion->query($sql);
            while ($result = odbc_fetch_array($exec)) {
                $data = $this->convertirEnUtf8($result);
                $numDa[] = $data['numero_demande_appro'];
            }

            // Étape 2 : Transformer en SQL list
            if (empty($numDa)) return [];

            $conditionNumDa = "AND seor_lib IN ('" . implode("', '", $numDa) . "')";
        }

        $allRefp = $demandeAppro->getDAL()->map(fn(DemandeApproL $demandeApproL) => $demandeApproL->getArtRefp())->toArray();
        $allRefpString = TableauEnStringService::orEnString($allRefp);

        $statement = "SELECT 
                        dfcc_datefac AS date_fac,
                        slor_constp AS cst, 
                        trim(slor_refp) AS refp, 
                        trim(slor_desi) AS desi, 
	                    sum(slor_pxnreel * slor_qterea) as mtt_total, 
                        sum(slor_qterea) AS qte_fac 
                    FROM sav_lor
                        INNER JOIN sav_eor ON seor_soc = slor_soc AND seor_succ = slor_succ AND slor_numor = seor_numor $conditionNumDa
                        INNER JOIN dpc_fcc ON dfcc_soc = slor_soc AND dfcc_succ = slor_succ AND dfcc_numfcc = slor_numfac
                    WHERE slor_succdeb = '$codeAgence' AND slor_servdeb = '$codeService' 
                        AND EXTEND(dfcc_datefac, YEAR TO DAY) BETWEEN '{$date['start']}' AND '{$date['end']}'
                        AND seor_numcli = 1
                        AND seor_servcrt = 'APP'
                        AND seor_pos = 'CP'
                        AND seor_succ = '80'
                        AND seor_natop = 'CES'
                        AND seor_typeor IN ('601','602','603','604','605','606','607','608','609')
                        AND slor_constp IN ('ALI','BOI','CEN','FAT','FBU','HAB','INF','MIN','OUT')
                        AND trim(slor_refp) IN ($allRefpString)
                    GROUP BY 1,2,3,4
                    ORDER BY slor_constp asc
                    ";

        $result = $this->connect->executeQuery($statement);
        $rows = $this->convertirEnUtf8($this->connect->fetchResults($result));

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        // Formatter mois_annee en MM-YYYY
        foreach ($rows as &$row) {
            [$year, $month,] = explode('-', $row['date_fac']);
            $row['mois_annee'] = $months[$month - 1]  . '-' . $year;
        }

        return $rows;
    }
}
