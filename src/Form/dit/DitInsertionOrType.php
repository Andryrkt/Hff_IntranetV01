<?php

namespace App\Form\dit;


use App\Entity\dit\DitInsertionOr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DitInsertionOrType extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        
        
        $builder
            ->add('numeroDit',
            TextType::class,
            [
                'label' => 'Numéro DIT',
            ])
            ->add('numeroOR',
            TextType::class,
            [
                'label' => 'Numéro OR',
            ])
            ->add('file', 
            FileType::class, 
            [
                'label' => 'Upload File',
                'required' => true,
            ]);
       ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitInsertionOr::class,
        ]);
    }


}