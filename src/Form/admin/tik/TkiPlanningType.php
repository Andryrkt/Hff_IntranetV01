<?php

namespace App\Form\admin\tik;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TkiPlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCreation', DateTimeType::class, [
                'label' => 'Date de Création',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('numeroTicket', TextType::class, [
                'label' => 'Numéro de Ticket',
            ])
            ->add('datePlanning', DateType::class, [
                'label' => 'Date de Planning',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('heureDebutPlanning', TextType::class, [
                'label' => 'Heure de Début',
            ])
            ->add('heureFinPlanning', TextType::class, [
                'label' => 'Heure de Fin',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiPlanningType::class,
        ]);
    }
}

?>