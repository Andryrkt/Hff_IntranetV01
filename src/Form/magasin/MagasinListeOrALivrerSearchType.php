<?php

namespace App\Form\magasin;



use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Service\GlobalVariablesService;
use Symfony\Component\Form\AbstractType;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Model\magasin\MagasinListeOrLivrerModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MagasinListeOrALivrerSearchType extends AbstractType
{
    const OR_COMPLET_OU_NON = [
        'TOUS' => 'TOUTS LES OR',
        'COMPLETS' => 'ORs COMPLET',
        'INCOMPLETS' => 'ORs INCOMPLETS'
    ];

    const PIECE_MAGASIN_ACHATS_LOCAUX = [
        'TOUTES LIGNES' => 'TOUTS PIECES',
        'PIÈCES MAGASIN' => 'PIECES MAGASIN',
        'LUB' => 'LUB',
        'ACHATS LOCAUX' => 'ACHATS LOCAUX'
    ];

    private $magasinModel;

    public function __construct()
    {
        $this->magasinModel = new MagasinListeOrLivrerModel();
    }

    private function recupConstructeur(bool $estPneumatique = false): array
    {
        return $estPneumatique ? $this->createAssociativeArray(GlobalVariablesService::get('pneumatique')) : $this->magasinModel->recuperationConstructeur();
    }

    private function agence()
    {
        return array_combine($this->magasinModel->agence(), $this->magasinModel->agence());
    }

    private function agenceAutoriserUser(string $codeAgence)
    {
        return array_combine($this->magasinModel->agenceUser($codeAgence), $this->magasinModel->agenceUser($codeAgence));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('niveauUrgence', EntityType::class, [
                'label' => 'Niveau d\'urgence',
                'class' => WorNiveauUrgence::class,
                'choice_label' => 'description',
                'placeholder' => '-- Choisir un niveau --',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->orderBy('n.description', 'DESC');
                },
            ])
            ->add('numDit', TextType::class, [
                'label' => 'n° DIT',
                'required' => false
            ])
            ->add('numOr', NumberType::class, [
                'label' => 'n° OR',
                'required' => false
            ])
            ->add('referencePiece', TextType::class, [
                'label' => 'Référence pièce',
                'required' => false
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'required' => false
            ])

            ->add('constructeur', ChoiceType::class, [
                'label' =>  'Constructeur',
                'required' => false,
                'choices' => $this->recupConstructeur($options['est_pneumatique'] ?? false),
                'placeholder' => ' -- Choisir un constructeur --'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de création OR (début)',
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de création OR (fin)',
                'required' => false,
            ])
            ->add(
                'orCompletNon',
                ChoiceType::class,
                [
                    'label' => 'Etat OR',
                    'required' => false,
                    'choices' => self::OR_COMPLET_OU_NON,
                    'placeholder' => false,
                    'data' => 'ORs COMPLET'
                ]
            )
            ->add(
                'pieces',
                ChoiceType::class,
                [
                    'label' => 'Type ligne',
                    'required' => false,
                    'choices' => self::PIECE_MAGASIN_ACHATS_LOCAUX,
                    'placeholder' => ' -- Choisir le type de ligne à afficher --',
                    'data' => 'PIECES MAGASIN'
                ]
            )
            ->add(
                'agence',
                ChoiceType::class,
                [
                    'label' => 'Agence débiteur',
                    'required' => false,
                    'choices' => $this->agence() ?? [],
                    'placeholder' => ' -- Choisir une agence --'
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $form->add(
                    'service',
                    ChoiceType::class,
                    [
                        'label' => 'Service débiteur',
                        'required' => false,
                        'choices' => [],
                        'placeholder' => ' -- Choisir un service --'
                    ]
                );
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $service = [];
                if ($data['agence'] !== "") {
                    $services = $this->magasinModel->service($data['agence']);

                    foreach ($services as $value) {
                        $service[$value['text']] = $value['text'];
                    }
                }


                $form->add(
                    'service',
                    ChoiceType::class,
                    [
                        'label' => 'Service débiteur',
                        'required' => false,
                        'choices' => $service,
                        'placeholder' => ' -- Choisir un service --'
                    ]
                );
            })

            ->add('agenceUser', ChoiceType::class, [
                'label' => 'Agence Emetteur',
                'required' => false,
                'choices' => $this->agenceAutoriserUser($options['data']['agenceUser']) ?? [],
                'placeholder' => ' -- Choisir une agence --',
            ])

            ->add('agenceUserHidden', HiddenType::class, [
                'data' => $options['data']['agenceUser'] ?? null,
            ])

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $data = $event->getData();

                $data['agenceUser'] =  $data['agenceUser'];
                $event->setData($data);
            });
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
        $resolver->setDefined('est_pneumatique');
    }
}
