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
    private ?\DateTime $datefin = null;
    private ?\DateTime $dateDebut = null;
    const STOCK = [
        'PRINCIPAL' => 'PRINCIPAL',
        'SECONDAIRE' => 'SECONDAIRE',
    ];
    public function __construct(InventaireModel $inventaireModel)
    {
        $this->InventaireModel = $inventaireModel;
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
                'multiple' => true,
                'expanded' => true,
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
            ->add('stock', ChoiceType::class, [
                'label' => 'stock',
                'required' => true,
                'choices' => self::STOCK,
                'attr' => ['class' => 'stock'],
                'data' => 'PRINCIPAL'
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
