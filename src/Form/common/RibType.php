<?php

namespace App\Form\common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RibType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'RIB *',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Regex([
                    'pattern' => '/^[0-9][0-9 ]*$/',
                    'message' => 'Le RIB doit commencer par un chiffre et ne contenir que des chiffres et des espaces.',
                ]),
                new Assert\Length([
                    'min' => 26, // 23 chiffres + 3 espaces = 26 caractères
                    'max' => 26,
                    'exactMessage' => 'Le RIB doit contenir exactement 23 chiffres et 3 espaces.',
                ]),
                new Assert\Callback(function ($value, ExecutionContextInterface $context) {
                    // Vérification supplémentaire pour s'assurer qu'il y a exactement 23 chiffres et 3 espaces
                    if ($value) {
                        $digits = preg_replace('/[^0-9]/', '', $value);
                        $spaces = substr_count($value, ' ');

                        if (strlen($digits) !== 23) {
                            $context->buildViolation('Le RIB doit contenir exactement 23 chiffres.')
                                ->addViolation();
                        }

                        if ($spaces !== 3) {
                            $context->buildViolation('Le RIB doit contenir exactement 3 espaces.')
                                ->addViolation();
                        }
                    }
                }),
            ],
            'attr' => [
                'placeholder' => '00005 ***** ********* 45',
                'class' => 'rib-field',
                'maxlength' => 26,
                'data-format-rib' => 'true', // Pour le JavaScript
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
