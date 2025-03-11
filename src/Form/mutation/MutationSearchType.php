<?php

namespace App\Form\mutation;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\dom\DomSearch;
use App\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\mutation\MutationSearch;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MutationSearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('statut', EntityType::class, [
                'label'         => 'Statut',
                'class'         => StatutDemande::class,
                'choice_label'  => 'description',
                'placeholder'   => '-- Choisir un statut --',
                'required'      => false,
                'query_builder' => function (StatutDemandeRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.codeApp = :codeApp')
                        ->setParameter('codeApp', 'MUT');
                },
            ])
            ->add('numMut', TextType::class, [
                'label'    => "N° MUT",
                'required' => false
            ])
            ->add('matricule', TextType::class, [
                'label'    => 'Matricule',
                'required' => false,
            ])
            ->add('dateDemandeDebut', DateType::class, [
                'widget'   => 'single_text',
                'label'    => 'Date de création Début',
                'required' => false,
            ])
            ->add('dateDemandeFin', DateType::class, [
                'widget'   => 'single_text',
                'label'    => 'Date de création Fin',
                'required' => false,
            ])
            ->add('dateMutationDebut', DateType::class, [
                'widget'   => 'single_text',
                'label'    => 'Date Mission Début',
                'required' => false,
            ])
            ->add('dateMutationFin', DateType::class, [
                'widget'   => 'single_text',
                'label'    => 'Date Mission Fin',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MutationSearch::class,
        ]);
    }
}
