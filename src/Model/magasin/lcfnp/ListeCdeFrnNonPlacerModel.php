<?php

namespace App\Model\magasin\lcfnp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Model\Traits\ConditionModelTrait;

class ListeCdeFrnNonPlacerModel extends Model
{
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
        $statement = "create view hff_ctrmarq_agence_".$vinstant." as 
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

        if($criteria['orValide']) {
            $numOrValide = " AND requete_base.n_OR in ('".$numOrValide."')";
        } else {
            $numOrValide = "";
        }
        $statement = " SELECT * FROM (select 
slor_succdeb as agence_deb,
slor_servdeb as serv_dev,
fcde_numcde as n_commande,
 fcde_date as date_cmd, 
fcde_numfou n_frs,
 (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
fcde_ttc as mont_TTC, 
fcde_devise as Devis,
 slor_numor as n_OR, 
case  
     when slor_natop = 'CES' then 'OR - Cession'
     else 'OR - Client'
end as commentaire,
case  
     when slor_natop = 'CES' then (select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb)||' - '||
(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb)
     else (select seor_numcli||' - '||trim(seor_nomcli) from sav_eor where seor_soc = slor_soc and seor_numor = slor_numor)
end as client ,
'OR' as obs

from frn_cde, sav_lor
where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
and fcde_posl = '--'
and (slor_soc = fcde_soc and slor_numcf = fcde_numcde)
group by 1,2,3,4,5,6,7,8,9,10,11,12

union 

select 
fcde_succ agence_deb,
fcde_serv as serv_dev,
fcde_numcde as n_commande ,
fcde_date as date_cmd, 
fcde_numfou as n_frs, 
(select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
fcde_ttc as mont_TTC, 
fcde_devise as Devis,
 nlig_numcde as n_OR, 
case  
     when nlig_natop = 'CIS' then 'CIS'||' vers '||(select trim(asuc_lib) from agr_succ where asuc_numsoc = nlig_soc and asuc_num = nlig_succd)
     else 'Vente Client'
end as commentaire,
case  
     when nlig_natop = 'CIS' then case when NOM_CLIENT is null then 'REAPPRO' else CLIENT||' - '||NOM_CLIENT end
     else (select nent_numcli||' - '||trim(nent_nomcli) from neg_ent where nent_soc = nlig_soc and nent_numcde = nlig_numcde)
end as client,
case  
     when nlig_natop = 'CIS' then case when TYPE is null then 'REAPPRO' else TYPE end
     else 'Vente'
end as obs

from frn_cde, neg_lig, outer hff_ctrmarq_agence_" . $vinstant ."
where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
and fcde_posl = '--'
and (nlig_soc = fcde_soc and nlig_numcf = fcde_numcde)
and (ctr_marque = nlig_numcde)
group by 1,2,3,4,5,6,7,8,9,10,11,12

union 

select
fcde_succ agence_deb,
fcde_serv as serv_dev,
fcde_numcde as n_commande, 
fcde_date as date_cmd, 
fcde_numfou as n_frs,
(select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
fcde_ttc as mont_TTC, 
fcde_devise  as Devis, 
0 as n_OR, 
'REAPPRO' as commentaire,
'' as client,
'REAPPRO' as obs

from frn_cde
where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
and fcde_posl = '--'
and not exists (select slor_numcf from sav_lor where slor_soc = fcde_soc and slor_numcf = fcde_numcde)
and not exists (select nlig_numcf from neg_lig where nlig_soc = fcde_soc and nlig_numcf = fcde_numcde)
group by 1,2,3,4,5,6,7,8,9,10,11,12
) as  requete_base

 ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function dropView($vinstant)
    {
        $statement = " drop view hff_ctrmarq_agence_" . $vinstant ."";
        $result = $this->connect->executeQuery($statement);
    }
}
