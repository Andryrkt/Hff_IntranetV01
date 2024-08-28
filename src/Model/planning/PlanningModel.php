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
        $statement = " SELECT 
                        asuc_num ,
                         asuc_lib 
                    FROM agr_succ
                    WHERE asuc_codsoc ='HF'
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
       return array_combine(
         array_column($dataUtf8, 'asuc_lib'),
         array_map(function($item) {
             return $item['asuc_num'] . ' - ' . $item['asuc_lib'];
         }, $dataUtf8)
       );
        
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
      dump($dataUtf8);
      
   }

   


   

}