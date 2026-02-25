<?php

namespace App\Form\badm;

use App\Entity\admin\Agence;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\badm\BadmSearch;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\badm\TypeMouvement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use App\Traits\PrepareAgenceServiceTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BadmSearchType extends AbstractType
{
    use PrepareAgenceServiceTrait;
    private $agenceRepository;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $this->prepareAgenceServiceChoices($options['agenceServiceAutorises']);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        $builder
            ->add('statut', EntityType::class, [
                'label' => 'Statut',
                'class' => StatutDemande::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un statut --',
                'required' => false,
                'query_builder' => function (StatutDemandeRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.codeApp = :codeApp')
                        ->setParameter('codeApp', 'BDM');
                },
            ])
            ->add('idMateriel', TextType::class, [
                'label' => 'Id Materiel',
                'required' => false,
            ])
            ->add('numParc', TextType::class, [
                'label' => "N° Parc",
                'required' => false
            ])
            ->add('numSerie', TextType::class, [
                'label' => "N° Serie",
                'required' => false
            ])
            ->add('typeMouvement', EntityType::class, [
                'label' => 'Type Mouvement',
                'class' => TypeMouvement::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir une type de mouvement--',
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Début',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Demande Fin',
                'required' => false,
            ])
            // --- agenceEmetteur : ChoiceType ---
            ->add('agenceEmetteur', ChoiceType::class, [
                'label'       => 'Agence émetteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceEmetteur : ChoiceType ---
            ->add('serviceEmetteur', ChoiceType::class, [
                'label'       => 'Service émetteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
            // --- agenceDebiteur : ChoiceType ---
            ->add('agenceDebiteur', ChoiceType::class, [
                'label'       => 'Agence Destinataire',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceDebiteur : ChoiceType ---
            ->add('serviceDebiteur', ChoiceType::class, [
                'label'       => 'Service Destinataire',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'             => BadmSearch::class,
            'agenceServiceAutorises' => [],
        ]);
    }
}
