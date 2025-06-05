<?php


namespace App\Form\planning;


use Doctrine\ORM\EntityRepository;
use App\Model\planning\PlanningModel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\planning\PlanningSearch;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Entity\admin\dit\WorTypeDocument;
use setasign\Fpdi\PdfParser\Filter\Flate;
use App\Entity\admin\dit\WorNiveauUrgence;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\admin\dit\WorTypeDocumentRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PlanningSearchType extends AbstractType
{
    use Transformation;

    private $planningModel;


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
    ];

    public function __construct()
    {
        $this->planningModel = new PlanningModel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$serviceDebite = $planningModel->recuperationServiceDebite();
        $agence = $this->transformEnSeulTableauAvecKey($this->planningModel->recuperationAgenceIrium());
        $annee = $this->planningModel->recuperationAnneeplannification();
        $agenceDebite = $this->planningModel->recuperationAgenceDebite();
        $section = $this->planningModel->recuperationSection();
        $builder
            ->add('agence', ChoiceType::class, [
                'label' =>  'Agence Travaux',
                'required' => false,
                'choices' => $agence,
                'placeholder' => ' -- Choisir une agence --',
                /*'choice_label' => function($choice,$key,$values){
                      return $values;  
                    },*/


            ])
            ->add('niveauUrgence', EntityType::class, [
                'label' => 'Niveau d\'urgence',
                'class' => WorNiveauUrgence::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un niveau--',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
                'attr' => [
                    'class' => 'niveauUrgence'
                ]
            ])

            // ->add('annee', ChoiceType::class,[
            //     'label' =>'Année',
            //     'required' =>true,
            //     'choices' => $annee,
            //     'placeholder' => " -- Choisir l'année --",
            //     'data' => date('Y')
            // ])
            ->add('interneExterne', ChoiceType::class, [
                'label' => 'Interne / Externe',
                'required' => true,
                'choices' => self::INTERNE_EXTERNE,
                'attr' => ['class' => 'interneExterne'],

            ])

            ->add('typeligne', ChoiceType::class, [
                'label' => 'Type de ligne',
                'required' => False,
                'choices' => self::TYPELIGNE,
                'attr' => ['class' => 'typeligne'],
                'data' => 'TOUTES',
                'placeholder' => False
            ])

            ->add('facture', ChoiceType::class, [
                'label' => 'Facturation',
                'required' => true,
                'choices' => self::FACTURE,
                'attr' => ['class' => 'facture'],
                'data' => 'ENCOURS'
            ])
            ->add('plan', ChoiceType::class, [
                'label' => 'Planification',
                'required' => true,
                'choices' => self::PLANIFIER,
                'attr' => ['class' => 'plan'],
                'data' => 'PLANIFIE'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => $options['planningDetaille'] ? 'Date Début Planning' : 'Date Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => $options['planningDetaille'] ? 'Date Fin Planning' : 'Date Fin',
                'required' => false,
            ])
            ->add('numOr', TextType::class, [
                'label' => "N° OR",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Série",
                'required' => false
            ])
            ->add('idMat', TextType::class, [
                'label' => "Id Matériel",
                'required' => false
            ])
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('casier', TextType::class, [
                'label' => "Casier",
                'required' => false
            ])
            ->add('agenceDebite', ChoiceType::class, [
                'label' => 'Agence Débiteur',
                'required' => false,
                'choices' => $agenceDebite,
                'placeholder' => " -- Choisir une agence --",

            ])
            ->add(
                'section',
                ChoiceType::class,
                [
                    'label' => 'Section',
                    'required' => false,
                    'choices' => $section,
                    'placeholder' => "-- Choisir une section --"
                ]

            )
            ->add(
                'orBackOrder',
                CheckboxType::class,
                [
                    'label' => 'OR avec Back Order',
                    'required' => false
                ]
            )
            ->add('serviceDebite', ChoiceType::class, [
                'label' => 'Service Débiteur',
                'multiple' => true,
                'choices' => [],
                'placeholder' => " -- Choisir un service--",
                'expanded' => true,
            ])
            ->add(
                'typeDocument',
                EntityType::class,
                [
                    'label' => 'Type de document ',
                    'placeholder' => '-- Choisir--',
                    'class' => WorTypeDocument::class,
                    'choice_label' => 'description',
                    'required' => false,
                    'query_builder' => function (WorTypeDocumentRepository $repository) {
                        return $repository->createQueryBuilder('w')
                            ->where('w.id >= :id')
                            ->setParameter('id', 5)
                            ->orderBy('w.description', 'ASC');
                    }
                ]
            )
            ->add(
                'reparationRealise',
                ChoiceType::class,
                [
                    'label' => "Réparation réalisé par *",
                    'choices' => self::REPARATION_REALISE,
                    'placeholder' => '-- Choisir le répartion réalisé --',
                    'required' => false,

                ]
            )
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $serviceDebite = $this->transformEnSeulTableauAvecKeyService($this->planningModel->recuperationServiceDebite($data['agenceDebite']));

                $form->add('serviceDebite', ChoiceType::class, [
                    'label' => 'Service Débiteur : ',
                    'multiple' => true,
                    'choices' => $serviceDebite,
                    'placeholder' => " -- choisir service--",
                    'expanded' => true,
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
                    'label' => 'OR non valider DW',
                    'required' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlanningSearch::class,
            'planningDetaille' => false,
        ]);
    }
}
