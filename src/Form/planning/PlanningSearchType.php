<?php


namespace App\Form\planning;


use App\Model\planning\PlanningModel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\planning\PlanningSearch;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PlanningSearchType extends AbstractType
{
    use Transformation; 
   
    private $planningModel;
    

        Const INTERNE_EXTERNE = [
                'TOUS' => 'TOUS',
                'INTERNE' => 'INTERNE',
                 'EXTERNE' => 'EXTERNE'
            ];
            const FACTURE = [
                'TOUS' => 'TOUS',
                ' DEJA FACTURE' => 'FACTURE',
                'ENCOURS' => 'ENCOURS'
            ];
            const PLANIFIER = [
               // 'TOUS' => 'TOUS',
                'PLANIFIE' => 'PLANIFIE',
                'NON PLANIFIE' => 'NON_PLANIFIE',
            ];

            public function __construct()
            {
                $this->planningModel = new PlanningModel();
               
            }
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {            
                
                //$serviceDebite = $planningModel->recuperationServiceDebite();
                $agence = $this->transformEnSeulTableauAvecKey($this->planningModel->recuperationAgenceIrium());
            $annee = $this->planningModel->recuperationAnneeplannification();
            $agenceDebite = $this->planningModel->recuperationAgenceDebite();

                $builder
                ->add('agence', ChoiceType::class, [
                    'label' =>  'Liste Agence',
                    'required' => true,
                    'choices' => $agence,
                    'placeholder' => ' -- choisir une agence --',
                    /*'choice_label' => function($choice,$key,$values){
                      return $values;  
                    },*/
                    'multiple' => true,
                    'expanded' => true,
                
                ])
                ->add('annee', ChoiceType::class,[
                    'label' =>'Année de planification : ',
                    'required' =>true,
                    'choices' => $annee,
                    'placeholder' => " -- choisir l'année --",
                    'data' => date('Y')
                ])
                ->add('interneExterne', ChoiceType::class,[
                    'label' => 'Interne / Externe',
                    'required' => true,
                    'choices' => self::INTERNE_EXTERNE,
                    'attr' => [ 'class' => 'interneExterne'],
                             
                    ])
                ->add('facture', ChoiceType::class,[
                    'label' => 'Facturation',
                    'required' => true,
                    'choices' => self::FACTURE,
                    'attr' => ['class'=> 'facture'],
                    'data' => 'ENCOURS'
                ])
                ->add('plan',ChoiceType::class,[
                    'label' => 'Plannification',
                    'required' => true,
                    'choices' => self::PLANIFIER,
                    'attr' => ['class'=> 'plan'],
                    'data' => 'PLANIFIE'
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
                ->add('serviceDebite', ChoiceType::class,[
                    'label' =>'Service Débiteur : ',
                    'multiple' => true,
                    'choices' => [],
                    'placeholder' => " -- choisir service--",
                    'expanded' => true,
                ])
                
                ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $serviceDebite = $this->transformEnSeulTableauAvecKeyService($this->planningModel->recuperationServiceDebite($data['agenceDebite']));
                 
                    $form->add('serviceDebite', ChoiceType::class,[
                        'label' =>'Service Débiteur : ',
                        'multiple' => true,
                        'choices' => $serviceDebite,
                        'placeholder' => " -- choisir service--",
                        'expanded' => true,
                    ]);
                }) 
                ;
       
              
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlanningSearch::class,
        ]);
       
    }
}
