<?php

namespace App\Form\ddp;

use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Dto\ddp\DdpDto;
use App\Form\common\AgenceServiceType;
use App\Form\Common\FileUploadType;
use App\Form\common\RibType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

        $this->addNumeroCommande($builder, $options);
        $this->addNumeroFacture($builder, $options);
        $this->addFournisseur($builder);
        $this->addAgenceServiceDebiteur($builder, $options);
        $this->addFile($builder);
    }

    public function addNumeroFacture(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'numeroFacture',
            ChoiceType::class,
            [
                'label' => 'N° Facture fournisseur *',
                'required' => false,
                'choices'   => $options['data']->numeroFacture,
                'multiple'  => true,
                'expanded'  => false,
                'attr'      => [
                    'disabled' => $options['data']->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE,
                    'data-typeId' => $options['data']->typeDdp->getId()
                ]
            ]
        )
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();

                $form->add(
                    'numeroFacture',
                    ChoiceType::class,
                    [
                        'label' => 'N° Facture *',
                        'choices'   => $data['numeroFacture'] ?? [],
                        'multiple'  => true,
                        'expanded'  => false,
                        'required' => false
                    ]
                );
            });
    }
    public function addNumeroCommande(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'numeroCommande',
            ChoiceType::class,
            [
                'label'     => 'N° Commande fournisseur *',
                'choices'   =>  $options['data']->numeroCommande,
                'multiple'  => true,
                'expanded'  => false,
                'attr'      => [
                    'disabled' => $options['data']->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE,
                ]
            ]
        )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) use ($options) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    if ($options['data']->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
                        $form->add(
                            'numeroCommande',
                            ChoiceType::class,
                            [
                                'label'     => 'N° Commande *',
                                'choices'   => $data['numeroCommande'],
                                'multiple'  => true,
                                'expanded'  => false,
                            ]
                        );
                    }
                }
            );
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

    public function addAgenceServiceDebiteur(FormBuilderInterface $builder, array $options)
    {
        $dto = $options['data'] ?? null;
        $agence = $dto && isset($dto->debiteur['agence']) ? $dto->debiteur['agence'] : null;
        $service = $dto && isset($dto->debiteur['service']) ? $dto->debiteur['service'] : null;

        $builder->add('debiteur', AgenceServiceType::class, [
            'agence_label' => 'Agence Debiteur *',
            'service_label' => 'Service Débiteur *',
            'data_agence' => $agence,
            'data_service' => $service,
            'agence_attr' => [
                'disabled' => true,
                'class' => 'agenceDebiteur'
            ],
            'service_attr' => [
                'disabled' => true,
                'class' => 'serviceDebiteur'
            ]
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

    public function getBlockPrefix()
    {
        return 'demande_paiement';
    }
}
