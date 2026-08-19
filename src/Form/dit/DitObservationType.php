<?php

namespace App\Form\dit;

use App\Entity\dit\DitObservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DitObservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('observation', TextareaType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Ecrivez votre observation ...',
                    'rows'        => 1,
                    'class'       => 'message-input',
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitObservation::class,
        ]);
    }
}
