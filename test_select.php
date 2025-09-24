<?php

require 'config/bootstrap_di.php';

$twig = $container->get('twig');
$formFactory = $container->get('form.factory');

// Créer un formulaire avec un champ select
$form = $formFactory->createBuilder()
    ->add('select', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
        'label' => 'Choix',
        'choices' => [
            'Option 1' => 'opt1',
            'Option 2' => 'opt2',
            'Option 3' => 'opt3'
        ],
        'placeholder' => 'Sélectionnez une option'
    ])
    ->getForm();

echo "Test du champ select avec Bootstrap 5:\n";
$template = $twig->createTemplate('{{ form_row(form.select) }}');
$rendered = $template->render(['form' => $form->createView()]);
echo htmlspecialchars($rendered) . "\n";

// Vérifier si la classe form-select est présente
if (strpos($rendered, 'form-select') !== false) {
    echo "✅ Classe 'form-select' détectée - Bootstrap 5 appliqué!\n";
} else {
    echo "❌ Classe 'form-select' non détectée\n";
}
