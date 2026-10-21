<?php

namespace App\Twig;

use App\Entity\admin\DocumentationUtilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * guide_utilisateur('slug') : le guide d'un écran (ou null), pour le bouton
 * « Guide d'utilisation » (partials/_lienGuide.html.twig)
 */
class GuideUtilisateurExtension extends AbstractExtension
{
    private ManagerRegistry $doctrine;
    private array $guides = [];

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('guide_utilisateur', [$this, 'guide'])];
    }

    public function guide(string $slug): ?DocumentationUtilisateur
    {
        if (!array_key_exists($slug, $this->guides)) {
            $this->guides[$slug] = $this->doctrine->getManager()
                ->getRepository(DocumentationUtilisateur::class)
                ->findOneBy(['slug' => $slug]);
        }

        return $this->guides[$slug];
    }
}
