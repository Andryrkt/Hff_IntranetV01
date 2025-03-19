<?php

namespace App\Form\dom;


use App\Entity\dom\Dom;


use App\Entity\admin\Agence;

use App\Entity\admin\dom\Rmq;
use App\Controller\Controller;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\Personnel;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\dom\Indemnite;
use Symfony\Component\Form\FormEvent;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Form\FormEvents;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dom\SousTypeDocument;
use App\Repository\admin\dom\CatgRepository;
use App\Repository\admin\dom\SousTypeDocumentRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class DomForm1Type extends AbstractType
{
    private $em;

    const SALARIE = [
        'PERMANENT' => 'PERMANENT',
        'TEMPORAIRE' => 'TEMPORAIRE',
    ];

    public function __construct()
    {
        $this->em = Controller::getEntity();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(
                'agenceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Agence',
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
                    'data' => $options["data"]->getAgenceEmetteur() ?? null
                ]
            )

            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped' => false,
                    'label' => 'Service',
                    'required' => false,
                    'attr' => [
                        'readonly' => true,
                    ],
                    'data' => $options["data"]->getServiceEmetteur() ?? null
                ]
            )
            ->add(
                'sousTypeDocument',
                EntityType::class,
                [
                    'label' => 'Type de Mission',
                    'class' => SousTypeDocument::class,
                    'choice_label' => 'codeSousType',
                    'query_builder' => function (SousTypeDocumentRepository $repo) {
                        return $repo->createQueryBuilder('s')
                            ->where('s.id NOT IN (:excludedIds)')
                            ->setParameter('excludedIds', [5, 11]); // id de mutation et trop perçu
                    }
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();
                $sousTypedocument = $data->getSousTypeDocument();

                if (substr($data->getAgenceEmetteur(), 0, 2) === '50') {
                    $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
                } else {
                    $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
                }

                $criteria = [
                    'sousTypeDoc' => $sousTypedocument,
                    'rmq' => $rmq
                ];

                $catg = $this->em->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);

                $categories = [];

                foreach ($catg as $value) {
                    $categories[] = $this->em->getRepository(Catg::class)->find($value['id']);
                }

                $form->add(
                    'categorie',
                    EntityType::class,
                    [
                        'label' => 'Catégorie',
                        'class' => Catg::class,
                        'choice_label' => 'description',
                        'query_builder' => function (CatgRepository $catg) {
                            return $catg->createQueryBuilder('c')->orderBy('c.description', 'ASC');
                        },
                        'choices' => $categories,
                    ]
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $sousTypedocumentId = $data['sousTypeDocument'];
                $sousTypedocument = $this->em->getRepository(SousTypeDocument::class)->find($sousTypedocumentId);

                if (substr($data['agenceEmetteur'], 0, 2) === '50') {
                    $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
                } else {
                    $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
                }

                $criteria = [
                    'sousTypeDoc' => $sousTypedocument,
                    'rmq' => $rmq
                ];

                $catg = $this->em->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);

                $categories = [];

                foreach ($catg as $value) {
                    $categories[] = $this->em->getRepository(Catg::class)->find($value['id']);
                }


                $form->add(
                    'categorie',
                    EntityType::class,
                    [
                        'label' => 'Catégorie',
                        'class' => Catg::class,
                        'choice_label' => 'description',
                        'query_builder' => function (CatgRepository $catg) {
                            return $catg->createQueryBuilder('c')->orderBy('c.description', 'ASC');
                        },
                        'choices' => $categories,
                    ]
                );
            })
            ->add(
                'salarie',
                ChoiceType::class,
                [
                    'mapped' => false,
                    'label' => 'Salarié',
                    'choices' => self::SALARIE,
                    'data' => 'PERMANENT'
                ]
            )

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();

                // Récupération de l'ID du service agence irium
                $agenceServiceIriumId = $this->em->getRepository(AgenceServiceIrium::class)
                    ->findId($data->getCodeAgenceAutoriser(), $data->getCodeSreviceAutoriser());

                // Ajout du champ 'matriculeNom'
                $form->add(
                    'matriculeNom',
                    EntityType::class,
                    [
                        'mapped' => false,
                        'label' => 'Matricule et nom',
                        'class' => Personnel::class,
                        'placeholder' => '-- choisir un personnel --',
                        'choice_label' => function (Personnel $personnel): string {
                            return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
                        },
                        'required' => true,
                        'query_builder' => function (EntityRepository $repository) use ($agenceServiceIriumId) {
                            return $repository->createQueryBuilder('p')
                                ->where('p.agenceServiceIriumId IN (:agenceIps)')
                                ->setParameter('agenceIps', $agenceServiceIriumId)
                                ->orderBy('p.Matricule', 'ASC');
                        },
                    ]
                );
            })

            ->add(
                'matricule',
                TextType::class,
                [
                    'label' => 'Matricule',
                    'attr' => [
                        'readonly' => true
                    ],
                    'required' => true
                ]
            )
            ->add(
                'nom',
                TextType::class,
                [
                    'label' => 'Nom',
                    'required' => true
                ]
            )
            ->add(
                'prenom',
                TextType::class,
                [
                    'label' => 'Prénoms',
                    'required' => true
                ]
            )
            ->add(
                'cin',
                TextType::class,
                [
                    'label' => 'CIN',
                    'required' => true,
                ]
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $form->getData();
            if ($data->getSalarier() === 'PERMANENT') {
                $form
                    ->add(
                        'matriculeNom',
                        EntityType::class,
                        [
                            'mapped' => false,
                            'label' => 'Matricule et nom',
                            'class' => Personnel::class,
                            'placeholder' => '-- choisir une personnel',
                            'choice_label' => function (Personnel $personnel): string {
                                return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
                            },
                            'required' => true
                        ]
                    )
                    ->add(
                        'matricule',
                        TextType::class,
                        [
                            'label' => 'Matricule',
                            'attr' => [
                                'readonly' => true
                            ],
                            'required' => true
                        ]
                    );
            }
        });
    }




    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dom::class,
        ]);
    }
}
