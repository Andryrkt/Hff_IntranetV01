<?php

namespace App\Form\tik;

use App\Controller\Controller;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Repository\admin\dit\WorNiveauUrgenceRepository;
use App\Repository\admin\tik\TkiAutreCategorieRepository;
use App\Repository\admin\tik\TkiCategorieRepository;
use App\Repository\admin\tik\TkiSousCategorieRepository;
use App\Repository\admin\utilisateur\UserRepository;
use App\Service\SessionManagerService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailTikType extends AbstractType
{
    private User $connectedUser;

    public function __construct() {
        $em = Controller::getEntity();
        $sessionService = new SessionManagerService;
        $this->connectedUser = $em->getRepository(User::class)->find($sessionService->get('user_id'));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDebutPlanning', DateType::class, [
                'label'      => 'Début planning',
                'attr'       => ['disabled' => !in_array("INTERVENANT", $this->connectedUser->getRoleNames())],
                'widget'     => 'single_text',
                'required'   => false,
            ])
            ->add('dateFinPlanning', DateType::class, [
                'label'      => 'Fin planning',
                'attr'       => ['disabled' => !in_array("INTERVENANT", $this->connectedUser->getRoleNames())],
                'widget'     => 'single_text',
                'required'   => false,
            ])
            ->add('categorie', EntityType::class, [
                'label'        => 'Catégorie',
                'class'        => TkiCategorie::class,
                'choice_label' => 'description',
                'query_builder'=> function(TkiCategorieRepository $TkiCategorieRepository) {
                    return $TkiCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
                'data'         => $options['data']->getCategorie(),
                'attr'         => [
                    'class'    => 'categorie',
                    'disabled' => in_array("INTERVENANT", $this->connectedUser->getRoleNames()),
                ],
                'placeholder'  => '-- Choisir une catégorie --',
                'multiple'     => false,
                'expanded'     => false,
                'required'     => true,
            ])
            ->add('sousCategorie', EntityType::class, [
                'label'        => 'Sous-catégories',
                'attr'         => [
                    'class'    => 'sous-categorie',
                    'disabled' => in_array("INTERVENANT", $this->connectedUser->getRoleNames())
                ],
                'placeholder'  => '-- Choisir une sous-catégorie --',
                'class'        => TkiSousCategorie::class,
                'query_builder'=> function(TkiSousCategorieRepository $TkiSousCategorieRepository) {
                    return $TkiSousCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
                'choice_label' => 'description',
                'required'     => false,
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('autresCategorie', EntityType::class, [
                'label'        => 'Autres catégories',
                'attr'         => [
                    'class'    => 'autre-categorie',
                    'disabled' => in_array("INTERVENANT", $this->connectedUser->getRoleNames())
                ],
                'placeholder'  => '-- Choix d\'autre catégorie --',
                'class'        => TkiAutresCategorie::class,
                'query_builder'=> function(TkiAutreCategorieRepository $TkiAutreCategorieRepository) {
                    return $TkiAutreCategorieRepository
                        ->createQueryBuilder('t')
                        ->orderBy('t.description', 'ASC');
                },
                'choice_label' => 'description',
                'required'     => false,
                'multiple'     => false,
                'expanded'     => false
            ])
            ->add('intervenant', EntityType::class, [
                'label'        => 'Intervenant',
                'placeholder'  => '-- Choisir un intervenant --',
                'class'        => User::class,
                'choice_label' => 'nom_utilisateur',
                'query_builder'=> function(UserRepository $userRepository) {
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
            ->add('niveauUrgence', EntityType::class, [
                'label'        => 'Niveau d\'urgence',
                'choice_label' => 'description',
                'attr'         => ['disabled' => in_array("INTERVENANT", $this->connectedUser->getRoleNames())],
                'placeholder'  => '-- Choisir le niveau d\'urgence --',
                'class'        => WorNiveauUrgence::class,
                'query_builder'=> function(WorNiveauUrgenceRepository $WorNiveauUrgenceRepository) {
                    return $WorNiveauUrgenceRepository
                        ->createQueryBuilder('w')
                        ->orderBy('w.description', 'DESC');
                },
                'multiple'     => false,
                'expanded'     => false,
            ])
            ->add('commentaires', TextareaType::class, [
                'label'    => 'Observation concernant le ticket',
                'required' => true,
                'attr'     => [
                    'rows'     => 5
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
