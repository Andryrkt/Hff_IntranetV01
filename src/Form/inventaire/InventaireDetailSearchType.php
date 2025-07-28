<?php

namespace App\Form\inventaire;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class InventaireDetailSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numInv', TextType::class, [
                'label' => "Numero inventaire",
                'required' => true,
            ]);

    }
}
