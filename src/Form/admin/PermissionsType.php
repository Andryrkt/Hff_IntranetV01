<?php

namespace App\Form\admin;

use App\Dto\admin\PermissionsDTO;
use App\Entity\admin\AgenceService;
use App\Entity\admin\ApplicationProfil;
use App\Repository\admin\AgenceServiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $societe = $options['societe'];
        $builder
            ->add('applicationProfil', EntityType::class, [
                'label'        => false,
                'class'        => ApplicationProfil::class,
                'disabled'     => true,
                'choice_label' => fn(ApplicationProfil $ap) =>
                $ap->getProfil()->getReference()
                    . ' — '
                    . $ap->getApplication()->getCodeApp(),
            ])
            ->add('agenceServices', EntityType::class, [
                'label'        => 'Agence(s) - Service(s) autorisée(s)',
                'required'     => false,
                'class'        => AgenceService::class,
                'choice_label' => fn(AgenceService $as) =>
                $as->getAgence()->getCodeAgence()
                    . ' — '
                    . $as->getService()->getCodeService(),
                'query_builder' => function (AgenceServiceRepository $er) use ($societe) {
                    $qb = $er->createQueryBuilder('t');
                    $qb->join('t.agence', 'a')
                        ->where('a.societe = :societe')
                        ->setParameter('societe', $societe);
                    return $qb;
                },
                'multiple'     => true,
                'expanded'     => false,
            ])
            ->add('lignes', CollectionType::class, [
                'entry_type'   => ApplicationProfilPagetype::class,
                'label'        => false,
                'allow_add'    => false,
                'allow_delete' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PermissionsDTO::class,
            'societe'    => null,
        ]);
    }
}
