<?php

namespace App\Form\planningAtelier;

use App\Model\planning\PlanningModel;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\planningAtelier\planningAtelierSearch;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class planningAtelierSearchType extends AbstractType
{
    use Transformation;
    private $planningModel;
    public function __construct()
    {
        $this->planningModel = new PlanningModel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $agence = $this->transformEnSeulTableauAvecKey($this->planningModel->recuperationAgenceIrium());
        $agenceDebite = $this->planningModel->recuperationAgenceDebite();

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
            ->add('numOr', TextType::class, [
                'label' => "N° OR",
                'required' => false
            ])
            ->add('resource', TextType::class, [
                'label' => "resource",
                'required' => false
            ])
            ->add('agenceEm', ChoiceType::class, [
                'label' =>  'Agence Travaux',
                'required' => false,
                'choices' => $agence,
                'placeholder' => ' -- Choisir une agence --',
            ])
            ->add('agenceDebite', ChoiceType::class, [
                'label' => 'Agence Débiteur',
                'required' => false,
                'choices' => $agenceDebite,
                'placeholder' => " -- Choisir une agence --",

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => planningAtelierSearch::class,
        ]);
    }
}
