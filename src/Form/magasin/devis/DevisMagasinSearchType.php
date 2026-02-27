<?php

namespace App\Form\magasin\devis;

use App\Form\common\DateRangeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Traits\PrepareAgenceServiceTrait;
use App\Entity\magasin\devis\DevisMagasin;
use App\Factory\magasin\devis\ListeDevisSearchDto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DevisMagasinSearchType extends AbstractType
{
    use PrepareAgenceServiceTrait;
    private $statutsDw;
    private $statutsBc;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $statutsDwRaw = $this->em->getRepository(DevisMagasin::class)->getStatutsDw();
        $statutsBcRaw = $this->em->getRepository(DevisMagasin::class)->getStatutsBc();

        // Transformer le tableau en format de choix pour le formulaire (statut DW)
        $this->statutsDw = [DevisMagasin::STATUT_A_TRAITER => DevisMagasin::STATUT_A_TRAITER];
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
        'TR' => 'TR',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $this->prepareAgenceServiceChoices($options['agenceServiceAutorises'], false);

        $agenceChoices = $choices['agenceChoices'];
        $serviceChoices = $choices['serviceChoices'];
        $serviceAttr = $choices['serviceAttr'];

        $builder
            ->add('numeroPO', TextType::class, [
                'label' => 'PO/BC client',
                'required' => false,
                'data' => $options['data']->getNumeroPO(),
            ])
            ->add('numeroDevis', TextType::class, [
                'label' => 'Numéro de devis',
                'required' => false,
                'data' => $options['data']->getNumeroDevis(),
            ])
            ->add('codeClient', TextType::class, [
                'label' => 'code Client',
                'required' => false,
                'data' => $options['data']->getCodeClient(),
            ])
            ->add('Operateur', TextType::class, [
                'label' => 'Soumis par',
                'required' => false,
                'data' => $options['data']->getOperateur(),
            ])
            ->add('CreePar', TextType::class, [
                'label' => 'Crée par',
                'required' => false,
                'data' => $options['data']->getCreePar(),
            ])
            ->add('statutDw', ChoiceType::class, [
                'label' => 'Statut devis',
                'placeholder' => '-- Choisir le choix --',
                'choices' => $this->statutsDw,
                'required' => false,
                'data' => $options['data']->getStatutDw(),
            ])
            ->add('statutBc', ChoiceType::class, [
                'label' => 'Statut BC',
                'placeholder' => '-- Choisir le choix --',
                'choices' => $this->statutsBc,
                'required' => false,
                'data' => $options['data']->getStatutBc(),
            ])
            ->add('statutIps', ChoiceType::class, [
                'label' => 'Position IPS',
                'placeholder' => '-- Choisir le choix --',
                'choices' => self::STATUT_IPS,
                'required' => false,
                'data' => $options['data']->getStatutIps(),
            ])
            ->add('dateCreation', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date création (début)',
                'fin_label' => 'Date création (fin)',
                'data_date_debut' => $options['data']->getDateCreation()['debut'] ?? null,
                'data_date_fin' => $options['data']->getDateCreation()['fin'] ?? null,
            ])
            // --- agenceDebiteur : ChoiceType ---
            ->add('agenceEmetteur', ChoiceType::class, [
                'label'       => 'Agence Emetteur',
                'placeholder' => '-- Choisir une agence --',
                'required'    => false,
                'choices'     => $agenceChoices
            ])
            // --- serviceDebiteur : ChoiceType ---
            ->add('serviceEmetteur', ChoiceType::class, [
                'label'       => 'Service Emetteur',
                'placeholder' => '-- Choisir une service --',
                'required'    => false,
                'choices'     => $serviceChoices,
                'choice_label' => function ($value) use ($options) {
                    // Retrouver le bon item et afficher service_code . ' ' . service_libelle
                    $item = $options['agenceServiceAutorises'][$value];
                    return $item['service_code'] . ' ' . $item['service_libelle'];
                },
                'choice_attr' => function ($val) use ($serviceAttr) {
                    return $serviceAttr[$val] ?? [];
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em' => null,
            'data_class' => ListeDevisSearchDto::class,
            'agenceServiceAutorises' => []
        ]);
    }
}
