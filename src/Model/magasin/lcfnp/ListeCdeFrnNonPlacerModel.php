<?php

namespace App\Model\magasin\lcfnp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Model\Traits\ConditionModelTrait;

class ListeCdeFrnNonPlacerModel extends Model
{
    use ConversionModel;
    use ConditionModelTrait;
    public function fournisseurIrum()
    {
        $statement = "SELECT 
                    DISTINCT  fcde_numfou as codeFrs, fbse_nomfou as libFrs
                    FROM frn_cde , frn_bse 
                    WHERE frn_cde .fcde_numfou = frn_bse.fbse_numfou
                    AND  frn_cde .fcde_soc = 'HF'
                    AND fcde_numfou not in ('1','10','20','30','40','50','60','92','10019','6000001')
                    ORDER BY 1
        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }
    public function viewHffCtrmarqVinstant($criteria, $vinstant)
    {
        $statement = "create view hff_ctrmarq_agence_" . $vinstant . " as 
select nlig_succ as SUCC, to_char(nlig_numcli) as CLIENT, nent_nomcli as NOM_CLIENT, nlig_numcde as COMMANDE_OR, nlig_numcf as CTR_MARQUE,'Vente' as TYPE
from neg_lig, neg_ent where nlig_soc = 'HF' and nlig_succ not in ('01') and nent_natop = 'DIR' and nlig_numcde = nent_numcde
and nvl(nlig_numcf,0) not in (0)
group by 1,2,3,4,5,6
union 
select slor_succ as SUCC, to_char(sitv_numcli) as CLIENT, sitv_nomcli as NOM_CLIENT, slor_numor as COMMANDE_OR, slor_numcf  as CTR_MARQUE,'OR' as TYPE
from sav_lor, sav_itv where slor_soc = 'HF' and slor_succ not in ('01') and slor_natop = 'VTE' and sitv_natop = 'VTE' and slor_numor = sitv_numor
and nvl(slor_numcf,0) not in (0)
group by 1,2,3,4,5,6
union
select slor_succ as SUCC, slor_succdeb||'-'||slor_servdeb as CLIENT, (select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb)||' - '||
(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as NOM_CLIENT, 
slor_numor as COMMANDE_OR, slor_numcf  as CTR_MARQUE,'OR' as TYPE 
from sav_lor, sav_itv where slor_soc = 'HF' and slor_succ not in ('01') and slor_natop = 'CES' and sitv_natop = 'CES' and slor_numor = sitv_numor
and nvl(slor_numcf,0) not in (0)
group by 1,2,3,4,5,6

        ";
        $result = $this->connect->executeQuery($statement);
    }

    public function requetteBase($criteria, $vinstant, string $numOrValide)
    {   
        
        $vRequette = $this->requette($criteria,$vinstant);
        $dateDebut = $this->conditionDateSigne('requete_base.date_cmd','dateDebutDoc',$criteria,'>=');
        $dateFin = $this->conditionDateSigne('requete_base.date_cmd','dateFinDoc',$criteria,'<=');
        $numOR = $this->conditionEgal('requete_base.n_OR','numOR',$criteria);
        $numClient = $this->conditionEgal('requete_base.client','numClient',$criteria);
        $numCdeFrs = $this->conditionEgal('requete_base.n_commande','numCdFrs',$criteria);
        $numFrs = $this->conditionEgal('requete_base.n_frs','CodeNomFrs',$criteria);
        if ($criteria['orValide']) {
            $numOrValide = " WHERE requete_base.n_OR in ('" . $numOrValide . "')";
        } else {
            $numOrValide = "";
        }


        $statement = $vRequette
.$numOrValide
.$numFrs
.$numCdeFrs
.$numClient
.$numOR
.$dateDebut
.$dateFin
 ;
//  dump($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function dropView($vinstant)
    {
        $statement = " drop view hff_ctrmarq_agence_" . $vinstant . "";
        $result = $this->connect->executeQuery($statement);
    }
}
