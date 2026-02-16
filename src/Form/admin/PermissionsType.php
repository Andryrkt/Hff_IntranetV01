<?php

namespace App\Form\admin;

use App\Dto\admin\PermissionsDTO;
use App\Entity\admin\AgenceService;
use App\Entity\admin\ApplicationProfil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                'class'        => AgenceService::class,
                'choice_label' => fn(AgenceService $as) =>
                $as->getAgence()->getCodeAgence()
                    . ' — '
                    . $as->getService()->getCodeService(),
                'multiple'     => true,
                'expanded'     => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PermissionsDTO::class,
        ]);
    }
}
