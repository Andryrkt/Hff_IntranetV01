<?php

namespace App\Form\admin;


use App\Entity\admin\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('codeService', 
            TextType::class, 
            [
                'label' => 'Code service',
            ])
            ->add('libelleService', 
            TextType::class, 
            [
                'label' => 'Libelle service',
            ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }


}