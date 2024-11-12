<?php

namespace App\Form\admin\tik;

use Symfony\Component\Form\AbstractType;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\tik\TkiAutresCategorie;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TkiSousCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Description',
            ])
            ->add('autresCategories', EntityType::class, [
                'label' => 'Autres catégories',
                'class' => TkiAutresCategorie::class,
                'choice_label'=> 'description',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false, // Ajoutez cette ligne
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
