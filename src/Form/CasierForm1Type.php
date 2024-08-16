<?php

namespace App\Form;

use App\Entity\Agence;
use App\Entity\Casier;
use App\Repository\AgenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CasierForm1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
            'label' => 'Agence',
            'required' => false,
            'attr' => [
                'readonly' => true
            ],
            'data' => $options['data']->getAgenceEmetteur()
        ])
       
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Service',
            'required' => false,
            'attr' => [
                'readonly' => true,
                'disable' => true
            ],
            'data' => $options['data']->getServiceEmetteur()
        ])
        ->add('idMateriel', TextType::class, [
            'label' => 'Id Materiel',
            'required' => false,
        ])
        ->add('numParc', TextType::class, [
            'label' => "N° Parc",
            'required' => false
        ])
        ->add('numSerie', TextType::class, [
            'label' => "N° Serie",
            'required' => false
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Casier::class,
        ]);
    }
}