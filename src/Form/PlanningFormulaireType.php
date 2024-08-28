<?php
namespace App\Form;

use App\Entity\Agence;
use App\Model\planning\PlanningModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PlanningFormulaireType extends AbstractType
{
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $planningModel = new PlanningModel();
            $agence = $planningModel->recuperationAgenceIrium();
            $annee = $planningModel->recuperationAnneeplannification();
           
            $builder
            ->add('agence', ChoiceType::class, [
                'label' =>  'Liste Agence',
                'required' => true,
                'choices' => $agence,
                'placeholder' => ' -- choisir une agence --',
                'choice_label' => function($choice,$key,$values){
                  return $values;  
                },
                'multiple' => true,
               /* 'choice_value' => function($choice){
                    return explode(' - ', $choice)[0];
                },*/
            ])
            
            ;
        }
};