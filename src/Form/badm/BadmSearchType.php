<?php

namespace App\Form\badm;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\badm\BadmSearch;
use App\Entity\admin\StatutDemande;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\admin\badm\TypeMouvement;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\admin\StatutDemandeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BadmSearchType extends AbstractType
{
    private $agenceRepository;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->agenceRepository = $this->em->getRepository(Agence::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // --- Préparer les choices pour agenceEmetteur ---
        $agenceChoices = [];
        $serviceChoices = [];
        $serviceAttr = [];

        foreach ($options['agenceServiceAutorises'] as $id => $item) {
            // Agence : pas de doublon sur le label
            $agenceLabel = $item['agence_code'] . ' ' . $item['agence_libelle'];
            if (!isset($agenceChoices[$agenceLabel])) {
                $agenceChoices[$agenceLabel] = $item['agence_id'];
            }

            // Service : on accepte les doublons de label
            // La valeur est l'id de la ligne (unique), pas service_id
            $serviceLabel = $item['service_code'] . ' ' . $item['service_libelle'];
            $serviceChoices[$serviceLabel . '_' . $id] = $id; // clé rendue unique pour PHP

            // data-agence et data-service-id sur chaque option
            $serviceAttr[$id] = [
                'data-agence' => $item['agence_id']
            ];
        }

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
                'placeholder' => '-- Choisir un service --',
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
            ->add('agenceDebiteur', EntityType::class, [
                'label' => "Agence Destinataire",
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => '-- Choisir une agence--',
                'required' => false,
                'attr' => ['class' => 'agenceDebiteur']
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $data->getAgenceDebiteur()) {
                    $services = $data->getAgenceDebiteur()->getServices();
                } else {
                    $services = [];
                }

                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service Destinataire",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir une service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if (isset($data['agenceDebiteur']) && $data['agenceDebiteur']) {
                    $agenceId = $data['agenceDebiteur'];
                    $agence = $this->agenceRepository->find($agenceId);

                    if ($agence) {
                        $services = $agence->getServices();
                    } else {
                        $services = [];
                    }
                } else {
                    $services = [];
                }

                $form->add('serviceDebiteur', EntityType::class, [
                    'label' => "Service Destiantaire",
                    'class' => Service::class,
                    'choice_label' => function (Service $service): string {
                        return $service->getCodeService() . ' ' . $service->getLibelleService();
                    },
                    'placeholder' => '-- Choisir une service--',
                    'choices' => $services,
                    'required' => false,
                    'query_builder' => function (ServiceRepository $serviceRepository) {
                        return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                    },
                    'attr' => ['class' => 'serviceDebiteur']
                ]);
            })
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
