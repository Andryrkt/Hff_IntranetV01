<?php


namespace App\Form;

use App\Entity\StatutDemande;
use App\Entity\TypeMouvement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadmForm1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('statut', EntityType::class, [
            'label' => 'Statut',
            'class' => StatutDemande::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une statut--',
            'required' => false,
        ])
            // ->add('idMateriel', TextType::class, [
            //     'label' => 'Id Materiel',
            //     'required' => false,
            // ])
            ->add('typeMouvement', EntityType::class, [
                'label' => 'Type Mouvement',
                'class' => TypeMouvement::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir une type de mouvement--',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Fin',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
