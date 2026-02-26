<?php

namespace App\Form\ddp;

use App\Entity\admin\ddp\DdpSearch;
use App\Entity\admin\ddp\TypeDemande;
use Symfony\Component\Form\AbstractType;
use App\Traits\PrepareAgenceServiceTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DdpSearchType extends AbstractType
{
    use PrepareAgenceServiceTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $this->prepareAgenceServiceChoices($options['agenceServiceAutorises'], false);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        $builder
            // --- agenceDebiteur : ChoiceType ---
            ->add('Agence', ChoiceType::class, [
                'label'       => 'Agence débiteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceDebiteur : ChoiceType ---
            ->add('service', ChoiceType::class, [
                'label'       => 'Service débiteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
            ->add('typeDemande', EntityType::class, [
                'label' => 'Type de Document',
                'class' => TypeDemande::class,
                'choice_label' => 'libelle',
                'placeholder' => '-- Choisir un type de demande--',
                'required' => false,
            ])
            ->add(
                'NumDdp',
                TextType::class,
                [
                    'label' => 'N° demande',
                    'required' => false
                ]
            )
            ->add(
                'numCommande',
                TextType::class,
                [
                    'label' => 'N° Commande',
                    'required' => false
                ]
            )
            ->add(
                'numFacture',
                TextType::class,
                [
                    'label' => 'N° facture',
                    'required' => false
                ]
            )
            ->add(
                'utilisateur',
                TextType::class,
                [
                    'label' => 'Utilisateur',
                    'required' => false
                ]
            )
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début demande',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin demande',
                'required' => false,
            ])
            ->add('statut', TextType::class, [
                'label' => 'Statut',
                'required' => false
            ])
            ->add('fournisseur', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'             => DdpSearch::class,
            'agenceServiceAutorises' => [],
        ]);
    }
}
