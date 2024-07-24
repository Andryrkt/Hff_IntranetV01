<?php


namespace App\Twig;


use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;


class DeleteWordExtension extends AbstractExtension 
{

        public function getFilters(): array
    {
        return [
            new TwigFilter('remove_words', [$this, 'removeWords']),
        ];
    }

    public function removeWords(?string $text, array $words): string
    {
        if (is_null($text)) {
            return '';
        }
        foreach ($words as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '[sS]?\b/u';
            $text = preg_replace($pattern, '', $text);
        }

        return trim(preg_replace('/\s+/', ' ', $text)); // Pour nettoyer les espaces multiples
    }
    
}

