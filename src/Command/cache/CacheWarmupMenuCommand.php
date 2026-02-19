<?php

namespace App\Command\cache;

use App\Entity\admin\utilisateur\Profil;
use App\Service\navigation\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheWarmupMenuCommand extends Command
{
    // Le nom de la commande
    protected static $defaultName = 'app:cache-warmup-menu';

    private EntityManagerInterface $entityManager;
    private TagAwareCacheInterface $cache;
    private MenuService $menuService;

    public function __construct(EntityManagerInterface $entityManager, TagAwareCacheInterface $cache, MenuService $menuService)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->menuService = $menuService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Préchauffage du cache des menus (principal et admin) pour tous les profils.')
            ->setHelp('Lance le préchauffage du cache pour tous les profils actifs. Usage : php bin/console app:cache-warmup-menu');
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
            $this->warmupMenuProfil($profil, $io);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d profil(s) mis en cache avec succès.', count($profils)));

        return Command::SUCCESS;
    }

    private function warmupMenuProfil(Profil $profil, SymfonyStyle $io): void
    {
        $profilId = $profil->getId();
        $tag      = 'profil_' . $profilId;

        $io->writeln(sprintf('  → Profil <info>%s</info> (id: %d)', $profil->getDesignation(), $profilId));

        // ── 3. Menu principal (MenuService::getMenuStructure) ─────────────────
        $this->cache->get(
            'menu_principal_' . $profilId,   // ← même clé que MenuService::CACHE_KEY_PRINCIPAL
            function (ItemInterface $item) use ($profilId, $tag) {
                $item->expiresAfter(3600);
                $item->tag([$tag]);
                return $this->menuService->construireMenuPrincipalPourProfil($profilId);
            }
        );

        // ── 4. Menu admin (MenuService::getAdminMenuStructure) ────────────────
        $this->cache->get(
            'menu_admin_' . $profilId,       // ← même clé que MenuService::CACHE_KEY_ADMIN
            function (ItemInterface $item) use ($profilId, $tag) {
                $item->expiresAfter(3600);
                $item->tag([$tag]);
                return $this->menuService->construireMenuAdminPourProfil($profilId);
            }
        );
    }
}
