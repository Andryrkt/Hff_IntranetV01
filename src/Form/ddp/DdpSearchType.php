<?php

namespace App\Form\ddp;

use App\Dto\ddp\DdpSearchDto;
use App\Entity\admin\ddp\TypeDemande;
use App\Form\common\AgenceServiceType;
use App\Form\common\DateRangeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DdpSearchType extends AbstractType
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $this->prepareAgenceServiceChoices($options['allAgenceServices'], false);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        $builder
            ->add('debiteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'agence_label' => 'Agence Debiteur',
                'service_label' => 'Service Debiteur',
                'agence_placeholder' => '-- Agence Debiteur --',
                'service_placeholder' => '-- Service Debiteur --',
                'em' => $this->em,
            ])
            ->add('typeDemande', EntityType::class, [
                'label' => 'Type de Document',
                'class' => TypeDemande::class,
                'choice_label' => 'libelle',
                'placeholder' => '-- Choisir un type de demande--',
                'required' => false,
            ])
            ->add(
                'numDdp',
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
            ->add('dateCreation', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date création (début)',
                'fin_label' => 'Date création (fin)',

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
            'data_class' => DdpSearchDto::class,
        ]);
    }
}
