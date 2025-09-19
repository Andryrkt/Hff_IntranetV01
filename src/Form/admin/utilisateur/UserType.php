<?php

namespace App\Form\admin\utilisateur;

use App\Model\LdapModel;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\Societte;
use App\Entity\admin\Personnel;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use App\Service\SessionManagerService;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\utilisateur\Fonction;
use App\Entity\admin\utilisateur\Permission;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\admin\utilisateur\RoleRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    private $ldap;
    private $em;
    private $sessionService;

    public function __construct(EntityManagerInterface $em, LdapModel $ldap, SessionManagerService $sessionService)
    {
        $this->ldap = $ldap;
        $this->em = $em;
        $this->sessionService = $sessionService;
    }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userId = $this->sessionService->get('user_id');
        $password = $this->sessionService->get('password');

        $nom = [];

        // Vérifier si les données de session sont disponibles
        if ($userId && $password && $userId !== '-') {
            try {
                $userEntity = $this->em->getRepository(User::class)->find($userId);
                if ($userEntity) {
                    $user = $userEntity->getNomUtilisateur();
                    $users = $this->ldap->infoUser($user, $password);

                    foreach ($users as $key => $value) {
                        $nom[] = $key;
                    }
                }
            } catch (\Exception $e) {
                // En cas d'erreur, on continue avec un tableau vide
                error_log("Erreur lors de la récupération des utilisateurs LDAP: " . $e->getMessage());
            }
        }

        // Si pas de données de session ou utilisateur non trouvé, essayer de récupérer tous les utilisateurs LDAP
        if (empty($nom)) {
            try {
                // Essayer de récupérer tous les utilisateurs LDAP sans authentification spécifique
                $users = $this->ldap->infoUser('', '');
                foreach ($users as $key => $value) {
                    $nom[] = $key;
                }
            } catch (\Exception $e) {
                error_log("Erreur lors de la récupération générale des utilisateurs LDAP: " . $e->getMessage());
            }
        }

        // Si aucun utilisateur n'a été trouvé, on peut ajouter un message d'information
        if (empty($nom)) {
            $nom = ['Aucun utilisateur LDAP disponible'];
        }


        $builder
            ->add(
                'nom_utilisateur',
                ChoiceType::class,
                [
                    'label' => "Nom d'utilisateur *",
                    'choices' => array_combine($nom, $nom),
                    'placeholder' => '-- Choisir un nom d\'utilisateur --',
                    'disabled' => empty($nom) || (count($nom) === 1 && $nom[0] === 'Aucun utilisateur LDAP disponible'),
                    'help' => empty($nom) || (count($nom) === 1 && $nom[0] === 'Aucun utilisateur LDAP disponible')
                        ? 'Aucun utilisateur LDAP disponible. Causes possibles : 1) Vous n\'êtes pas connecté, 2) Session expirée, 3) Problème de connexion LDAP. Veuillez vous connecter d\'abord via /login.'
                        : null
                ]
            )
            ->add(
                'matricule',
                NumberType::class,
                [
                    'label' => 'Numero Matricule *',
                    'required' => true,

                ]
            )
            ->add(
                'mail',
                EmailType::class,
                [
                    'label' => 'Email *',
                    'required' => true,

                ]
            )
            ->add(
                'roles',
                EntityType::class,
                [
                    'label' => 'Role *',
                    'placeholder' => '-- Choisir une role --',
                    'class' => Role::class,
                    'choice_label' => 'role_name',
                    'query_builder' => function (RoleRepository $roleRepository) {
                        return $roleRepository->createQueryBuilder('r')->orderBy('r.role_name', 'ASC');
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,

                ]
            )
            ->add(
                'applications',
                EntityType::class,
                [
                    'label' => 'Applications *',
                    'class' => Application::class,
                    'choice_label' => 'codeApp',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true,
                ]
            )
            ->add(
                'societtes',
                EntityType::class,
                [
                    'label' => 'Sociétes *',
                    'class' => Societte::class,
                    'choice_label' => function (Societte $societte): string {
                        return $societte->getCodeSociete() . ' ' . $societte->getNom();
                    },
                    'placeholder' => '-- Choisir une sociétés--',
                    'required' => true,
                ]
            )
            ->add(
                'personnels',
                EntityType::class,
                [
                    'label' => 'Matricule Personnel *',
                    'class' => Personnel::class,
                    'choice_label' => 'Matricule',
                    'placeholder' => '-- Choisir une matricuel --',
                    'required' => true,
                ]
            )
            ->add(
                'superieur',
                TextType::class,
                [
                    'label' => 'Chef de service',
                    'required' => false,
                    'attr' => [
                        'disabled' => true
                    ],
                    'data' => $options['data']->getSuperieur()
                ]
            )
            ->add(
                'fonction',
                EntityType::class,
                [
                    'label' => 'Fonction de l\'utilisateur',
                    'class' => Fonction::class,
                    'choice_label' => 'description',
                    'required' => false
                ]
            )
            ->add(
                'agenceServiceIrium',
                EntityType::class,
                [
                    'label' => 'Code Sage *',
                    'class' => AgenceServiceIrium::class,
                    'choice_label' => 'service_sage_paie',
                    'placeholder' => "-- choisir une code sage --",
                    'required' => true,
                ]
            )
            ->add(
                'agencesAutorisees',
                EntityType::class,
                [
                    'label' => 'Agence autoriser *',
                    'class' => Agence::class,
                    'choice_label' => function (Agence $agence): string {
                        return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                    },
                    'multiple' => true,
                    'expanded' => false,
                    'required' => true,
                ]
            )
            ->add(
                'serviceAutoriser',
                EntityType::class,
                [
                    'label' => 'Service autoriser *',
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'multiple' => true,
                    'expanded' => false,
                    'required' => true,

                ]
            )
            ->add(
                'permissions',
                EntityType::class,
                [
                    'label' => "Permission utilisateur",
                    'class' => Permission::class,
                    'choice_label' => 'permissionName',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false
                ]
            )
            ->add(
                'numTel',
                TextType::class,
                [
                    'label' => 'N° Telephone',
                    'required' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
