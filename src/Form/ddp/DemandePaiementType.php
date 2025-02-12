<?php

namespace App\Form\ddp;

use App\Entity\ddp\DemandePaiement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DemandePaiementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroFournisseur', TextType::class,
                [
                    'label' => 'Fournisseur'
                ])
            ->add('numeroCommande', TextType::class,
            [
                'label' => 'N° Commande'
            ])
            ->add('numeroFacture',TextType::class,
                [
                    'label' => 'N° Facture'
                ])
            ->add('beneficiaire', TextType::class,
                [
                    'label' => 'Bénéficiaire'
                ])
            ->add('motif', TextType::class,
                [
                    'label' => 'Motif'
                ])
            ->add('agenceDebiter', TextType::class,
            [
                'label' => 'Agence à débiter'
            ])
            ->add('serviceDebiter', TextType::class,
            [
                'label' => 'Service à débiter'
            ])
            ->add('ribFournisseur', TextType::class,
            [
                'label' => 'RIB'
            ])
            ->add('contact', TextType::class,
            [
                'label' => 'Contact'
            ])
            ->add('modePaiement', TextType::class,
            [
                'label' => 'Mode de paiement'
            ])
            ->add('devise', TextType::class,
            [
                'label' => 'Devise'
            ])
            ->add('montantAPayer', TextType::class,
            [
                'label' => 'Montant à payer'
            ])
            ->add('pieceJoint01',
            FileType::class,
            [
                'label' => 'Pièce Jointe 01 (PDF)',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            // 'image/jpeg',
                            // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandePaiement::class,
        ]);
    }
}