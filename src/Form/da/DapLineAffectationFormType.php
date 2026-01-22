<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproParentLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DapLineAffectationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var DemandeApproParentLine $dapLine */
            $dapLine = $event->getData();

            $articleStocke = $dapLine->getArticleStocke();

            $form
                ->add('artRefp', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'disabled' => $articleStocke,
                ])
                ->add('artDesi', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ],
                    'required' => false,
                    'disabled' => $articleStocke,
                ])
                ->add('dateFinSouhaite', DateType::class, [
                    'label' => false,
                    'required' => false,
                    'widget' => 'single_text',
                    'disabled' => $articleStocke,
                    'constraints' => [
                        new NotBlank(['message' => 'la date ne doit pas être vide'])
                    ]
                ])
                ->add('qteDem', TextType::class,  [
                    'label' => false,
                    'required' => false,
                    'disabled' => $articleStocke,
                ])
                ->add('commentaire', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'empty_data' => '',
                    'disabled' => $articleStocke,
                ])
                ->add('artConstp', TextType::class, [
                    'label' => false,
                    'required' => false,
                ])
                ->add('numeroFournisseur', TextType::class, [
                    'label' => false,
                    'required' => false,
                ])
                ->add('nomFournisseur', TextType::class, [
                    'label' => false,
                    'required' => false
                ])
                ->add('articleStocke', CheckboxType::class, [
                    'required' => false,
                    'label'    => false,
                ])
                ->add('prixUnitaire', TextType::class, [
                    'label' => false,
                    'required' => false,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeApproParentLine::class,
        ]);
    }
}
