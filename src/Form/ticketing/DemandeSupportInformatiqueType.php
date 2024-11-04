<?php

namespace App\Form\ticketing;

use Symfony\Component\Form\AbstractType;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DemandeSupportInformatiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Mail_Demandeur', EmailType::class, [
                'required' => true,
            ])
            ->add('Categorie', ChoiceType::class, [
                'choices' => [
                    
                ],
                'required' => true,
            ])
            ->add('Agence_Emetteur', ChoiceType::class, [
                'choices' => [
                    
                ],
                'required' => true,
            ])
            ->add('Agence_Debiteur', ChoiceType::class, [
                'choices' => [
                    
                ],
                'required' => true,
            ])
            ->add('Service_Emmetteur', ChoiceType::class, [
                'choices' => [

                ],
                'required' => true,
            ])
            ->add('Service_Debiteur', ChoiceType::class, [
                'choices' => [

                ],
                'required' => true,
            ])

            ->add('Objet_Demande', TextType::class, [
                'required' => true,
            ])
            ->add('Detail_Demande', TextareaType::class, [
                'required' => true,
            ])
            ->add('Piece_Jointe1', FileType::class, [
                'label' => 'Pièce jointe 1',
                'required' => false,
                'mapped' => false, 
            ])
            ->add('Piece_Jointe2', FileType::class, [
                'label' => 'Pièce jointe 2',
                'required' => false,
                'mapped' => false,
            ])
            ->add('Piece_Jointe3', FileType::class, [
                'label' => 'Pièce jointe 3',
                'required' => false,
                'mapped' => false,
            ])
            ->add('Date_Fin_Planning', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
    
            ->add('Date_Fin_Souhaitee', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeSupportInformatique::class,
        ]);
    }
}
?>