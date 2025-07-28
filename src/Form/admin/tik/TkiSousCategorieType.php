<?php

namespace App\Form\admin\tik;

use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TkiSousCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Déscription de la sous-catégorie *',
            ])
            ->add('autresCategories', EntityType::class, [
                'label' => 'Autres catégories liée(s)',
                'placeholder' => '-- Choisir une ou d\' autres catégorie(s) --',
                'class' => TkiAutresCategorie::class,
                'choice_label' => 'description',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiSousCategorie::class,
        ]);
    }
}
