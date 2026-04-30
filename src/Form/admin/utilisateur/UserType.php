<?php

namespace App\Form\admin\utilisateur;

use App\Dto\admin\UserDTO;
use App\Model\LdapModel;
use App\Entity\admin\Personnel;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\utilisateur\Profil;
use App\Repository\admin\ProfilRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $users = (new LdapModel())->infoUser();
        $nom = array_keys($users);
        $canSeeAll = $options['canSeeAll'];

        $builder
            ->add(
                'username',
                ChoiceType::class,
                [
                    'label'       => "Nom d'utilisateur *",
                    'choices'     => array_combine($nom, $nom),
                    'placeholder' => '-- Choisir nom d\'utilisateur --',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'    => 'Email *',
                    'required' => true,

                ]
            )
            ->add(
                'personnel',
                EntityType::class,
                [
                    'label'        => 'Matricule *',
                    'class'        => Personnel::class,
                    'choice_label' => 'Matricule',
                    'placeholder'  => '-- Choisir matricule --',
                    'required'     => true,
                ]
            )
            ->add(
                'profils',
                EntityType::class,
                [
                    'label'        => 'Profil(s) *',
                    'class'        => Profil::class,
                    'choice_label' => function (Profil $profil): string {
                        return $profil->getReference() . ' - ' . $profil->getDesignation();
                    },
                    'multiple'     => true,
                    'expanded'     => false,
                    'required'     => true,
                    'query_builder' => function (ProfilRepository $repo) use ($canSeeAll) {
                        $qb = $repo->createQueryBuilder('p')
                            ->orderBy('p.designation', 'ASC');

                        if (!$canSeeAll) {
                            $qb->where('p.reference != :sup_admin')
                                ->setParameter('sup_admin', 'SUP-ADMIN');
                        }

                        return $qb;
                    },
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserDTO::class,
            'canSeeAll' => false,
        ]);
    }
}
