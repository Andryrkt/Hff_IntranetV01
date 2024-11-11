<?php

namespace App\Form\admin\tik;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TkiAutresCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idSousCategorie', IntegerType::class, [
                'label' => 'ID Sous-Catégorie',
            ])
            ->add('description', ChoiceType::class, [
                'label' => 'Description',
                'choices' => [
                    'Normal' => 'Normal',
                    'Urgent' => 'Urgent',
                    'Critique' => 'Critique',
                ],
            ])
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de Création',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiAutresCategorieType::class,
        ]);
    }
}
?>