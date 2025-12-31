<?php

namespace App\Form\admin;

use App\Dto\admin\ApplicationProfilAgenceServiceDTO;
use App\Entity\admin\AgenceService;
use App\Entity\admin\ApplicationProfil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationProfilAgenceServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('applicationProfil', EntityType::class, [
                'label' => 'Profil - Application',
                'class' => ApplicationProfil::class,
                'choice_label' => fn(ApplicationProfil $ap) =>
                $ap->getProfil()->getReference()
                    . ' — '
                    . $ap->getApplication()->getCodeApp(),
                'placeholder' => '-- Choisir une application / profil --',
            ])
            ->add('agenceServices', EntityType::class, [
                'label' => 'Agence(s) - Service(s) autorisée(s)',
                'class' => AgenceService::class,
                'choice_label' => fn(AgenceService $as) =>
                $as->getAgence()->getCodeAgence()
                    . ' / '
                    . $as->getService()->getCodeService(),
                'multiple' => true,
                'expanded' => false,
                'placeholder' => '-- Choisir des agences/services --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApplicationProfilAgenceServiceDTO::class,
        ]);
    }
}
