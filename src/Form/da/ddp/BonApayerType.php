<?php

namespace App\Form\da\ddp;



use App\Dto\Da\ddp\BapSearchDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BonApayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numDa', TextType::class, [
                'label'         => 'Numéro DA',
                'required'      => false
            ])
            ->add('numCde', TextType::class, [
                'label'         => 'Numéro cde',
                'required'      => false
            ])
            ->add('numLivIps', TextType::class, [
                'label'         => 'Numéro livraison IPS',
                'required'      => false
            ])
            ->add('numDdp', TextType::class, [
                'label'         => 'Numéro DDP',
                'required'      => false
            ])
            ->add('FactureBl', TextType::class, [
                'label'         => 'Facture BL',
                'required'      => false
            ])
            ->add('fournisseur', TextType::class, [
                'label'         => 'Fournisseur',
                'required'      => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BapSearchDto::class,
        ]);
    }
}
