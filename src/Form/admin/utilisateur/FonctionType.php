<?php

namespace App\Form\admin\utilisateur;


use Symfony\Component\Form\AbstractType;
use App\Entity\admin\utilisateur\Fonction;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FonctionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        ->add('description', 
        TextType::class,
        [
            'label' => 'Description',
        ])
       
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Fonction::class,
        ]);
    }
}