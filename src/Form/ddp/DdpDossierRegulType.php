<?php

namespace App\Form\ddp;

use App\Controller\Controller;
use App\Entity\ddp\DemandePaiement;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\AgenceRepository;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DdpDossierRegulType extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('pieceJoint01',
        FileType::class,
        [
            'mapped' => false, 
            'label' => 'contrôle livraison (PDF)',
            'required' => true,
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
        ]);
    }
}
