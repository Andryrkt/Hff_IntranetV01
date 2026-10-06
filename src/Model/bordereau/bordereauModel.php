<?php
namespace App\Model\bordereau;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;
class BordereauModel extends Model{
    use ConversionModel;
    use FormatageTrait;
    public function bordereauListe($numInv){
        $statement = " SELECT   ainvp_numinv as numInv,
                                ainvp_nbordereau as numBordereau ,
                               ainvp_nligne as ligne,
                                (select astp_casier 
                                from art_stp
                                 WHERE astp_soc = ainvp_soc 
                                 AND astp_succ = ainvp_succ 
                                 AND astp_constp = ainvp_constp 
                                 AND astp_refp = ainvp_refp) as casier ,
	                            ainvp_constp as cst, 
                                TRIM(ainvp_refp) as refp,
                                TRIM((select abse_desi 
                                from art_bse 
                                WHERE abse_constp = ainvp_constp 
                                AND abse_refp = ainvp_refp)) as descrip,
                                ROUND(ainvp_stktheo) as qte_theo,
	                            ROUND((select astp_reserv 
                                from art_stp 
                                WHERE astp_soc = ainvp_soc 
                                AND astp_succ = ainvp_succ 
                                AND astp_constp = ainvp_constp
                                AND astp_refp = ainvp_refp)) as qte_alloue,
	                            ainvp_date as dateinv
                        from art_invp
	                    WHERE ainvp_soc = 'HF'  
	                    AND ainvp_numinv = ( select  max(ainvi_numinv) from art_invi  where ainvi_numinv_mait = '".$numInv."')
	                    AND ainvp_nbordereau > 0
                    	order by 4,2,3
                    ";
        $result = $this->connect->executeQuery($statement);
        //  dd($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}