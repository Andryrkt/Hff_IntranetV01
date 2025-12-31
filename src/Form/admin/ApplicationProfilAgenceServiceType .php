<?php

namespace App\Form\admin;

use App\Dto\admin\ApplicationProfilAgenceServiceDTO;
use App\Entity\admin\AgenceService;
use App\Entity\admin\ApplicationProfil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationProfilAgenceServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('applicationProfil', EntityType::class, [
                'class' => ApplicationProfil::class,
                'choice_label' => fn(ApplicationProfil $ap) =>
                $ap->getApplication()->getCodeApp()
                    . ' — '
                    . $ap->getProfil()->getReference(),
                'placeholder' => 'Choisir une application / profil',
            ])
            ->add('agenceServiceIds', ChoiceType::class, [
                'choices' => $options['agence_services'],
                'choice_label' => fn(AgenceService $as) =>
                $as->getAgence()->getCodeAgence()
                    . ' / '
                    . $as->getService()->getCodeService(),
                'choice_value' => fn(?AgenceService $as) => $as ? $as->getId() : null,
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApplicationProfilAgenceServiceDTO::class,
            'agence_services' => [],
        ]);
    }
}
