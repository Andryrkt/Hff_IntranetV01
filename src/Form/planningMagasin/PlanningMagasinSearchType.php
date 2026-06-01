<?php

namespace App\Form\planningMagasin;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Entity\planningMagasin\PlanningMagasinSearch;
use App\Model\planningMagasin\PlanningMagasinModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PlanningMagasinSearchType extends AbstractType
{
    use Transformation;

    private $planningMagasinModel;


    const INTERNE_EXTERNE = [
        'TOUS' => 'TOUS',
        'INTERNE' => 'INTERNE',
        'EXTERNE' => 'EXTERNE'
    ];
    const FACTURE = [
        'TOUS' => 'TOUS',
        ' DEJA FACTURE' => 'FACTURE',
        'ENCOURS' => 'ENCOURS'
    ];
    const PLANIFIER = [
        // 'TOUS' => 'TOUS',
        'PLANIFIE' => 'PLANIFIE',
        'NON PLANIFIE' => 'NON_PLANIFIE',
    ];
    const TYPELIGNE = [
        'TOUTES' => 'TOUTES',
        'PIECES MAGASIN' => 'PIECES_MAGASIN',
        'ACHATS LOCAUX' => 'ACHAT_LOCAUX',
        'LUBRIFIANTS' => 'LUBRIFIANTS'
    ];
    const REPARATION_REALISE = [
        'ATE TANA' => 'ATE TANA',
        'ATE STAR' => 'ATE STAR',
        'ATE MAS' => 'ATE MAS',
        'ATE TMV' => 'ATE TMV',
        'ATE FTU' => 'ATE FTU',
        'ATE ABV' => 'ATE ABV',
        'ATE LEV' => 'ATE LEV',
    ];

    public function __construct()
    {
        $this->planningMagasinModel = new PlanningMagasinModel();
    }

    private function serviceDebiteur(string $codeAgence = "-0")
    {
        $serviceDebiteur = $this->planningMagasinModel->recuperationServiceDebite($codeAgence);

        $result = [];
        if ($serviceDebiteur && !empty($serviceDebiteur)) {
            foreach ($serviceDebiteur as $item) {
                $result[$item['text']] = $item['value'];
            }
        }

        return $result;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $multisuccursale = $options['multisuccursale'];
        $agenceAutorises = $options['agencesAutorises'];
        $codeAgenceDefaut = $options['codeAgenceDefaut'];

        $agenceDebite = $this->planningMagasinModel->recuperationAgenceDebite($multisuccursale, $agenceAutorises, $codeAgenceDefaut);

        $codeAgence = $options['data']->getAgence() ?? $codeAgenceDefaut;
        $serviceDebite = $this->serviceDebiteur($codeAgence);

        $builder
            ->add('numeroDevis', TextType::class, [
                'label' => 'N° Devis',
                'required' => false,
            ])
            ->add('commercial', TextType::class, [
                'label' =>  'Commercial',
                'required' => false,
            ])
            ->add('numOr', TextType::class, [
                'label' => "N° Commande",
                'required' => false
            ])

            ->add('refcde', TextType::class, [
                'label' => "PO Client",
                'required' => false
            ])
            ->add('numParc', TextType::class, [
                'label' => "Client ",
                'required' => false
            ])

            ->add('agenceDebite', ChoiceType::class, [
                'label'       => 'Agence',
                'required'    => false,
                'choices'     => $agenceDebite,
                'placeholder' => " -- Choisir une agence --",
                'data'        => $codeAgenceDefaut,
                'disabled'    => !$multisuccursale && (empty($agenceAutorises) || count($agenceAutorises) == 1)
            ])
            ->add(
                'orBackOrder',
                CheckboxType::class,
                [
                    'label' => 'Commande avec Back Order',
                    'required' => false
                ]
            )
            ->add('serviceDebite', ChoiceType::class, [
                'label'       => 'Service ',
                'multiple'    => true,
                'choices'     => $serviceDebite,
                'placeholder' => " -- Choisir un service--",
                'expanded'    => true,
                'data'        => array_values($serviceDebite)
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $codeAgenceDebite = $data['agenceDebite'];
                $serviceDebite = $this->serviceDebiteur($codeAgenceDebite);

                $form->add('serviceDebite', ChoiceType::class, [
                    'label'       => 'Service: ',
                    'multiple'    => true,
                    'choices'     => $serviceDebite,
                    'placeholder' => " -- choisir service--",
                    'expanded'    => true,
                ]);
            })
            ->add('months', ChoiceType::class, [
                'choices' => [
                    '3 mois suivant'    => 3,
                    '6 mois suivant'    => 6,
                    '12 mois suivant'   => 12,
                    '12 mois précédent' => 13,
                    'Année encours'     => 9,
                    'Année suivante'    => 11,
                    'Année précédente'  => 14,
                ],
                'expanded' => false, // Utiliser une liste déroulante
                'multiple' => false, // Sélectionner une seule valeur
                'label'    => 'Nombre de mois',
                'data'     => 3
            ])
            ->add(
                'orNonValiderDw',
                CheckboxType::class,
                [
                    'label' => 'BC non valider DW',
                    'required' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'       => PlanningMagasinSearch::class,
            'planningDetaille' => false,
            'multisuccursale'  => false,
            'codeAgenceDefaut' => '',
            'agencesAutorises' => [],
        ]);
    }
}
