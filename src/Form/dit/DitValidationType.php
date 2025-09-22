<?php

namespace App\Form\dit;


use App\Model\dit\DitModel;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\DependencyInjection\ContainerInterface;


class DitValidationType extends AbstractType
{

    const SERVICE_INTERVENANT = [
        'ATELIER' => 'ATE',
        'MOBILE ASSETS' => 'MAS',
        'FORMATION' => 'FOR',
        'GARANTIE' => 'GAR',
        'MAGASIN' => 'NEG',
        'ASSURENCE' => 'ASS'
    ];

    private $ditModel;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->ditModel = $this->container->get(DitModel::class);
    }

    private function section()
    {
        $section = $this->ditModel->recuperationSectionValidation();
        $sections = [];
        foreach ($section as $value) {
            $sections[] = $value['atab_code'] . ' ' . $value['atab_lib'];
        }
        return $sections;
    }

    private function codeSection()
    {
        $section = $this->ditModel->recuperationSectionValidation();
        $sections = [];
        foreach ($section as $value) {
            $sections[] = $value['atab_code'];
        }
        return $sections;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'idServiceIntervenant',
                ChoiceType::class,
                [
                    'label' => 'Service',
                    'choices' => self::SERVICE_INTERVENANT,
                    'placeholder' => '-- Choisir une service --',
                    'required' => true,
                ]
            )
            ->add(
                'codeSection',
                ChoiceType::class,
                [
                    'label' => 'Section',
                    'choices' => array_combine($this->section(), $this->codeSection()),
                    'placeholder' => '-- Choisir une section --',
                    'required' => true,
                ]
            )
            ->add(
                'observationDirectionTechnique',
                TextareaType::class,
                [
                    'label' => 'Observation D.T',
                    'required' => true,
                    'attr' => [
                        'rows' => 5,
                    ],
                ]
            )
        ;
    }

    // public function getParent()
    // {
    //     return demandeInterventionType::class;
    // }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeIntervention::class,
        ]);
    }
}
