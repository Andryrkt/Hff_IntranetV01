<?php

namespace App\Form\tik;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\utilisateur\User;
use App\Service\SessionManagerService;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\admin\tik\TkiCategorieRepository;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DetailTikType extends AbstractType
{
    private $sousCategorieRepository;
    private $categoriesRepository;
    const DAY_PART = [
        'AM (08:00 - 12:00)' => 'AM',
        'PM (13:30 - 17:30)' => 'PM'
    ];

    public function __construct(EntityManagerInterface $em)
    {
        $this->sousCategorieRepository = $em->getRepository(TkiSousCategorie::class);
        $this->categoriesRepository = $em->getRepository(TkiCategorie::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categorie', EntityType::class, [
                'label'        => 'Catégorie',
                'class'        => TkiCategorie::class,
                'choice_label' => 'description',
                'query_builder' => function (TkiCategorieRepository $TkiCategorieRepository) {
                    return $TkiCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
                'data'         => $options['data']->getCategorie(),
                'attr'         => [
                    'class'    => 'categorie',
                ],
                'placeholder'  => '-- Choisir une catégorie --',
                'multiple'     => false,
                'expanded'     => false,
                'required'     => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $sousCategorie = [];
                if ($data && $data->getCategorie()) {
                    $sousCategorie = $data->getCategorie()->getSousCategories();
                }

                $autresCategories = [];
                if ($data && $data->getSousCategorie()) {
                    $autresCategories = $data->getSousCategorie()->getAutresCategories();
                }

                $form
                    ->add('sousCategorie', EntityType::class, [
                        'label' => 'Sous Catégorie',
                        'class' => TkiSousCategorie::class,
                        'choice_label' => 'description',
                        'placeholder' => '-- Choisir une sous categorie --',
                        'required' => false,
                        'choices' => $sousCategorie,
                        'query_builder' => function (EntityRepository $tkiCategorie) {
                            return $tkiCategorie->createQueryBuilder('sc')->orderBy('sc.description', 'ASC');
                        },
                        'attr' => ['class' => 'sous-categorie']
                    ])
                    ->add('autresCategorie', EntityType::class, [
                        'label' => 'Autres Catégories',
                        'class' => TkiAutresCategorie::class,
                        'choice_label' => 'description',
                        'placeholder' => '-- Choisir une autre categorie --',
                        'required' => false,
                        'choices' => $autresCategories,
                        'query_builder' => function (EntityRepository $tkiCategorie) {
                            return $tkiCategorie->createQueryBuilder('ac')->orderBy('ac.description', 'ASC');
                        },
                        'attr' => ['class' => 'autre-categorie']
                    ])
                ;
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                //souscategorie
                $sousCategories = [];
                if (isset($data['categorie']) && $data['categorie']) {
                    $categorieId = $data['categorie'];
                    $categorie = $this->categoriesRepository->find($categorieId);

                    if ($categorie) {
                        $sousCategories = $categorie->getSousCategories();
                    }
                }

                //autrecategorie
                $autresCategories = [];
                if (isset($data['sousCategorie']) && $data['sousCategorie']) {
                    $sousCategorieId = $data['sousCategorie'];
                    $sousCategorie = $this->sousCategorieRepository->find($sousCategorieId);

                    if ($sousCategorie) {
                        $autresCategories = $sousCategorie->getAutresCategories();
                    }
                }

                $form
                    ->add('sousCategorie', EntityType::class, [
                        'label' => 'Sous Catégorie',
                        'class' => TkiSousCategorie::class,
                        'choice_label' => 'description',
                        'placeholder' => '-- Choisir une sous categorie --',
                        'required' => false,
                        'choices' => $sousCategories,
                        'query_builder' => function (EntityRepository $tkiCategorie) {
                            return $tkiCategorie->createQueryBuilder('sc')->orderBy('sc.description', 'ASC');
                        },
                        'attr' => ['class' => 'sous-categorie']
                    ])
                    ->add('autresCategorie', EntityType::class, [
                        'label' => 'Autres Catégories',
                        'class' => TkiAutresCategorie::class,
                        'choice_label' => 'description',
                        'placeholder' => '-- Choisir une autre categorie --',
                        'required' => false,
                        'choices' => $autresCategories,
                        'query_builder' => function (EntityRepository $tkiCategorie) {
                            return $tkiCategorie->createQueryBuilder('ac')->orderBy('ac.description', 'ASC');
                        },
                        'attr' => ['class' => 'autres-categories']
                    ])
                ;
            })
            ->add('niveauUrgence', EntityType::class, [
                'label'        => 'Niveau d\'urgence',
                'choice_label' => 'description',
                'placeholder'  => '-- Choisir le niveau d\'urgence --',
                'class'        => WorNiveauUrgence::class,
                'query_builder' => function (WorNiveauUrgenceRepository $WorNiveauUrgenceRepository) {
                    return $WorNiveauUrgenceRepository
                        ->createQueryBuilder('w')
                        ->orderBy('w.description', 'DESC');
                },
                'multiple'     => false,
                'expanded'     => false,
            ])
            ->add('intervenant', EntityType::class, [
                'label'        => 'Intervenant',
                'placeholder'  => '-- Choisir un intervenant --',
                'class'        => User::class,
                'choice_label' => 'nom_utilisateur',
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository
                        ->createQueryBuilder('u')
                        ->innerJoin('u.roles', 'r')  // Jointure avec la table 'roles'
                        ->where('r.id = :roleId')  // Filtre sur l'id du rôle
                        ->setParameter('roleId', 8)
                        ->orderBy('u.nom_utilisateur', 'ASC');;
                },
                'multiple'     => false,
                'expanded'     => false,
            ])
            ->add('dateDebutPlanning', DateType::class, [
                'label'      => 'Début planning',
                'widget'     => 'single_text', // Permet de gérer la date et l'heure en un seul champ
                'required'   => false,
            ])
            ->add('dateFinPlanning', DateType::class, [
                'label'      => 'Fin planning',
                'widget'     => 'single_text',
                'required'   => false,
            ])
            ->add('partOfDay', ChoiceType::class, [
                'label' => 'Période de la journée du planning',
                'choices' => self::DAY_PART,
                'placeholder' => '-- Choisir une période de la journée --',
                'required' => false,
            ])
            ->add('commentaires', TextareaType::class, [
                'label'    => false,
                'required' => true,
                'attr'     => [
                    'rows'  => 5,
                    'class' => 'mt-3',
                ],
                'mapped'   => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeSupportInformatique::class
        ]);
    }
}
