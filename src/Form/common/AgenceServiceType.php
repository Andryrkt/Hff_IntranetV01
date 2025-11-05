<?php

namespace App\Form\common;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Utils\EntityManagerHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgenceServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agence', EntityType::class, [
                'label' => $options['agence_label'],
                'class' => Agence::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('a');
                    if (!empty($options['agence_codes'])) {
                        $qb->where($qb->expr()->in('a.codeAgence', $options['agence_codes']));
                    }
                    return $qb;
                },
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => $options['agence_placeholder'],
                'required' => $options['agence_required']
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
            $agence = $this->getAgenceFromFormData($data, $event->getForm());
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
            'required' => $options['service_required']
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

    private function getAgenceFromFormData(array $data, FormInterface $form): ?Agence
    {
        if (isset($data['agence']) && $data['agence']) {
            // Récupérer l'EntityManager via les options du formulaire
            $em = $form->getConfig()->getOption('em');
            if ($em) {
                return $em->getRepository(Agence::class)->find($data['agence']);
            }

            // Fallback: utiliser EntityManagerHelper
            $repository = EntityManagerHelper::getRepository(Agence::class);
            return $repository ? $repository->find($data['agence']) : null;
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
            'em' => null,
            'agence_codes' => []
        ]);
    }
}