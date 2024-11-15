<?php

namespace App\Form\admin\tik;


use Symfony\Component\Form\AbstractType;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TkiAutresCategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Déscription de l\'autre catégorie *',
            ])
            ->add('sousCategories', EntityType::class, [
                'label' => 'Sous-catégories liée(s) *',
                'placeholder'  => '-- Choisir une ou des sous-catégorie(s) --',
                'class' => TkiSousCategorie::class,
                'choice_label'=> 'description',
                'multiple' => true,
                'expanded' => false
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiAutresCategorie::class,
        ]);
    }
}
?>