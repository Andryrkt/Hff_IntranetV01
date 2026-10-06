<?php

namespace App\Form\admin;

use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\admin\ServiceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;




class AgenceServiceType extends AbstractType
{

    private $agenceRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->agenceRepository = $em->getRepository(Agence::class);
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add(
                'agences',
                EntityType::class,
                [
                    'label' => 'Agence :',
                    'class' => Agence::class,
                    'choice_label' => function (Agence $service): string {
                        return $service->getCodeAgence() . ' ' . $service->getLibelleAgence();
                    },
                    'attr' => ['class' => 'agenceDebiteur']

                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();
                $services = null;

                if ($data instanceof Dom && $data->getAgence()) {
                    $services = $data->getAgence()->getServices();
                }

                $form->add(
                    'services',
                    EntityType::class,
                    [

                        'label' => 'Service :',
                        'class' => Service::class,
                        'choice_label' => function (Service $service): string {
                            return $service->getCodeService() . ' ' . $service->getLibelleService();
                        },
                        'choices' => $services,
                        // 'disabled' => $agence === null,
                        'required' => false,
                        'query_builder' => function (ServiceRepository $serviceRepository) {
                            return $serviceRepository->createQueryBuilder('s')->orderBy('s.codeService', 'ASC');
                        },
                        //'data' => $options['data']->getService(),
                        'attr' => ['class' => 'serviceDebiteur'],
                        'multiple' => true,
                        'expanded' => false
                    ]
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $agenceId = $data['agence'] ?? null;

                if ($agenceId) {

                    $agence = $this->agenceRepository->find($agenceId);
                    $services = $agence ? $agence->getServices() : [];

                    $form->add('service', EntityType::class, [
                        'label' => 'Service Debiteur',
                        'class' => Service::class,
                        'choice_label' => function (Service $service): string {
                            return $service->getCodeService() . ' ' . $service->getLibelleService();
                        },
                        'choices' => $services,
                        'required' => false,
                        'attr' => ['class' => 'serviceDebiteur'],
                        'multiple' => true,
                        'expanded' => false
                    ]);
                    //Ajouter des validations ou des traitements supplémentaires ici si nécessaire
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // $resolver->setDefaults([
        //     'data_class' => Agence::class,
        // ]);
    }
}
