<?php

namespace App\Form\da;

use App\Entity\da\DaObservation;
use App\Entity\da\DemandeAppro;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaObservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $datypeId = $options['daTypeId'];

        if ($datypeId != DemandeAppro::TYPE_DA_REAPPRO) {
            if ($datypeId == DemandeAppro::TYPE_DA_DIRECT) $observationLabel = 'Autoriser le service à modifier';
            if ($datypeId == DemandeAppro::TYPE_DA_AVEC_DIT) $observationLabel = 'Autoriser l’ATELIER à modifier';
            $builder
                ->add('statutChange', CheckboxType::class, [
                    'label'    => $observationLabel,
                    'required' => false
                ]);
        }

        $builder
            ->add('observation', TextareaType::class, [
                'label' => 'Observation',
                'attr'  => [
                    'rows' => 5,
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DaObservation::class,
            'daTypeId' => null, // valeur par défaut
        ]);
    }
}
