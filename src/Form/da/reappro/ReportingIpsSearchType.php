<?php

namespace App\Form\da\reappro;

use Doctrine\ORM\EntityRepository;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Form\common\DateRangeType;
use App\Service\GlobalVariablesService;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportingIpsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aujourdhui = new \DateTime();
        $debutAnnee = (new \DateTime())->modify('first day of january this year');
        $choices = $this->createAssociativeArray(GlobalVariablesService::get('reappro'));

        $builder
            ->add('agences', EntityType::class, [
                'label' => 'Agences Débitrices',
                'class' => Agence::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.codeAgence', 'ASC');
                },
                'choice_label' => function (Agence $agence): string {
                    return $agence->getCodeAgence() . ' ' . $agence->getLibelleAgence();
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'data' => $this->getAllAgences($options['em']), // Sélectionner toutes les agences par défaut
            ])
            ->add('services', EntityType::class, [
                'label' => 'Services Débiteurs',
                'class' => Service::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.codeService', 'ASC');
                },
                'choice_label' => function (Service $service): string {
                    return $service->getCodeService() . ' ' . $service->getLibelleService();
                },
                'by_reference' => false,
                'multiple' => true,
                'expanded' => false, // Rendu en tant que <select>
                'required' => false,
                'attr' => ['class' => 'select2-enable'] // Classe pour le ciblage JS
            ])
            ->add('date', DateRangeType::class, [
                'label' => false,
                'debut_label' => 'Date (début)',
                'fin_label' => 'Date (fin)',
                'data' => [
                    'debut' => $debutAnnee,
                    'fin' => $aujourdhui,
                ],
            ])
            ->add('constructeur', ChoiceType::class, [
                'label' => 'Constructeur',
                'required' => false,
                'choices' => $choices,
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

    private function getAllAgences(EntityManager $entityManager): array
    {
        return $entityManager->getRepository(Agence::class)
            ->findAll();
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
