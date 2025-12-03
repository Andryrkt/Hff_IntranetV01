<?php
namespace App\Form\cours_echange;

use App\Entity\cours_echange\CoursEchangeSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class CoursSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
          $builder
          ->add('dateHisto', DateType::class, [
                'label' => 'Date historique',
                'widget' => 'single_text',
                'required' => false,
          ]);
    }
     public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CoursEchangeSearch::class
        ]);
    }
    
}