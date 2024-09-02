<?php
namespace App\Form;

use App\Entity\Agence;
use App\Model\planning\PlanningModel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PlanningFormulaireType extends AbstractType
{       Const INTERNE_EXTERNE = [
            'INTERNE' => 'INTERNE',
             'EXTERNE' => 'EXTERNE'
        ];
        const FACTURE = [
            'FACTURE' => 'FACTURE',
            'ENCOURS' => 'ENCOURS',
            'TOUS' => 'TOUS',
        ];
        const PLANIFIER = [
            'PLANIFIE' => 'PLANIFIE',
            'NON PLANIFIE' => 'NON PLANIFIE',
            'TOUS' => 'TOUS'
        ];
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $planningModel = new PlanningModel();
            $agence = $planningModel->recuperationAgenceIrium();
            $annee = $planningModel->recuperationAnneeplannification();
            $agenceDebite = $planningModel->recuperationAgenceDebite();
           
            //$serviceDebite = $planningModel->recuperationServiceDebite();
            
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
                'expanded' => true,
            
            ])
            ->add('annee', ChoiceType::class,[
                'label' =>'Année de planification : ',
                'required' =>true,
                'choices' => $annee,
                'placeholder' => " -- choisir l''année' --",
                
            ])
            ->add('interneExterne', ChoiceType::class,[
                'label' => 'Interne/ Externe',
                'required' => true,
                'choices' => self::INTERNE_EXTERNE,
            
                'attr' => [ 'class' => 'interneExterne']
            ])
            ->add('facture', ChoiceType::class,[
                'label' => 'Facturation',
                'required' => true,
                'choices' => self::FACTURE,
                'attr' => ['class'=> 'facture']
            ])
            ->add('plan',ChoiceType::class,[
                'label' => 'Plannification',
                'required' => true,
                'choices' => self::PLANIFIER,
                'attr' => ['class'=> 'plan']
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Fin',
                'required' => false,
            ])
            ->add('numOr', TextType::class, [
                'label' => "N° Or",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Serie",
                'required' => false
            ])
            ->add('idMat', TextType::class, [
                'label' => "Id Matériel",
                'required' => false
            ])
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('agenceDebite', ChoiceType::class,[
                'label' =>'Agence Débiteur : ',
                'required' =>true,
                'choices' => $agenceDebite ,
                'placeholder' => " -- choisir une agence --",
                
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($planningModel) {
                $form = $event->getForm();
                $data = $event->getData(); 
            $serviceDebite = $planningModel->recuperationServiceDebite(null);
             
                $form->add('serviceDebite', ChoiceType::class,[
                    'label' =>'Service Débiteur : ',
                    'multiple' => true,
                    'choices' =>$serviceDebite,
                    'placeholder' => " -- choisir service--",
                    'expanded' => true,
                ]) ; 
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use($planningModel) {
                $form = $event->getForm();
                $data = $event->getData(); 
            
                $serviceDebite = $planningModel->recuperationServiceDebite($data['agenceDebite']);
                
                $result = [];
                foreach ($serviceDebite as $key => $value) {
                    $result[$value['text']] = $value['value'];
                }

                $form->add('serviceDebite', ChoiceType::class,[
                    'label' =>'Service Débiteur : ',
                    'multiple' => true,
                    'choices' =>$result,
                    'placeholder' => " -- choisir service--",
                    'expanded' => true,
                ]) ; 
            })
            ;
        }
};