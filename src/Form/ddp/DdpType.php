<?php

namespace App\Form\ddp;

use App\Dto\ddp\DdpDto;
use App\Form\common\AgenceServiceType;
use App\Form\Common\FileUploadType;
use App\Form\common\RibType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DdpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add(
                'motif',
                TextType::class,
                [
                    'label' => 'Motif',
                    'required' => false
                ]
            )
            ->add(
                'contact',
                TextType::class,
                [
                    'label' => 'Contact',
                    'required' => false
                ]
            )
            ->add(
                'modePaiement',
                ChoiceType::class,
                [
                    'label'     => 'Mode de paiement *',
                    'choices'   =>  $options['data']->choiceModePaiement,
                    'multiple'  => false,
                    'expanded'  => false,
                    'data' => 'VIREMENT'
                ]
            )
            ->add(
                'devise',
                ChoiceType::class,
                [
                    'label'     => 'Devise *',
                    'choices'   =>  $options['data']->choiceDevise,
                    'multiple'  => false,
                    'expanded'  => false,
                ]
            )
            ->add(
                'montantAPayer',
                TextType::class,
                [
                    'label' => 'Montant à payer *'
                ]
            )
        ;

        $this->addFournisseur($builder);
        $this->addAgenceServiceDebiteur($builder);
        $this->addFile($builder);
    }

    public function addFournisseur(FormBuilderInterface $builder)
    {
        $builder->add(
            'numeroFournisseur',
            TextType::class,
            [
                'label' => 'Fournisseur *',
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
            ]
        )
            ->add(
                'beneficiaire',
                TextType::class,
                [
                    'label' => 'Bénéficiaire *',
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ]
                ]
            )
            ->add('ribFournisseur', RibType::class)
        ;
    }

    public function addAgenceServiceDebiteur(FormBuilderInterface $builder)
    {
        $builder->add('debiteur', AgenceServiceType::class, [
            'inherit_data' => true,
            'agence_label' => 'Agence Debiteur *',
            'service_label' => 'Service Débiteur *',
        ]);
    }

    public function addFile(FormBuilderInterface $builder)
    {
        $builder->add('pieceJoint01', FileUploadType::class, [

            'label' => 'Pièce Jointe 01 (PDF)',
            'allowed_mime_types' => ['application/pdf'],
            'attr' => ['accept' => 'application/pdf'],
            'max_size' => '5M'
        ])
            ->add('pieceJoint02', FileUploadType::class, [
                'label' => 'Pièce Jointe 02 (PDF)',
                'allowed_mime_types' => ['application/pdf'],
                'attr' => ['accept' => 'application/pdf'],
                'max_size' => '5M'
            ])
            ->add('pieceJoint03', FileUploadType::class, [
                'label' => 'Pièce Jointe 03 (PDF)',
                'allowed_mime_types' => ['application/pdf'],
                'attr' => ['accept' => 'application/pdf'],
                'max_size' => '5M'
            ])
            ->add('pieceJoint04', FileUploadType::class, [
                'label' => 'Pièce Jointe 04 (PDF)',
                'allowed_mime_types' => ['application/pdf'],
                'attr' => ['accept' => 'application/pdf'],
                'max_size' => '5M'
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DdpDto::class
        ]);
    }
}
