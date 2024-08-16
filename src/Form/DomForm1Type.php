<?php

namespace App\Form;

use App\Entity\Dom;
use App\Entity\Rmq;


use App\Entity\Catg;

use App\Entity\Indemnite;
use App\Entity\Personnel;
use App\Controller\Controller;
use App\Entity\SousTypeDocument;
use App\Repository\CatgRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class DomForm1Type extends AbstractType
{
    private $em;
    const SALARIE = [
        'PERMANENT' => 'PERMANENT',
        'TEMPORAIRE' => 'TEMPORAIRE',
    ];
    
    public function __construct()
    {
        $this->em = Controller::getEntity();
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder
        ->add('agenceEmetteur', 
        TextType::class,
        [
           'mapped' => false,
                    'label' => 'Agence',
                    'required' => false,
                    'attr' => [
                        'readonly' => true
                    ],
            'data' => $options["data"]->getAgenceEmetteur() ?? null
        ])
       
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Service',
            'required' => false,
            'attr' => [
              'readonly' => true,
            ],
            'data' => $options["data"]->getServiceEmetteur() ?? null
        ])
        
        ->add('salarie',
        ChoiceType::class,
        [
            'mapped' => false,
            'label' => 'Salarié',
            'choices' => self::SALARIE,
            'data' => 'PERMANENT'
        ])
        ->add('sousTypeDocument',
        EntityType::class,
        [
            'label' => 'Type de Mission',
            'class' => SousTypeDocument::class,
            'choice_label' => 'codeSousType'
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options){
            $form = $event->getForm();
            $data = $event->getData();   
            $sousTypedocument = $data->getSousTypeDocument();
            if(substr($data->getAgenceEmetteur(),0,2) === '50'){
                $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
               
           } else {
            $rmq = $this->em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
           }

           $criteria = [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq
            ];
                
            $catg = $this->em->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);
  
            $categories = [];

            foreach ($catg as $value) {
                $categories[] = $this->em->getRepository(Catg::class)->find($value['id']);
            }
    
            $form->add('categorie',
            EntityType::class,
            [
                'label' => 'Catégorie',
                'class' => Catg::class,
                'choice_label' => 'description',
                'query_builder' => function(CatgRepository $catg) {
                        return $catg->createQueryBuilder('c')->orderBy('c.description', 'ASC');
                    },
                    'choices' => $categories,
                ]);
        })
        ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event)  {
            $form = $event->getForm();
            $data = $event->getData();
                
                $sousTypeDocument = $this->em->getRepository(SousTypeDocument::class)->find($data['sousTypeDocument']);
                
                $categories = $sousTypeDocument->getCatg();
                $form->add('categorie',
                EntityType::class,
                [
                    'label' => 'Catégorie',
                    'class' => Catg::class,
                    'choice_label' => 'description',
                    'query_builder' => function(CatgRepository $catg) {
                            return $catg->createQueryBuilder('c')->orderBy('c.description', 'ASC');
                        },
                        'choices' => $categories,
                    ]);
               
            })
        ->add('matriculeNom',
        EntityType::class,
        [
            'mapped' => false,
            'label' => 'Matricule et nom',
            'class' => Personnel::class,
            'placeholder' => '-- choisir une personnel',
            'choice_label' => function(Personnel $personnel): string {
                return $personnel->getMatricule() . ' ' . $personnel->getNom() . ' ' . $personnel->getPrenoms();
            },
            'required' => false
        ])
        ->add('matricule',
        TextType::class,
        [
            'label' => 'Matricule',
            'attr' => [
                'readonly' => true
            ],
            'required' => false
        ]
        )
        ->add('nom',
        TextType::class,
        [
            'label' => 'Nom',
            'required' => false
        ])
        ->add('prenom',
        TextType::class,
        [
            'label' => 'Prénoms',
            'required' => false
        ])
        ->add('cin',
        NumberType::class,
        [
            'label' => 'CIN',
            'required' => false
        ])
        ;
        
        
    
    }



    
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Dom::class,
            ]);
        }


}