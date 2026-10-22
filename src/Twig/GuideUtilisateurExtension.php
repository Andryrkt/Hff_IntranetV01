<?php

namespace App\Twig;

use App\Entity\admin\DocumentationUtilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * guide_utilisateur('slug') : le guide d'un écran (ou null), pour le bouton
 * « Guide d'utilisation » (partials/_lienGuide.html.twig)
 * guides_annonces() : guides publiés dont une mise à jour a été annoncée
 * récemment (partials/_annonceGuides.html.twig)
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
        return [
            new TwigFunction('guide_utilisateur', [$this, 'guide']),
            new TwigFunction('guides_annonces', [$this, 'guidesAnnonces']),
        ];
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

    /**
     * Annonces des 30 derniers jours : au-delà, la mise à jour n'est plus une nouveauté
     *
     * @return DocumentationUtilisateur[]
     */
    public function guidesAnnonces(): array
    {
        return $this->doctrine->getManager()
            ->getRepository(DocumentationUtilisateur::class)
            ->createQueryBuilder('d')
            ->where('d.publie = true')
            ->andWhere('d.dateAnnonce >= :depuis')
            ->setParameter('depuis', new \DateTime('-30 days'))
            ->orderBy('d.dateAnnonce', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
