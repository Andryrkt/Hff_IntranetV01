<?php

namespace App\Form;

use App\Entity\Agence;
use App\Entity\Casier;
use App\Repository\AgenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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