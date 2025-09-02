<?php

namespace App\Form\common;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Repository\admin\AgenceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgenceServiceType extends AbstractType
{
    private $agenceRepository;

    public function __construct(AgenceRepository $agenceRepository)
    {
        $this->agenceRepository = $agenceRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agence', EntityType::class, [
                'label' => $options['agence_label'],
                'class' => Agence::class,
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => $options['agence_placeholder'],
                'required' => $options['agence_required'],
                'attr' => ['class' => 'agence-selector']
            ]);

        // Pré-set data
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $agence = $data ? $this->getAgenceFromData($data) : null;
            
            $this->addServiceField($event->getForm(), $agence, $options);
        });

        // Pré-submit
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $agence = $this->getAgenceFromFormData($data);
            
            $this->addServiceField($event->getForm(), $agence, $options);
        });
    }

    private function addServiceField(FormInterface $form, ?Agence $agence, array $options): void
    {
        $services = $agence ? $agence->getServices() : [];

        $form->add('service', EntityType::class, [
            'label' => $options['service_label'],
            'class' => Service::class,
            'choice_label' => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            },
            'placeholder' => $options['service_placeholder'],
            'choices' => $services,
            'required' => $options['service_required'],
            'attr' => ['class' => 'service-selector']
        ]);
    }

    private function getAgenceFromData($data): ?Agence
    {
        if (is_object($data) && method_exists($data, 'getAgence')) {
            return $data->getAgence();
        }
        if (is_array($data) && isset($data['agence'])) {
            return $data['agence'];
        }

        return null;
    }

    private function getAgenceFromFormData(array $data): ?Agence
    {
        if (isset($data['agence']) && $data['agence']) {
            return $this->agenceRepository->find($data['agence']);
        }

        return null;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'agence_label' => "Agence",
            'agence_placeholder' => '-- Choisir une agence--',
            'agence_required' => false,
            'service_label' => "Service",
            'service_placeholder' => '-- Choisir un service--',
            'service_required' => false,
        ]);
    }
}
