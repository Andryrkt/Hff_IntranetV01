<?php

namespace App\Twig;

use App\Entity\admin\historisation\JournalCopieDocuware;
use App\Service\security\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Fournit le nombre de copies DocuWare en échec non vues, pour le badge du menu Administrateur.
 * Non mis en cache (contrairement au menu admin) afin que le compteur soit toujours à jour.
 */
class JournalCopieDocuwareExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;
    private SecurityService $securityService;
    private ?int $nbEchecsNonVus = null;

    public function __construct(EntityManagerInterface $entityManager, SecurityService $securityService)
    {
        $this->entityManager   = $entityManager;
        $this->securityService = $securityService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nbEchecsCopieDocuware', [$this, 'nbEchecsCopieDocuware']),
        ];
    }

    public function nbEchecsCopieDocuware(): int
    {
        if ($this->nbEchecsNonVus !== null) {
            return $this->nbEchecsNonVus;
        }

        try {
            $this->nbEchecsNonVus = $this->securityService->estAdmin()
                ? $this->entityManager->getRepository(JournalCopieDocuware::class)->countEchecsNonVus()
                : 0;
        } catch (\Throwable $e) {
            // table absente ou base indisponible : on n'empêche pas l'affichage de la page
            $this->nbEchecsNonVus = 0;
        }

        return $this->nbEchecsNonVus;
    }
}
