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
                'label'               => $options['agence_label'],
                'class'               => Agence::class,
                'query_builder'       => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('a');
                    if (!empty($options['agence_codes'])) {
                        $qb->where($qb->expr()->in('a.codeAgence', $options['agence_codes']));
                    }
                    return $qb;
                },
                'choice_label'        => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'placeholder' => $options['agence_placeholder'],
                'required' => $options['agence_required'],
                'data' => $options['data_agence'] ?? null,
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
            $agence = $data ? $this->getAgenceFromFormData($data, $event->getForm()) : null;
            $this->addServiceField($event->getForm(), $agence, $options);
        });
    }

    private function addServiceField(FormInterface $form, ?Agence $agence, array $options): void
    {
        $services = $agence ? $agence->getServices() : (isset($options['data_agence']) && $options['data_agence'] ? $options['data_agence']->getServices() : null);

        $em = $form->getConfig()->getOption('em') ?? EntityManagerHelper::getEntityManager();

        // Optimisation MASSIVE : Si on charge tout, on bypass complètement Doctrine EntityType
        // On construit un ChoiceType plat et on transforme le retour en objet métier via DataTransformer
        if ($services === null && $em) {
            try {
                $sql = "
                    SELECT s.id, s.code_service, s.libelle_service, asrv.agence_id 
                    FROM services s
                    LEFT JOIN agence_service asrv ON s.id = asrv.service_id
                ";
                $results = $em->getConnection()->fetchAllAssociative($sql);

                $choices = [];
                $agenceMap = [];
                foreach ($results as $row) {
                    $label = $row['code_service'] . ' ' . $row['libelle_service'];
                    $choices[$label] = clone (object)[]; // placeholder
                    // on contourne array merge pour perf
                    $id = $row['id'];
                    $choices[$label] = $id;

                    if (!isset($agenceMap[$id]) && $row['agence_id']) {
                        $agenceMap[$id] = (string) $row['agence_id'];
                    }
                }

                $serviceBuilder = $form->getConfig()->getFormFactory()->createNamedBuilder('service', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, null, [
                    'label'               => $options['service_label'],
                    'choices'             => $choices,
                    'choice_attr'         => function ($choice, $key, $value) use ($agenceMap) {
                        return ['data-agence' => $agenceMap[$value] ?? ''];
                    },
                    'placeholder'         => $options['service_placeholder'],
                    'required'            => $options['service_required'],
                    'data'                => $options['data_service'] ?? null,
                    'auto_initialize'     => false,
                ]);

                $serviceBuilder->addModelTransformer(new \Symfony\Component\Form\CallbackTransformer(
                    // Model data (Service object) to Norm data (id integer/string matching choices array)
                    function ($serviceAsObject) {
                        return $serviceAsObject ? strval($serviceAsObject->getId()) : null;
                    }, 
                    // Norm data (id integer/string) to Model data (Service object)
                    function ($serviceIdAsString) use ($em) {
                        if (!$serviceIdAsString) return null;
                        return $em->getRepository(Service::class)->find($serviceIdAsString);
                    }
                ));

                $form->add($serviceBuilder->getForm());

                return; // Terminé, on sort de la fonction
            } catch (\Exception $e) {
                // Fallback normal si erreur SQL
            }
        }

        // --- Logique Fallback (quand l'agence est choisie ou erreur SQL) ---
        if ($services === null && $em) {
            $services = $em->getRepository(Service::class)->findAll();
        } elseif ($services === null) {
            $services = [];
        }

        // Cache des ID agences
        $agenceMap = [];
        if ($services !== null && $em) {
            try {
                $sql = "SELECT service_id, agence_id FROM agence_service";
                $results = $em->getConnection()->fetchAllAssociative($sql);
                foreach ($results as $row) {
                    if (!isset($agenceMap[$row['service_id']])) {
                        $agenceMap[$row['service_id']] = (string) $row['agence_id'];
                    }
                }
            } catch (\Exception $e) {
                if (is_iterable($services)) {
                    foreach ($services as $srv) {
                        $asrv = $srv->getAgenceServices()->first();
                        $agenceMap[$srv->getId()] = $asrv && $asrv->getAgence() ? $asrv->getAgence()->getId() : '';
                    }
                }
            }
        }

        $form->add('service', EntityType::class, [
            'label'               => $options['service_label'],
            'class'               => Service::class,
            'choice_label'        => function (Service $service): string {
                return $service->getCodeService() . ' ' . $service->getLibelleService();
            },
            'choice_attr' => function (Service $service) use ($agenceMap) {
                return ['data-agence' => $agenceMap[$service->getId()] ?? ''];
            },
            'placeholder' => $options['service_placeholder'],
            'choices' => $services,
            'required' => $options['service_required'],
            'data' => $options['data_service'] ?? null,
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
            'em'                  => null,
            'agence_label'        => "Agence",
            'agence_placeholder'  => '-- Choisir une agence--',
            'agence_required'     => false,
            'service_label'       => "Service",
            'service_placeholder' => '-- Choisir un service--',
            'service_required' => false,
            'agence_codes' => [],
            'data_agence' => null,
            'data_service' => null,
        ]);
    }
}
