<?php

namespace App\Form\ddp;

use App\Dto\ddp\DdpDto;
use App\Form\common\AgenceServiceType;
use App\Form\Common\FileUploadType;
use App\Form\common\RibType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DdpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ribFournisseur', RibType::class)
            ->add('debiteur', AgenceServiceType::class, [
                'inherit_data' => true,
                'agence_label' => 'Agence Debiteur *',
                'service_label' => 'Service Débiteur *',
            ])
            ->add('pieceJoint01', FileUploadType::class, [

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
