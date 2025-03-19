<?php

namespace App\Form\inventaire;

use Symfony\Component\Form\AbstractType;
use App\Controller\Traits\Transformation;
use App\Entity\inventaire\InventaireSearch;
use App\Model\inventaire\InventaireModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventaireSearchType extends AbstractType
{
    use Transformation;
    private $InventaireModel;
    private ?\DateTime $dateDebut = null;
    const STOCK = [
        'PRINCIPAL' => 'PRINCIPAL',
        'SECONDAIRE' => 'SECONDAIRE'
    ];
    public function __construct()
    {
        $this->InventaireModel = new InventaireModel;
        $this->dateDebut = new \DateTime();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $agence = $this->transformEnSeulTableauAvecKey($this->InventaireModel->recuperationAgenceIrium());
        $builder
            ->add('agence', ChoiceType::class, [
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
                'data' => $this->dateDebut
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date Fin',
                'required' => false,
            ])
            ->add('stock', ChoiceType::class, [
                'label' => 'stock',
                'required' => false,
                'choices' => self::STOCK,
                'attr' => [ 'class' => 'stock'],
                'data' => 'PRINCIPAL',
                'placeholder' => ' -- Choisir une stock --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventaireSearch::class,
        ]);
    }
}
