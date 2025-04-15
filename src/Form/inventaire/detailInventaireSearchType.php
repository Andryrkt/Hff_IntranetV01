<?php

namespace App\Form\inventaire;

use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Model\inventaire\InventaireModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class detailInventaireSearchType extends AbstractType
{
    use Transformation;
    private $InventaireModel;
    private ?\DateTime $datefin = null;
    private ?\DateTime $dateDebut = null;
    public function __construct()
    {
        $this->InventaireModel = new InventaireModel;
        $this->datefin = new \DateTime();
        $this->dateDebut = clone $this->datefin;
        $this->dateDebut->modify('first day of this month');
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
                'data'=>$agence['01-ANTANANARIVO']
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Début',
                'required' => false,
                'data' => $this->dateDebut
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Fin',
                'required' => false,
                'data' => $this->datefin
            ])
           
            ;
    }
}
