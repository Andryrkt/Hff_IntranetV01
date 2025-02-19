<?php

namespace App\Form\mutation;

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
use App\Entity\mutation\Mutation;
use App\Repository\admin\dom\CatgRepository;
use App\Repository\admin\dom\SousTypeDocumentRepository;
use App\Repository\admin\PersonnelRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MutationFormType extends AbstractType
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
                    'mapped'   => false,
                    'label'    => 'Agence',
                    'required' => true,
                    'attr'     => [
                        'class'    => 'disabled',
                    ],
                    'data'     => $options["data"]->getAgenceEmetteur() ?? null
                ]
            )
            ->add(
                'serviceEmetteur',
                TextType::class,
                [
                    'mapped'   => false,
                    'label'    => 'Service',
                    'required' => true,
                    'attr'     => [
                        'class'    => 'disabled',
                    ],
                    'data'     => $options["data"]->getServiceEmetteur() ?? null
                ]
            )
            ->add(
                'categorie',
                EntityType::class,
                [
                    'label'         => 'Catégorie',
                    'class'         => Catg::class,
                    'choice_label'  => 'description',
                    'query_builder' => function (CatgRepository $catg) {
                        return $catg->createQueryBuilder('c')->orderBy('c.description', 'ASC');
                    }
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();

                $codeAgence = explode(" ", $options['data']->getAgenceEmetteur())[0];   // obtenir le code agence de l'utilisateur
                $codeService = explode(" ", $options['data']->getServiceEmetteur())[0];  // obtenir le code service de l'utilisateur

                // Récupération de l'ID du service agence irium
                $agenceServiceIriumId = $this->em->getRepository(AgenceServiceIrium::class)
                    ->findId($codeAgence, $codeService, $options['data']->getServiceEmetteur());

                // Ajout du champ 'matriculeNom'
                $form->add(
                    'matriculeNomPrenom',
                    EntityType::class,
                    [
                        'mapped'        => false,
                        'label'         => 'Matricule, nom et prénoms',
                        'class'         => Personnel::class,
                        'placeholder'   => '-- choisir un personnel --',
                        'choice_label'  => function (Personnel $personnel): string {
                            return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
                        },
                        'required'      => true,
                        'query_builder' => function (PersonnelRepository $repository) use ($agenceServiceIriumId) {
                            return $repository->createQueryBuilder('p')
                                ->where('p.agenceServiceIriumId IN (:agenceIps)')
                                ->setParameter('agenceIps', $agenceServiceIriumId)
                                ->orderBy('p.Matricule', 'ASC');
                        },
                    ]
                );
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $mutation = $event->getData(); // Objet
                $form = $event->getForm();

                $personnelId = $form->get('matriculeNomPrenom')->getData(); // id du personnel sélectionné

                /** 
                 * @var Personnel $personnel
                 */
                $personnel = $this->em->getRepository(Personnel::class)->find($personnelId);

                // On met à jour les données du formulaire
                $mutation->setMatricule($personnel->getMatricule());
                $mutation->setNom($personnel->getNom());
                $mutation->setPrenom($personnel->getPrenoms());
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mutation::class,
        ]);
    }
}
