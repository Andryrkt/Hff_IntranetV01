<?php
namespace App\Model\planning;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;
use Doctrine\DBAL\Driver\IBMDB2\Statement;

class PlanningModel extends Model
{
   use ConversionModel;
   use FormatageTrait;

   public function recuperationAgenceIrium(){
        $statement = " SELECT  trim(asuc_num) as asuc_num ,
                               trim(asuc_lib) as asuc_lib
                      FROM agr_succ
                      WHERE asuc_codsoc = 'HF'
                      AND  (ASUC_NUM like '01' 
                      or ASUC_NUM like '10' 
                      or ASUC_NUM like '20' 
                      or ASUC_NUM like '30'
                       or ASUC_NUM like '40')
                      order by 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return 
          array_map(function($item) {
              return [$item['asuc_num'].'-'.$item['asuc_lib'] => $item['asuc_num']] ;
          }, $dataUtf8);
        
        
   }
   

   public function recuperationAnneeplannification(){
       $query = " SELECT YEAR(ska_d_start) as Annee
                  FROM ska
                  INNER JOIN skw ON skw.skw_id = ska.skw_id
                  GROUP BY 1
                  ORDER BY YEAR(ska_d_start) DESC           
       ";
      $result = $this->connect->executeQuery($query);
      $data = $this->connect->fetchResults($result);
      $dataUtf8 = $this->convertirEnUtf8($data);
      return array_combine(array_column($dataUtf8,'annee'),array_column($dataUtf8,'annee'));
      
   }
   public function recuperationAgenceDebite(){
      $statement = "SELECT  trim(asuc_lib) as asuc_lib,
                            trim(asuc_num) as asuc_num
                    FROM  agr_succ , sav_itv 
                    WHERE asuc_num = sitv_succdeb 
                    AND asuc_codsoc = 'HF'
                    group by 1,2
                    order by 1";
      $result = $this->connect->executeQuery($statement);
      $data = $this->connect->fetchResults($result);
      $dataUtf8 = $this->convertirEnUtf8($data);
     return array_combine(
       array_column($dataUtf8, 'asuc_lib'),
       array_map(function($item) {
           return $item['asuc_num'];
       }, $dataUtf8)
     );              
   }

   public function recuperationServiceDebite($agence){
        $statement = " SELECT DISTINCT
                        trim(atab_code) as atab_code ,
                        trim(atab_lib) as atab_lib  
                        FROM agr_succ , agr_tab a 
                        WHERE a.atab_nom = 'SER' 
                        and a.atab_code not in (select b.atab_code from agr_tab b where substr(b.atab_nom,10,2) = asuc_num and b.atab_nom like 'SERBLOSUC%') 
                        and asuc_num = '" .$agence."'
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return array_map(function($item) {
          return [
              "value" => $item['atab_code'], 
              "text"  => $item['atab_lib']
          ];
      }, $dataUtf8);  

   }
  public function recuperationMaterielplanifier($criteria = []){
    dd($criteria);
  }
    
 

   

}