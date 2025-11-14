<?php

namespace App\Form\magasin\devis;

use App\Form\common\DateRangeType;
use App\Form\common\AgenceServiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Entity\magasin\devis\DevisMagasin;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DevisMagasinSearchType extends AbstractType
{
    private $statutsDw;
    private $statutsBc;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $statutsDwRaw = $this->em->getRepository(DevisMagasin::class)->getStatutsDw();
        $statutsBcRaw = $this->em->getRepository(DevisMagasin::class)->getStatutsBc();

        // Transformer le tableau en format de choix pour le formulaire (statut DW)
        $this->statutsDw = [];
        foreach ($statutsDwRaw as $statut) {
            if (!empty($statut)) {
                $this->statutsDw[$statut] = $statut;
            }
        }

        $this->statutsBc = [];
        foreach ($statutsBcRaw as $statut) {
            if (!empty($statut)) {
                $this->statutsBc[$statut] = $statut;
            }
        }
    }

    private const STATUT_IPS = [
        '--' => '--',
        'AC' => 'AC',
        'DE' => 'DE',
        'RE' => 'RE',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroDevis', TextType::class, [
                'label' => 'Numéro de devis',
                'required' => false,
            ])
            ->add('codeClient', TextType::class, [
                'label' => 'code Client',
                'required' => false
            ])
            ->add('Operateur', TextType::class, [
                'label' => 'Soumis par',
                'required' => false
            ])
            ->add('CreePar', TextType::class, [
                'label' => 'Crée par',
                'required' => false
            ])
            ->add('statutDw', ChoiceType::class, [
                'label' => 'Statut devis',
                'placeholder' => '-- Choisir le choix --',
                'choices' => $this->statutsDw,
                'required' => false
            ])
            ->add('statutBc', ChoiceType::class, [
                'label' => 'Statut BC',
                'placeholder' => '-- Choisir le choix --',
                'choices' => $this->statutsBc,
                'required' => false
            ])
            ->add('statutIps', ChoiceType::class, [
                'label' => 'Position IPS',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::STATUT_IPS,
                'required' => false
            ])
            ->add('emetteur', AgenceServiceType::class, [
                'label' => false,
                'required' => false,
                'agence_label' => 'Agence Emetteur',
                'service_label' => 'Service Emetteur',
                'agence_placeholder' => '-- Agence Emetteur --',
                'service_placeholder' => '-- Service Emetteur --',
                'em' => $options['em'] ?? null,
            ])
            // ->add('debitteur', AgenceServiceType::class, [
            //     'label' => false,
            //     'required' => false,
            //     'agence_label' => 'Agence Debiteur',
            //     'service_label' => 'Service Debiteur',
            //     'agence_placeholder' => '-- Agence Debiteur --',
            //     'service_placeholder' => '-- Service Debiteur --',
            // ])
            ->add('dateCreation', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date création (début)',
                'fin_label' => 'Date création (fin)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ListeDevisSearchDto::class,
            'em' => null,
        ]);
    }
}
