<?php

namespace App\Form\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Form\Common\FileUploadType;
use Symfony\Component\Form\FormEvent;
use App\Form\common\AgenceServiceType;
use Symfony\Component\Form\FormEvents;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use App\Entity\cde\CdefnrSoumisAValidation;
use Symfony\Component\Form\FormBuilderInterface;
use App\Repository\ddp\DemandePaiementRepository;
use App\Constants\ddp\TypeDemandePaiementConstants;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DemandePaiementDaType extends AbstractType
{
    private $demandePaiementModel;

    public function __construct()
    {
        $this->demandePaiementModel = new DemandePaiementModel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'motif',
                TextType::class,
                [
                    'label' => 'Motif',
                    'required' => false
                ]
            )
            ->add(
                'ribFournisseur',
                TextType::class,
                [
                    'label' => 'RIB *',
                    'attr' => [
                        'readOnly' => true
                    ]
                ]
            )
            ->add(
                'contact',
                TextType::class,
                [
                    'label' => 'Contact',
                    'required' => false
                ]
            )
            ->add(
                'modePaiement',
                ChoiceType::class,
                [
                    'label'     => 'Mode de paiement *',
                    'choices'   =>  $this->mode_paiement(),
                    'multiple'  => false,
                    'expanded'  => false,
                    'data' => 'VIREMENT'
                ]
            )
            ->add(
                'devise',
                ChoiceType::class,
                [
                    'label'     => 'Devise *',
                    'choices'   =>  $this->devise(),
                    'multiple'  => false,
                    'expanded'  => false,
                ]
            )
            ->add(
                'montantAPayer',
                TextType::class,
                [
                    'label' => 'Montant à payer *',
                    'attr' => [
                        'readOnly' => true
                    ]
                ]
            )
        ;

        $this->addAgenceServiceDebiteur($builder);
        $this->addFournisseur($builder);
        $this->addFile($builder);
        $this->addNumeroCdeAndFacture($builder, $options);
    }

    private function addFournisseur(FormBuilderInterface $builder)
    {
        $builder->add(
            'numeroFournisseur',
            TextType::class,
            [
                'label' => 'Fournisseur *',
                'attr' => [
                    'class' => 'autocomplete',
                    'autocomplete' => 'off',
                ],
            ]
        )
            ->add(
                'beneficiaire',
                TextType::class,
                [
                    'label' => 'Bénéficiaire *',
                    'attr' => [
                        'class' => 'autocomplete',
                        'autocomplete' => 'off',
                    ]
                ]
            );
    }

    private function addNumeroCdeAndFacture(FormBuilderInterface $builder, $options)
    {
        $typeDemandeId = $options['data']->typeDemande->getId();
        $numeroFournisseur = $options['data']->numeroFournisseur;
        $typeDa = $options['data']->typeDa;
        $numCde = $options['data']->numeroCommande;
        $numFac = $options['data']->numeroFacture;

        $builder
            ->add(
                'numeroCommande',
                ChoiceType::class,
                [
                    'label'     => 'N° Commande fournisseur *',
                    'choices'   => $typeDa !== null ? (!empty($numCde) ? array_combine($numCde, $numCde) : []) : $this->numeroCmd($typeDemandeId),
                    'multiple'  => $typeDa !== null ? false : true,
                    'expanded'  => false,
                    'attr'      => [
                        'disabled' => $typeDemandeId == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE || ($typeDemandeId === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE && $typeDa !== null),
                    ],
                    'data' => $typeDa !== null ? ($numCde[0] ?? null) : $numCde,
                ]
            )
            ->add(
                'numeroFacture',
                ChoiceType::class,
                [
                    'label' => 'N° Facture fournisseur *',
                    'required' => false,
                    'choices'   => $typeDa !== null ? (!empty($numFac) ? array_combine($numFac, $numFac) : []) : $this->numeroFac($numeroFournisseur, $typeDemandeId),
                    'multiple'  => $typeDa !== null ? false : true,
                    'expanded'  => false,
                    'attr'      => [
                        'disabled' => $typeDemandeId == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE,
                        'data-typeId' => $typeDemandeId
                    ],
                    'data' => $typeDa !== null ? ($numFac[0] ?? null) : $numFac,
                ]
            )
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($typeDemandeId) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($typeDemandeId == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE) {
                    $form->add(
                        'numeroCommande',
                        ChoiceType::class,
                        [
                            'label'     => 'N° Commande *',
                            'choices'   => $data['numeroCommande'] ?? [],
                            'multiple'  => true,
                            'expanded'  => false,
                        ]
                    );
                }

                $form->add(
                    'numeroFacture',
                    ChoiceType::class,
                    [
                        'label' => 'N° Facture *',
                        'choices'   => $data['numeroFacture'] ?? [],
                        'multiple'  => true,
                        'expanded'  => false,
                        'required' => false
                    ]
                );
            });
    }

    private function addAgenceServiceDebiteur(FormBuilderInterface $builder): void
    {
        $builder->add('debiteur', AgenceServiceType::class, [
            'label' => false,
            'required' => false,
            'mapped' => false,
            'agence_label' => 'Agence Debiteur',
            'service_label' => 'Service Debiteur',
            'agence_placeholder' => '-- Agence Debiteur --',
            'service_placeholder' => '-- Service Debiteur --',
            'em' => $options['em'] ?? null,
        ]);
    }

    private function addFile(FormBuilderInterface $builder): void
    {
        $builder->add(
            'pieceJoint01',
            FileUploadType::class,
            [
                'label' => 'Pièce joint 01',
                'required' => false,
                'allowed_mime_types' => ['application/pdf'],
                'accept' => '.pdf',
                'max_size' => '5M'
            ]
        )
            ->add(
                'pieceJoint02',
                FileUploadType::class,
                [
                    'label' => 'Pièce joint 01',
                    'required' => false,
                    'allowed_mime_types' => ['application/pdf'],
                    'accept' => '.pdf',
                    'max_size' => '5M'
                ]
            )
            ->add(
                'pieceJoint03',
                FileUploadType::class,
                [
                    'label' => 'Pièce joint 03',
                    'required' => false,
                    'allowed_mime_types' => ['application/pdf'],
                    'accept' => '.pdf',
                    'max_size' => '5M'
                ]
            )
            ->add(
                'pieceJoint04',
                FileUploadType::class,
                [
                    'label' => 'Pièce joint 04',
                    'required' => false,
                    'allowed_mime_types' => ['application/pdf'],
                    'accept' => '.pdf',
                    'max_size' => '5M'
                ]
            )
        ;
    }

    private function mode_paiement(): array
    {
        $modePaiement = $this->demandePaiementModel->getModePaiement();
        return array_combine($modePaiement, $modePaiement);
    }

    private function devise(): array
    {
        $devisess = $this->demandePaiementModel->getDevise();

        $devises = [
            '' => '',
        ];

        foreach ($devisess as $devise) {
            $devises[$devise['adevlib']] = $devise['adevcode'];
        }

        return $devises;
    }

    private function numeroCmd($typeId): array
    {
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        return array_combine($numCdes, $numCdes);
    }

    private function recuperationCdeFacEtNonFac(int $typeId): array
    {
        $numCdeDws = $this->demandePaiementModel->getNumCdeDw();
        $numCdes1 = [];
        $numCdes2 = [];
        foreach ($numCdeDws as $numCdeDw) {
            $numfactures = $this->demandePaiementModel->cdeFacOuNonFac($numCdeDw);
            if (!empty($numfactures)) {
                $numCdes2[] = $numCdeDw;
            } else {
                $numCdes1[] = $numCdeDw;
            }
        }
        $numCdes = [];

        if ($typeId == TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $numCdes = $numCdes2;
        } else {
            $numCdes = $numCdes1;
        }
        return $numCdes;
    }

    private function numeroFac(?int $numeroFournisseur, int $typeId): array
    {
        $numCdes = $this->recuperationCdeFacEtNonFac($typeId);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);

        if ($numeroFournisseur) {

            $listeGcot = $this->demandePaiementModel->finListFacGcot($numeroFournisseur, $numCdesString);
            return array_combine($listeGcot, $listeGcot);
        }

        return [];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandePaiementDto::class,
        ]);
    }
}
