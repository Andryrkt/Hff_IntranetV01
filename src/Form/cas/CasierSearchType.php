<?php

namespace App\Form\cas;


use App\Entity\admin\Agence;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\AgenceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class CasierSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

     
        $builder
        ->add('agence', 
        EntityType::class,
        [
            'label' => 'Agence rattacher',
            'placeholder' => '-- Choisir une agence  --',
            'class' => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            },
            'required' => false,
            'query_builder' => function(AgenceRepository $agenceRepository) {
                    return $agenceRepository->createQueryBuilder('a')->orderBy('a.codeAgence', 'ASC');
                },
           
        ])
        ->add('casier',
        TextType::class,
        [
            'label' => 'Casier',
            'required' => false,
        ])
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}