<?php


namespace App\Form;

use App\Entity\Badm;
use App\Entity\TypeMouvement;
use App\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BadmForm1Type extends AbstractType
{   
    private $em;

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
            'data' => $options['data']->getAgenceEmetteur()
        ])
        ->add('serviceEmetteur', 
        TextType::class,
        [
            'mapped' => false,
            'label' => 'Service',
            'required' => false,
            'attr' => [
                'readonly' => true,
                'disable' => true
            ],
            'data' => $options['data']->getServiceEmetteur()
        ])
        ->add('idMateriel', TextType::class, [
            'label' => 'Id Materiel',
            'required' => false,
        ])
        ->add('numParc', TextType::class, [
            'label' => "N° Parc",
            'required' => false
        ])
        ->add('numSerie', TextType::class, [
            'label' => "N° Serie",
            'required' => false
        ])
        ->add('typeMouvement', EntityType::class, [
            'label' => 'Type Mouvement',
            'class' => TypeMouvement::class,
            'choice_label' => 'description',
            'placeholder' => '-- Choisir une type de mouvement--',
            'required' => true,
            'data' => $this->em->getRepository(TypeMouvement::class)->find(1)
        ])
        ; 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Badm::class
        ]);
    }
}
