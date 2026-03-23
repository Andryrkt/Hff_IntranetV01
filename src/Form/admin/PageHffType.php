<?php

namespace App\Form\admin;

use App\Entity\admin\historisation\pageConsultation\PageHff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PageHffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'nom',
                TextType::class,
                [
                    'label' => 'Nom de la page *',
                ]
            )
            ->add(
                'nomRoute',
                TextType::class,
                [
                    'label' => 'Nom de la route (utilisé dans le controlleur) *',
                ]
            )
            ->add(
                'lien',
                TextType::class,
                [
                    'label' => 'Lien de la page *',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageHff::class,
        ]);
    }
}
