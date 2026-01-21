<?php

namespace App\Form\da\daCdeFrn;

use App\Model\da\DaReapproModel;
use App\Entity\da\DaSoumissionFacBl;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use App\Dto\Da\ListeCdeFrn\DaSoumisionBlReapproDto;
use App\Service\TableauEnStringService;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DaSoumissionBlReapprotype extends AbstractType
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $numeroFactureDansFacBl =  TableauEnStringService::orEnString($this->em->getRepository(DaSoumissionFacBl::class)->getNumeroFactureDansFacBl($options['data']->numCde));
        $numeroFactureReapproChoices = (new DaReapproModel())->getNumeroFactureReappro($options['data']->numOr, $numeroFactureDansFacBl);

        $builder
            ->add('numOr', TextType::class, [
                'label' => 'Numéro OR',
                'mapped' => false,
                'attr' => ['disabled' => true]
            ])
            ->add('numeroFactureReappro', ChoiceType::class, [
                'label' => 'Numéro de facture réappro',
                // 'choices' => $numeroFactureReapproChoices,
                'choices' => ['1457896' => '1457896'],
                'placeholder' => 'Sélectionnez une facture',
                'required' => true,
            ])
            ->add(
                'pieceJoint1',
                FileType::class,
                [
                    'label' => 'BL Reappro à soumettre',
                    'required' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                                // 'image/jpeg',
                                // 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                // 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF file.',
                        ])
                    ],
                ]
            )
            ->add(
                'pieceJoint2',
                FileType::class,
                [
                    'label' => 'Pièces Jointes',
                    'required' => false,
                    'multiple' => true,
                    'data_class' => null,
                    'mapped' => true, // Indique que ce champ ne doit pas être lié à l'entité
                    'constraints' => [
                        new Callback([$this, 'validateFiles']),
                    ],
                ]
            )
        ;
    }

    public function validateFiles($files, ExecutionContextInterface $context)
    {
        $maxSize = '5M';
        $mimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        if ($files) {
            foreach ($files as $file) {
                $fileConstraint = new File([
                    'maxSize' => $maxSize,
                    'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5 Mo.',
                    'mimeTypes' => $mimeTypes,
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide.',
                ]);

                $violations = $context->getValidator()->validate($file, $fileConstraint);

                if (count($violations) > 0) {
                    foreach ($violations as $violation) {
                        $context->buildViolation($violation->getMessage())
                            ->addViolation();
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DaSoumisionBlReapproDto::class,
        ]);
    }
}
