<?php

namespace App\Form\da;

use App\Entity\da\DemandeApproParentLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    'required' => true,
                    'disabled' => $articleStocke,
                    'attr' => [
                        'class' => 'da-art-refp',
                        'autocomplete' => 'off',
                    ],
                ])
                ->add('artDesi', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'class' => 'da-art-desi',
                    ],
                    'required' => false,
                    'disabled' => $articleStocke,
                ])
                ->add('artConstp', TextType::class, [
                    'label' => false,
                    'required' => false,
                ])
                ->add('numeroFournisseur', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'disabled' => $articleStocke,
                ])
                ->add('nomFournisseur', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'disabled' => $articleStocke,
                    'attr' => [
                        'class' => 'da-nom-frn',
                        'autocomplete' => 'off',
                    ],
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
