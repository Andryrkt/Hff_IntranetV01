<?php

namespace App\Form\planningAtelier;

use App\Model\planning\PlanningModel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Dto\PlanningAtelier\PlanningAtelierSearchDto;
use App\Model\planningAtelier\planningAtelierModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Stopwatch\Section;

class planningAtelierSearchType extends AbstractType
{
    use Transformation;
    private PlanningModel $planningModel;
    private planningAtelierModel $planningAtelierModel;
    public function __construct()
    {
        $this->planningModel = new PlanningModel();
        $this->planningAtelierModel = new PlanningAtelierModel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $agence = $this->transformEnSeulTableauAvecKey($this->planningModel->recuperationAgenceIrium());
        $agenceDebite = $this->planningModel->recuperationAgenceDebite();
        $section = $this->transformeValeur( $this->planningAtelierModel->getSection('HF'),'section','num' ) ;
        $ressource =  $this->transformEnSeulTableau($this->planningAtelierModel->getResource('HF')) ;
        $builder
            ->add('numeroSemaine', ChoiceType::class, [
                'choices' => array_combine(range(1, 53), range(1, 53)),
                'label' => 'Numéro de semaine',
                'placeholder' => '-- Choisir une semaine --',
                'data' => date('W'),
                'required' => false
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Début',
                'required' => false
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Fin',
                'required' => false
            ])
            ->add('numeroOr', TextType::class, [
                'label' => "N° OR",
                'required' => false
            ])
            ->add('ressource', ChoiceType::class, [
                'label' => "Ressource",
                'choices' => array_combine($ressource, $ressource),
                'required' => false,
                'placeholder' => ' -- Choisir un ressource --',
            ])
            ->add('section', ChoiceType::class, [
                'label' => "Section",
                'required' => false,
                'choices' => $section,
                'placeholder' => ' -- Choisir un section --',
            ])
            ->add('agenceEm', ChoiceType::class, [
                'label' =>  'Agence Travaux',
                'required' => false,
                'choices' => $agence,
                'placeholder' => ' -- Choisir une agence --',
            ])
            ->add('agenceDeb', ChoiceType::class, [
                'label' => 'Agence Débiteur',
                'required' => false,
                'choices' => $agenceDebite,
                'placeholder' => " -- Choisir une agence --",

            ])
            ->add('serviceDeb', ChoiceType::class, [
                'label' => 'Service Débiteur',
                'multiple' => true,
                'choices' => [],
                'placeholder' => " -- Choisir un service--",
                'expanded' => true,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $serviceDebite = $this->transformEnSeulTableauAvecKeyService($this->planningModel->recuperationServiceDebite($data['agenceDeb']));

                $form->add('serviceDeb', ChoiceType::class, [
                    'label' => 'Service Débiteur : ',
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
            'data_class' => PlanningAtelierSearchDto::class,
        ]);
    }
}
