<?php

namespace App\Form\inventaire;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Model\inventaire\InventaireModel;
use DateTime;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class detailInventaireSearchType extends AbstractType
{
    use Transformation;
    private $InventaireModel;
    // private ?\DateTime $datefin = null;
    // private ?\DateTime $dateDebut = null;
    public function __construct(InventaireModel $inventaireModel)
    {
        $this->InventaireModel = $inventaireModel;
        // $this->datefin = new \DateTime();
        // $this->dateDebut = clone $this->datefin;
        // $this->dateDebut->modify('first day of this month');
    }

    public function listeInventaireDispo(array $criteria): array
    {
        $listeInventaireDispo = $this->InventaireModel->recuperationListeInventaireDispo($criteria);
        $tab = [];
        foreach ($listeInventaireDispo as $keys => $listes) {
            foreach ($listes as $key => $liste) {
                $tab[trim($key)] =  $liste;
            }
        }

        return $tab;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $agence = $this->transformEnSeulTableauAvecKey($this->InventaireModel->recuperationAgenceIrium());



        $builder
            ->add('agence', ChoiceType::class, [
                'label' => 'Agence',
                'required' => false,
                'choices' => $agence,
                'placeholder' => ' -- choisir agence --',
                'data' => $agence['01-ANTANANARIVO']
            ])

            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Début',
                'required' => false,
                'data' => $options['data']->getDateDebut()
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Fin',
                'required' => false,
                'data' => $options['data']->getDateFin()
            ])

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();


                $criteria = [
                    'agence' => $options['data']->getAgence(),
                    'dateDebut' => $options['data']->getDateDebut(),
                    'dateFin' => $options['data']->getDateFin()
                ];
                $listeInventaireDispo = $this->listeInventaireDispo($criteria);
                $form->add('InventaireDispo', ChoiceType::class, [
                    'label' => 'Inventaire Dispo',
                    'multiple' => true,
                    'choices' => $listeInventaireDispo,
                    'placeholder' => " -- Choisir un inventaire--",
                    'expanded' => true,
                ]);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();


                $criteria = [
                    'agence' => $data['agence'],
                    'dateDebut' => new DateTime($data['dateDebut']),
                    'dateFin' => new DateTime($data['dateFin'])
                ];

                $listeInventaireDispo = $this->listeInventaireDispo($criteria);

                $form->add('InventaireDispo', ChoiceType::class, [
                    'label' => 'Inventaire Dispo',
                    'multiple' => true,
                    'choices' => $listeInventaireDispo,
                    'placeholder' => " -- Choisir un inventaire--",
                    'expanded' => true,
                    'data' => $data["InventaireDispo"] ?? []
                ]);
            })
        ;
    }
}
