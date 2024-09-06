<?php

namespace App\Form\magasin;


use App\Model\magasin\MagasinModel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MagasinListeOrALivrerSearchType extends AbstractType
{

    

    const OR_COMPLET_OU_NON = [
        'TOUTS LES OR' => 'TOUTS LES OR',
        'COMPLET' => 'ORs COMPLET',
        'INCOMPLET' => 'ORs INCOMPLETS'
    ];

    const PIECE_MAGASIN_ACHATS_LOCAUX = [
        'TOUTS PIECES' => 'TOUTS PIECES',
        'PIECES MAGASIN' => 'PIECES MAGASIN',
        'LUB ET ACHATS LOCAUX' => 'LUB ET ACHATS LOCAUX'
    ];

    private $magasinModel;

    public function __construct()
    {
        $this->magasinModel = new MagasinModel();
    }

    private function recupConstructeur()
    {
       return  $this->magasinModel->recuperationConstructeur();
    }

    private function agence(){
        return array_combine($this->magasinModel->agence(), $this->magasinModel->agence());
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
        ->add('niveauUrgence', EntityType::class, [
            'label' => 'Niveau d\'urgence',
            'class' => WorNiveauUrgence::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une niveau --',
            'required' => false,
        ])
        ->add('numDit', TextType::class, [
            'label' => 'n° DIT',
            'required' => false
        ])
        ->add('numOr', NumberType::class, [
            'label' => 'n° Or',
            'required' => false
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
            'placeholder' => ' -- choisir une constructeur --'
        ])
        ->add('dateDebut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création OR (début)',
            'required' => false,
        ])
        ->add('dateFin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de création OR (fin)',
            'required' => false,
        ])
        ->add('orCompletNon',
        ChoiceType::class,
        [
            'label' => 'Etat ORs',
            'required' => false,
            'choices' => self::OR_COMPLET_OU_NON,
            'placeholder' => ' -- choisir une mode affichage --',
            'data' => 'ORs COMPLET'
        ])
        ->add('pieces',
        ChoiceType::class,
        [
            'label' => 'Pièces',
            'required' => false,
            'choices' => self::PIECE_MAGASIN_ACHATS_LOCAUX,
            'placeholder' => ' -- choisir une mode affichage --',
            'data' => 'PIECES MAGASIN'
        ])
        ->add('agence',
        ChoiceType::class,
        [
            'label' => 'Agence',
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
                'label' => 'Service',
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
            $services = $this->magasinModel->service($data['agence']);
            
            foreach ($services as $value) {
                $service[$value['text']] = $value['text'];
            }
        } else {
            $service = [];
        }
   
        
        $form->add('service',
        ChoiceType::class,
        [
            'label' => 'Service',
            'required' => false,
            'choices' => $service,
            'placeholder' => ' -- choisir service --'
        ]);
           
        })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
