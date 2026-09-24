<?php

namespace App\Form\magasin\Commande\SoumissionCommande;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;



class SoumissionCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numCmde', TextType::class, [
                'label' => 'Veuillez rentrer un numero de commande * :',
                'required' => false,
            ])
            ->add(
                'numCmdeAValider',
                HiddenType::class
            )
            ->add(
                'generatedFilePath',
                HiddenType::class
            );
    }
}
