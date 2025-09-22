<?php

namespace App\Form\magasin\lcfnp;


use App\Model\magasin\MagasinListeOrATraiterModel;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ListeCdeFrnNonPlaceSearchType extends \Symfony\Component\Form\AbstractType
{
    private $magasinModel;
    const CMD = [
        'TOUS' => 'TOUS',
        'ATELIER' => 'ATE',
        'MAGASIN' => 'NEG',
        'REAPPRO' => 'REAPPRO'
    ];
    public function __construct(MagasinListeOrATraiterModel $magasinModel)
    {
        $this->magasinModel = $magasinModel;
    }
    private function agence()
    {
        return array_combine($this->magasinModel->agence(), $this->magasinModel->agence());
    }
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commande', ChoiceType::class, [
                'label' => 'Commande',
                'required' => true,
                'data' => 'TOUS',
                'choices' => self::CMD
            ])
            ->add('numOR', TextType::class, [
                'label' => 'N° OR',
                'required' => false
            ])
            ->add('dateDebutDoc', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début',
                'required' => false,
            ])
            ->add('dateFinDoc', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin',
                'required' => false,
            ])
            ->add('CodeNomFrs', TextType::class, [
                'label' => 'code/nom fournisseur ',
                'required' => false
            ])
            // ->add('numCdeNego', TextType::class, [
            //     'label' => 'N° Commande Négoce',
            //     'required' => false
            // ])
            ->add('numCdFrs', NumberType::class, [
                'label' => 'N° Commande Fournisseur',
                'required' => false
            ])
            ->add('numClient', TextType::class, [
                'label' => 'N° Client',
                'required' => false
            ])
            ->add(
                'orValide',
                CheckboxType::class,
                [
                    'label' => 'OR validé',
                    'required' => false,
                    'data' => true // Définit la case comme cochée par défaut
                ]
            )



            // ->add('agence',
            // ChoiceType::class,
            // [
            //     'label' => 'Agence débiteur',
            //     'required' => false,
            //     'choices' => $this->agence() ?? [],
            //     'placeholder' => ' -- choisir agence --'
            // ])
            // ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            //     $form = $event->getForm();
            //     $data = $event->getData();
            //     $form->add('service',
            //     ChoiceType::class,
            //     [
            //         'label' => 'Service débiteur',
            //         'required' => false,
            //         'choices' => [],
            //         'placeholder' => ' -- choisir service --'
            //     ]);
            // })
            // ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            //     $form = $event->getForm();
            //     $data = $event->getData();

            //     $service = [];
            //     if($data['agence'] !== ""){
            //         $services = $this->magasinModel->service(explode('-',$data['agence'])[0]);

            //         foreach ($services as $value) {
            //             $service[$value['text']] = $value['text'];
            //         }
            //     } else {
            //         $service = [];
            //     }

            //     $form->add('service',
            //     ChoiceType::class,
            //     [
            //         'label' => 'Service débiteur',
            //         'required' => false,
            //         'choices' => $service,
            //         'placeholder' => ' -- choisir service --'
            //     ]);
            // })


            // ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            //     $form = $event->getForm();
            //     $data = $event->getData();
            //     $data['agenceEmetteur'] = $data['agenceEmetteur'] ?? '01-ANTANANARIVO';

            //     $service = [];
            //     if($data['agenceEmetteur'] !== ""){
            //         $services = $this->magasinModel->service(explode('-',$data['agenceEmetteur'])[0]);

            //         foreach ($services as $value) {
            //             $service[$value['text']] = $value['text'];
            //         }
            //     } else {
            //         $service = [];
            //     }

            //     $form->add('serviceEmetteur',
            //     ChoiceType::class,
            //     [
            //         'label' => 'Service Emetteur',
            //         'required' => false,
            //         'choices' => $service,
            //         'placeholder' => ' -- choisir service --'
            //     ]);
            // })

        ;
    }
}
