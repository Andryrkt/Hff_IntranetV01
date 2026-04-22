<?php

namespace App\Form\da;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Entity\admin\Agence;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\da\DaSearch;
use App\Entity\da\DemandeAppro;
use App\Traits\PrepareAgenceServiceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DaSearchType extends AbstractType
{
    use PrepareAgenceServiceTrait;
    use MarkupIconTrait;

    private $agenceRepository;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isApproUser = $options['codeAgence'] == '80' && $options['codeService'] == 'APP';
        $choices = $this->getChoicesData($options['estAppro']);
        $traiterLists = $this->getTraiterLists();

        $builder
            // --- Sections de base ---
            ->add('numDit', TextType::class, ['label' => 'N° OR/DIT', 'required' => false])
            ->add('numDa', TextType::class, ['label' => 'N° DAP', 'required' => false])
            ->add('numCde', TextType::class, ['label' => 'N° Commande', 'required' => false])
            ->add('demandeur', TextType::class, ['label' => 'Demandeur', 'required' => false])

            // --- Filtres d'Affichage ---
            ->add('afficherCloturees', CheckboxType::class, [
                'label'    => 'Inclure les DA clôturées',
                'required' => false
            ])
            ->add('afficherDaTraiter', CheckboxType::class, [
                'label'    => "N'afficher que les DA à traiter",
                'required' => false,
                'attr'     => ['data-is-appro-user' => $isApproUser ? '1' : '0']
            ])

            // --- Statuts ---
            ->add('statutDA', ChoiceType::class, [
                'placeholder' => '-- Choisir un statut --',
                'label'       => 'Statut de la DA',
                'choices'     => $choices['statut_da'],
                'required'    => false,
                'choice_attr' => function ($choice, $key, $value) use ($traiterLists) {
                    $attr = [];
                    if (in_array($value, $traiterLists['da_appro'])) $attr['data-traiter-appro'] = '1';
                    if (in_array($value, $traiterLists['da_pas_appro'])) $attr['data-traiter-pas-appro'] = '1';
                    return $attr;
                }
            ])
            ->add('statutOR', ChoiceType::class, [
                'placeholder' => '-- Choisir un statut --',
                'label'       => 'Statut',
                'choices'     => $choices['statut_or'],
                'required'    => false
            ])
            ->add('statutBC', ChoiceType::class, [
                'placeholder' => '-- Choisir un statut --',
                'label'       => 'Statut du BC',
                'choices'     => $choices['statut_bc'],
                'required'    => false,
                'choice_attr' => function ($choice, $key, $value) use ($traiterLists) {
                    $attr = [];
                    if (in_array($value, $traiterLists['bc_appro'])) $attr['data-traiter-appro'] = '1';
                    return $attr;
                }
            ])

            // --- Paramètres supplémentaires ---
            ->add('sortNbJours', ChoiceType::class, [
                'placeholder' => '-- Choisir un tri --',
                'label'       => 'Tri par Nbr Jour(s)',
                'choices'     => ['Ordre croissant' => 'asc', 'Ordre décroissant' => 'desc'],
                'required'    => false
            ])
            ->add('codeCentrale', TextType::class, ['label' => false, 'required' => false])
            ->add('desiCentrale', TextType::class, [
                'mapped' => false,
                'label' => 'Centrale rattachée à la DA',
                'required' => false
            ])
            ->add('idMateriel', TextType::class, ['label' => "N° Matériel", 'required' => false])
            ->add('typeAchat', ChoiceType::class, [
                'label'       => 'Type de la demande d\'achat',
                'placeholder' => '-- Choisir le type de la DA --',
                'choices'     => $choices['type_achat'],
                'required'    => false
            ])
            ->add('niveauUrgence', EntityType::class, [
                'label'         => 'Niveau d\'urgence',
                'label_html'    => true,
                'class'         => WorNiveauUrgence::class,
                'choice_label'  => 'description',
                'choice_value'  => 'description',
                'placeholder'   => '-- Choisir un niveau --',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')->orderBy('n.description', 'DESC');
                },
                'attr' => ['class' => 'niveauUrgence']
            ]);

        // --- Dates ---
        $this->addDateFields($builder);

        // --- Agences & Services ---
        $this->addAgenceServiceGroup($builder, 'Emetteur');
        $this->addAgenceServiceGroup($builder, 'Debiteur');

        // --- Transformers ---
        $this->addTransformers($builder);
    }

    /**
     * Centralisation des listes de choix
     */
    private function getChoicesData(bool $estAppro): array
    {
        $statut_or = StatutOrConstant::STATUT_OR;
        ksort($statut_or);

        return [
            'statut_or'  => $statut_or,
            'statut_da'  => $estAppro ? StatutDaConstant::STATUT_DA : StatutDaConstant::STATUT_DA_PAS_APPRO_NI_ADMIN,
            'statut_bc'  => $estAppro ? StatutBcConstant::STATUT_BC : StatutBcConstant::STATUT_BC_PAS_APPRO_NI_ADMIN,
            'type_achat' => [
                'Demande d’approvisionnement via OR'      => DemandeAppro::TYPE_DA_AVEC_DIT,
                'Demande d’achat direct'                  => DemandeAppro::TYPE_DA_DIRECT,
                'Demande de réapprovisionnement mensuel'  => DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
                'Demande de réapprovisionnement ponctuel' => DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL
            ]
        ];
    }

    /**
     * Listes utilisées pour le filtrage JS "DA à traiter"
     */
    private function getTraiterLists(): array
    {
        return [
            'da_appro'     => StatutDaConstant::TRAITER_APPRO_LIST,
            'bc_appro'     => StatutBcConstant::TRAITER_APPRO_LIST,
            'da_pas_appro' => StatutDaConstant::TRAITER_AUTRES_LIST
        ];
    }

    /**
     * Ajout des champs de date
     */
    private function addDateFields(FormBuilderInterface $builder)
    {
        $dateFields = [
            'dateDebutCreation'    => 'Date création (début)',
            'dateFinCreation'      => 'Date création (fin)',
            'dateDebutfinSouhaite' => 'Date fin souhaitée (début)',
            'dateFinFinSouhaite'   => 'Date fin souhaitée (fin)',
        ];

        foreach ($dateFields as $name => $label) {
            $builder->add($name, DateType::class, [
                'widget'   => 'single_text',
                'label'    => $label,
                'required' => false,
            ]);
        }
    }

    /**
     * Gestion groupée des agences et de leurs services dépendants
     */
    private function addAgenceServiceGroup(FormBuilderInterface $builder, string $type)
    {
        $agenceField = 'agence' . $type;
        $serviceField = 'service' . $type;
        $labelType = ($type === 'Emetteur') ? 'émetteur' : 'débiteur';

        $builder->add($agenceField, EntityType::class, [
            'label'        => "Agence " . $labelType,
            'class'        => Agence::class,
            'choice_label' => function (Agence $agence): string {
                return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
            },
            'placeholder'  => '-- Choisir une agence --',
            'required'     => false,
            'attr'         => ['class' => $agenceField]
        ]);

        // Listener pour l'initialisation (PRE_SET_DATA)
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($agenceField, $serviceField, $labelType) {
            $data = $event->getData();
            $method = 'get' . ucfirst($agenceField);

            $services = ($data && method_exists($data, $method) && $data->$method())
                ? $data->$method()->getServices()
                : [];

            $this->addServiceField($event->getForm(), $serviceField, "Service " . $labelType, $services);
        });

        // Listener pour la soumission (PRE_SUBMIT)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($agenceField, $serviceField, $labelType) {
            $data = $event->getData();
            $services = [];

            if (isset($data[$agenceField]) && $data[$agenceField]) {
                $agence = $this->agenceRepository->find($data[$agenceField]);
                if ($agence) {
                    $services = $agence->getServices();
                }
            }

            $this->addServiceField($event->getForm(), $serviceField, "Service " . $labelType, $services);
        });
    }

    /**
     * Ajout dynamique du champ service
     */
    private function addServiceField($form, string $name, string $label, $services)
    {
        $form->add($name, EntityType::class, [
            'label'         => $label,
            'class'         => Service::class,
            'choice_label'  => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            },
            'placeholder'   => '-- Choisir un service --',
            'choices'       => $services,
            'required'      => false,
            'query_builder' => function (ServiceRepository $serviceRepository) {
                return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
            },
            'attr'          => ['class' => $name]
        ]);
    }

    /**
     * Ajout des transformers pour gérer les tableaux (cas DA à traiter)
     */
    private function addTransformers(FormBuilderInterface $builder)
    {
        $transformer = new CallbackTransformer(
            function ($value) {
                return is_array($value) ? null : $value;
            },
            function ($value) {
                return $value;
            }
        );

        $builder->get('statutDA')->addModelTransformer($transformer);
        $builder->get('statutOR')->addModelTransformer($transformer);
        $builder->get('statutBC')->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => DaSearch::class,
            'estAppro'    => false,
            'codeAgence'  => null,
            'codeService' => null,
        ]);
    }
}
