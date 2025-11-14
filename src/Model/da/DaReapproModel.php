<?php

namespace App\Model\da;

use App\Model\Model;

class DaReapproModel extends Model
{
    public function getHistoriqueConsommation(array $date, string $codeAgence, string $codeService)
    {
        $statement = "SELECT 
                        dfcc_datefac AS date_fac,
                        month(dfcc_datefac)||'-'||year(dfcc_datefac) AS mois_annee,
                        slor_constp AS cst, 
                        trim(slor_refp) AS refp, 
                        trim(slor_desi) AS desi, 
                        sum(slor_qterea) AS qte_fac 
                    FROM sav_lor
                        INNER JOIN sav_eor ON seor_soc = slor_soc AND seor_succ = slor_succ AND slor_numor = seor_numor
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
                    GROUP BY 1,2,3,4,5
                    ORDER BY slor_constp asc
                    ";

        $result = $this->connect->executeQuery($statement);
        $rows = $this->convertirEnUtf8($this->connect->fetchResults($result));

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        // Formatter mois_annee en MM-YYYY
        foreach ($rows as &$row) {
            if (isset($row['mois_annee'])) {
                [$mois, $annee] = explode('-', $row['mois_annee']);
                $row['mois_annee'] = $months[$mois - 1]  . '-' . $annee;
            }
        }

        return $rows;
    }
}
