<?php

namespace App\Form\da\reappro;

use App\Form\common\DateRangeType;
use App\Form\common\AgenceServiceType;
use App\Service\GlobalVariablesService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportingIpsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('debiteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'agence_label' => 'Agence Debiteur',
                'service_label' => 'Service Debiteur',
                'agence_placeholder' => '-- Agence Debiteur --',
                'service_placeholder' => '-- Service Debiteur --',
                'em' => $options['em'] ?? null
            ])
            ->add('date', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date (début)',
                'fin_label' => 'Date (fin)',
            ])
            ->add('constructeur', ChoiceType::class, [
                'label' => 'Constructeur',
                'required' => false,
                'choices' => $choices = $this->createAssociativeArray(GlobalVariablesService::get('reappro')),
                'multiple' => true,
                'expanded' => true,
                'data' => array_keys($choices), // Cocher toutes les cases par défaut
            ])
            ->add('numFacture', TextType::class, [
                'label' => 'Numéro de facture',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false,
            ])
        ;
    }

    private function createAssociativeArray($inputString)
    {
        // Nettoyer la chaîne et créer un tableau
        $array = explode(',', str_replace("'", "", $inputString));

        // Créer le tableau associatif
        $result = array_combine($array, $array);

        return $result;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
        $resolver->setDefined('em');
    }
}
