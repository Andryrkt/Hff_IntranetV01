<?php

namespace App\Command\cache;

use App\Entity\admin\utilisateur\ApplicationProfilPage;
use App\Entity\admin\utilisateur\Profil;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheWarmupSecurityCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:cache-warmup-security';

    private EntityManagerInterface $entityManager;
    private TagAwareCacheInterface $cache;
    private UserDataService $userDataService;

    public function __construct(EntityManagerInterface $entityManager, TagAwareCacheInterface $cache, UserDataService $userDataService)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->userDataService = $userDataService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Préchauffage du cache des permissions sur chaque page pour tous les profils.')
            ->setHelp('Lance le préchauffage du cache pour tous les profils actifs. Usage : php bin/console app:cache-warmup-security');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Préchauffage du cache — tous les profils');

        // Charger tous les profils depuis la BDD
        $profils = $this->entityManager->getRepository(Profil::class)->findAll();

        if (empty($profils)) {
            $io->warning('Aucun profil trouvé en base.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($profils));

        foreach ($profils as $profil) {
            $io->progressAdvance();
            $this->warmupSecurityProfil($profil, $io);
        }

        $io->progressFinish();
        $io->success(sprintf('%d profil(s) mis en cache avec succès.', count($profils)));

        return Command::SUCCESS;
    }

    private function warmupSecurityProfil(Profil $profil, SymfonyStyle $io): void
    {
        $profilId = $profil->getId();
        $tag = UserDataService::CACHE_TAG_PREFIX . $profilId;

        $io->writeln(sprintf('  → Profil <info>%s</info> (id: %d)', $profil->getDesignation(), $profilId));

        // ── 1. Pages du profil (UserDataService::getPagesProfil) ─────────────
        $clePage = sprintf('%s_%s', $tag, UserDataService::SUFFIX_PAGES);
        $this->cache->delete($clePage); // forcer l'écriture
        $this->cache->get(
            $clePage,
            function (ItemInterface $item) use ($profil, $tag) {
                $item->expiresAfter(3600);
                $item->tag($tag);
                return $this->userDataService->calculerPagesProfil($profil);
            }
        );

        // ── 2. Permissions par route (UserDataService::getPermissions) ────────
        $routes = $this->getRoutesForProfil($profil);
        foreach ($routes as $nomRoute) {
            $clePermissions = sprintf('%s_%s_%s', $tag, UserDataService::SUFFIX_PERMISSIONS, md5($nomRoute));
            $this->cache->delete($clePermissions);
            $this->cache->get(
                $clePermissions,
                function (ItemInterface $item) use ($nomRoute, $profil, $tag) {
                    $item->expiresAfter(3600);
                    $item->tag($tag);
                    return $this->userDataService->calculerPermissions($nomRoute, $profil);
                }
            );
        }
    }

    /**
     * Récupère toutes les routes accessibles pour un profil
     * en naviguant dans les relations Doctrine.
     */
    private function getRoutesForProfil(Profil $profil): array
    {
        $routes = [];

        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            /** @var ApplicationProfilPage $applicationProfilPage */
            foreach ($applicationProfil->getLiaisonsPage() as $applicationProfilPage) {
                $nomRoute = $applicationProfilPage->getPage()->getNomRoute();
                if ($nomRoute) $routes[] = $nomRoute;
            }
        }

        return array_unique($routes);
    }
}
