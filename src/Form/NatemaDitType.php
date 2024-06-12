<?php

namespace App\Form;

use App\Entity\Agence;
use App\Entity\Societte;
use App\Entity\Application;
use App\Entity\CategorieATEAPP;
use App\Entity\WorTypeDocument;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RoleRepository;
use App\Entity\DemandeIntervention;
use App\Entity\Service;
use App\Entity\WorNiveauUrgence;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\File;


class NatemaDitType extends AbstractType
{
   

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        
        $builder
        ->add('agenceEmetteur', 
        EntityType::class,
        [
            'label' => 'Agence ',
            'placeholder' => '-- Choisir une agence  --',
            'class' => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            }
        ])
        ->add('agenceDebiteur', 
        EntityType::class,
        [
            'label' => 'Agence Debiteur',
            'placeholder' => '-- Choisir une agence Debiteur --',
            'class' => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            }
        ])
        ->add('serviceEmetteur', 
        EntityType::class,
        [
            'label' => 'Service Emetteur',
            'placeholder' => '-- Choisir une service emetteur --',
            'class' => Service::class,
            'choice_label' => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            }
        ])
        ->add('serviceDebiteur', 
        EntityType::class,
        [
            'label' => 'Service Débiteut',
            'placeholder' => '-- Choisir une service débiteur --',
            'class' => Service::class,
            'choice_label' => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            }
        ])
        ->add('objetDemande',
        TextType::class,
        [
            'label' => 'Objet'
        ])
        ->add('detailDemande',
        TextType::class,
        [
            'label' => 'Demande(les détails de votre demande)'
        ])
        ->add('pieceJoint03',
        FileType::class, 
        [
            'label' => 'Pièce Joint 03 (PDF, JPEG, XLSX, DOCX)',
            'required' => true,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, JPEG, XLSX, or DOCX file.',
                ])
            ],
        ])
        ->add('pieceJoint02',
        FileType::class, 
        [
            'label' => 'Pièce Joint 02 (PDF, JPEG, XLSX, DOCX)',
            'required' => true,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, JPEG, XLSX, or DOCX file.',
                ])
            ],
        ]
        )
        ->add('pieceJoint01',
        FileType::class, 
        [
            'label' => 'Pièce Joint 01 (PDF, JPEG, XLSX, DOCX)',
            'required' => true,
            'constraints' => [
                new NotNull(['message' => 'Please upload a file.']),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF, JPEG, XLSX, or DOCX file.',
                ])
            ],
        ]
        )
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeIntervention::class,
        ]);
    }


}