<?php
namespace App\Model\planning;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;
use App\Entity\planning\ListePlanningSearch;

class ListePlanningModel extends Model{
    use ConversionModel;
    use FormatageTrait;
    use PlanningModelTrait;

}