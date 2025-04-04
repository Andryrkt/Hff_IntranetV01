<?php

namespace App\Form\magasin\lcfng;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Model\magasin\MagasinListeOrATraiterModel;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ListeCdeFrnNonGenererSearchType extends \Symfony\Component\Form\AbstractType
{

    const PIECE_MAGASIN_ACHATS_LOCAUX = [
        'TOUTES LIGNES' => 'TOUTS PIECES',
        'PIÈCES MAGASIN' => 'PIECES MAGASIN',
        'LUB' => 'LUB',
        'ACHATS LOCAUX' => 'ACHATS LOCAUX'
    ];

    const TYPE_DOC = [
        'ORDRE DE REPARATION' => 'OR',
        'CESSION INTER STOCK' => 'CIS',
        'VENTE NEGOCE' => 'VTE NEGOCE',
    ];

    private $magasinModel;

    public function __construct()
    {
        $this->magasinModel = new MagasinListeOrATraiterModel();
    }

    private function recupConstructeur()
    {
        return  $this->magasinModel->recuperationConstructeur();
    }

    private function agence(){
        return array_combine($this->magasinModel->agence(), $this->magasinModel->agence());
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
            $builder
            ->add('numDit', TextType::class, [
                'label' => 'n° DIT',
                'required' => false
            ])
            ->add('dateDebutDoc', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date document (début)',
                'required' => false,
            ])
            ->add('dateFinDoc', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date document (fin)',
                'required' => false,
            ])
            ->add('referencePiece', TextType::class, [
                'label' => 'Référence pièce',
                'required' => false
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'required' => false
            ])
            ->add('constructeur', ChoiceType::class, [
                'label' =>  'Constructeur',
                'required' => false,
                'choices' => $this->recupConstructeur(),
                'placeholder' => ' -- choisir un constructeur --'
            ])
            ->add('typeLigne',
            ChoiceType::class,
            [
                'label' => 'Type de ligne',
                'required' => false,
                'choices' => self::PIECE_MAGASIN_ACHATS_LOCAUX,
                'placeholder' => ' -- choisir une mode affichage --',
                'data' => 'PIECES MAGASIN'
            ])
            ->add('agence',
            ChoiceType::class,
            [
                'label' => 'Agence débiteur',
                'required' => false,
                'choices' => $this->agence() ?? [],
                'placeholder' => ' -- choisir agence --'
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $form->add('service',
                ChoiceType::class,
                [
                    'label' => 'Service débiteur',
                    'required' => false,
                    'choices' => [],
                    'placeholder' => ' -- choisir service --'
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                
                $service = [];
                if($data['agence'] !== ""){
                    $services = $this->magasinModel->service(explode('-',$data['agence'])[0]);
                    
                    foreach ($services as $value) {
                        $service[$value['text']] = $value['text'];
                    }
                } else {
                    $service = [];
                }
        
                $form->add('service',
                ChoiceType::class,
                [
                    'label' => 'Service débiteur',
                    'required' => false,
                    'choices' => $service,
                    'placeholder' => ' -- choisir service --'
                ]);
            })
            ->add('numDoc', NumberType::class, [
                'label' => 'N° Document',
                'required' => false
            ])
            ->add('typeDoc', ChoiceType::class, [
                'label' => 'Type document',
                'required' => false,
                'choices' => self::TYPE_DOC,
                'placeholder' => ' -- choisir un type de document --',
            ])

            ->add('agenceEmetteur',
            ChoiceType::class,
            [
                'label' => 'Agence Emetteur',
                'required' => false,
                'choices' => $this->agence() ?? [],
                'data' => '01-ANTANANARIVO',
                'mapped' => false,
                'disabled' => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $service = [];
                $data['agenceEmetteur'] = $data['agenceEmetteur'] ?? '01-ANTANANARIVO';
                if($data['agenceEmetteur'] !== ""){
                    $services = $this->magasinModel->service(explode('-',$data['agenceEmetteur'])[0]);
                    
                    foreach ($services as $value) {
                        $service[$value['text']] = $value['text'];
                    }
                } else {
                    $service = [];
                }

                $form->add('serviceEmetteur',
                ChoiceType::class,
                [
                    'label' => 'Service Emetteur',
                    'required' => false,
                    'choices' => $service,
                    'placeholder' => ' -- choisir service --'
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $data['agenceEmetteur'] = $data['agenceEmetteur'] ?? '01-ANTANANARIVO';

                $service = [];
                if($data['agenceEmetteur'] !== ""){
                    $services = $this->magasinModel->service(explode('-',$data['agenceEmetteur'])[0]);
                    
                    foreach ($services as $value) {
                        $service[$value['text']] = $value['text'];
                    }
                } else {
                    $service = [];
                }

                $form->add('serviceEmetteur',
                ChoiceType::class,
                [
                    'label' => 'Service Emetteur',
                    'required' => false,
                    'choices' => $service,
                    'placeholder' => ' -- choisir service --'
                ]);
            })
            ->add('numClient', TextType::class, [
                'label' => 'N° Client',
                'required' => false
            ])
            ->add('orValide', 
            CheckboxType::class,
            [
                'label' => 'OR validé',
                'required' => false,
                'data' => true // Définit la case comme cochée par défaut
            ])
            ;
    }
}