<?php

namespace App\Form\admin\tik;

use App\Entity\admin\tik\TkiCommentaires;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TkiCommentairesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentaires', TextareaType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'Entrer votre commentaire ici',
                    'minlength'   => '1',
                    'maxlength'   => '1500'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiCommentaires::class,
        ]);
    }
}
?>