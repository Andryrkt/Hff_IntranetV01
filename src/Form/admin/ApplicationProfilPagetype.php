<?php

namespace App\Form\admin;

use App\Dto\admin\AppProfilPageDTO;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationProfilPagetype extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('page', EntityType::class, [
            'label'        => false,
            'class'        => PageHff::class,
            'disabled'     => true,
            'choice_label' => 'nom',
        ]);

        foreach ($this->permissionsDisponibles() as $champ => $_) {
            $builder->add($champ, CheckboxType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'class'            => 'form-check-input case-permission fs-5',
                    'data-colonne'     => $champ,
                    'data-depend-voir' => $champ !== 'peutVoir' ? 'true' : false,
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppProfilPageDTO::class,
        ]);
    }

    /**
     * Retourne la définition des 5 permissions dans l'ordre d'affichage.
     */
    public static function permissionsDisponibles(): array
    {
        return [
            'peutVoir'      => ['label' => 'Voir',      'icon' => 'fa-eye',          'couleur' => 'primary'],
            'peutAjouter'   => ['label' => 'Ajouter',   'icon' => 'fa-plus-circle',  'couleur' => 'info'],
            'peutModifier'  => ['label' => 'Modifier',  'icon' => 'fa-pencil',       'couleur' => 'warning'],
            'peutSupprimer' => ['label' => 'Supprimer', 'icon' => 'fa-trash',        'couleur' => 'danger'],
            'peutExporter'  => ['label' => 'Exporter',  'icon' => 'fa-download',     'couleur' => 'success'],
        ];
    }
}
