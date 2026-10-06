<?php

namespace App\Form\admin;


use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Societte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class AgenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('codeAgence', TextType::class, [
                'label' => 'Code Agence',
            ])
            ->add('libelleAgence', TextType::class, [
                'label' => 'Libelle Agence',
            ])
            ->add('societe', EntityType::class, [
                'label'        => 'Société',
                'placeholder'  => '-- Choisir société --',
                'class'        => Societte::class,
                'choice_label' => function (Societte $societe): string {
                    return $societe->getCodeSociete() . ' ' . $societe->getNom();
                },
                'multiple'     => false,
                'expanded'     => false,
            ])
            ->add('services', EntityType::class, [
                'label'        => 'Services liées',
                'class'        => Service::class,
                'choice_label' => function (Service $service): string {
                    return $service->getCodeService() . ' ' . $service->getLibelleService();
                },
                'multiple'     => true,
                'expanded'     => false,
                'mapped'       => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agence::class,
        ]);
    }
}
