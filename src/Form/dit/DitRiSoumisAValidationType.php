<?php

namespace App\Form\dit;


use Symfony\Component\Form\AbstractType;
use App\Entity\dit\DitRiSoumisAValidation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class DitRiSoumisAValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $itvAfficher = $options['itvAfficher'];
         $tab = [];
         foreach ($itvAfficher as  $value) {
            $tab[] = (int)$value['numeroitv'];
         }

    
       
        $builder
        
            ->add('numeroDit',
            TextType::class,
            [
                'mapped' => false,
                'required' => false,
                'label' => 'Numéro DIT',
                'data' => $options['data']->getNumeroDit(),
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('numeroOR',
            IntegerType::class,
            [
                'label' => 'Numéro OR *',
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => 8,
                        'maxMessage' => 'Le numéro OR ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'min' => 0,
                    'pattern' => '\d*', // Permet uniquement l'entrée de chiffres
                ],
            ])
            ->add('pieceJoint01', 
            FileType::class, 
            [
                'label' => 'Upload File',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuiller sélectionner la facture à soumettre .', // Message d'erreur si le champ est vide
                    ]),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ])
                ],
            ])
            ->add('action', 
            TextType::class, 
            [
                'label' => 'numero Itv (Possibilité de saisir plusieurs interventions, merci de les séparer par des points virgules ";")',
                'data' => implode(';',$tab),
                'required' => true
            ])
       ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DitRiSoumisAValidation::class,
            'itvAfficher' => null,
        ]);
    }

}