<?php


namespace App\Form\planning;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class MoisAvantType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {            
                $builder
                    ->add('months', ChoiceType::class, [
                        'choices' => [
                            '3 mois avant' => 3,
                            '6 mois avant' => 6,
                            '9 mois avant' => 9,
                            '11 mois avant' => 11,
                        ],
                        'expanded' => false, // Utiliser une liste déroulante
                        'multiple' => false, // Sélectionner une seule valeur
                        'label' => 'Nombre de mois avant', // Label du champ
                        'data' => 3
                    ]);
                ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
