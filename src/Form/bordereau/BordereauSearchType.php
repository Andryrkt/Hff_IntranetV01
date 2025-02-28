<?php
namespace App\Form\bordereau;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BordereauSearchType extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  $builder
        ->add('numInv', TextType::class, [
            'label' => "Numero inventaire",
            'required' => true
        ]);
    
    } 
}