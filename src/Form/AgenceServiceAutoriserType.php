<?php

namespace App\Form;

use App\Entity\AgenceServiceAutoriser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;

class AgenceServiceAutoriserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('Session_Utilisateur', 
        TextType::class, 
        [
            'label' => "Nom d'utilisateur",
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 4]),
            ],
        ])
    
        ->add('Code_AgenceService_IRIUM', 
            TextType::class,
            [
                'label' => 'Agence/Service'
            ])    
        ;

    
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgenceServiceAutoriser::class,
        ]);
    }
}