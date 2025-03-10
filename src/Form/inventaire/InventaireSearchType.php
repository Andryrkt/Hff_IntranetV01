<?php
namespace App\Form\inventaire;

use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Model\inventaire\InventaireModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class InventaireSearchType extends AbstractType{
    use Transformation; 
    private $InventaireModel;
    public function __construct()
    {
        $this->InventaireModel = new InventaireModel;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {  $agence = $this->transformEnSeulTableauAvecKey($this->InventaireModel->recuperationAgenceIrium());
       $builder
       ->add('agence', ChoiceType::class,[
        'label' => 'Agence',
        'required' => false,
        'choices' => $agence,
        'multiple' => true,
        'expanded' => true,
        'placeholder' => ' -- Choisir une agence --',
       ])
       ->add('dateDebut', DateType::class, [
        'widget' => 'single_text',
        'label' => 'Date Début',
        'required' => false,
    ])
    ->add('dateFin', DateType::class, [
        'widget' => 'single_text',
        'label' => 'Date Fin',
        'required' => false,
    ]);


    }
}