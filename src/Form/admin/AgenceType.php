<?php

namespace App\Form\admin;


use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class AgenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add(
                'codeAgence',
                NumberType::class,
                [
                    'label' => 'Code Agence',
                ]
            )
            ->add(
                'libelleAgence',
                TextType::class,
                [
                    'label' => 'Libelle Agence',
                ]
            )
            ->add(
                'services',
                EntityType::class,
                [
                    'label' => 'Service',
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'multiple' => true,
                    'expanded' => true
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agence::class,
        ]);
    }
}
