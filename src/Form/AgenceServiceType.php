<?php

namespace App\Form;

use App\Entity\Agence;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;


class AgenceServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
       
        ->add('agences',
        EntityType::class,
        [
            'label' => 'service',
                'class' => Agence::class,
                'choice_label' => function (Agence $service): string {
                    return $service->getCodeAgence() . ' ' . $service->getLibelleAgence();
                },
                'multiple' => true,
                'expanded' => false
        ])
        ->add('services',
        EntityType::class,
        [
            'label' => 'service',
                'class' => Service::class,
                'choice_label' => function (Service $service): string {
                    return $service->getCodeService() . ' ' . $service->getLibelleService();
                },
                'multiple' => true,
                'expanded' => false
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // $resolver->setDefaults([
        //     'data_class' => Agence::class,
        // ]);
    }


}