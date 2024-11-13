<?php

namespace App\Form\admin\tik;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TkiStatusTicketInformatiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroTicket', TextType::class, [
                'label' => 'Numéro de Ticket',
                'attr' => ['maxlength' => 3],
            ])
            ->add('codeStatut', TextType::class, [
                'label' => 'Code Statut',
            ])
            ->add('dateStatut', DateTimeType::class, [
                'label' => 'Date de Statut',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TkiStatusTicketInformatiqueType::class,
        ]);
    }
}
?>