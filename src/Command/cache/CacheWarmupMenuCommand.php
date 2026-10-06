<?php

namespace App\Command\cache;

use App\Entity\admin\utilisateur\Profil;
use App\Service\navigation\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheWarmupMenuCommand extends Command
{
    protected static $defaultName = 'app:cache-warmup-menu';

    private EntityManagerInterface $entityManager;
    private MenuService $menuService;

    public function __construct(EntityManagerInterface $entityManager, MenuService $menuService)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->menuService   = $menuService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Préchauffage du cache des menus (principal et admin) pour un ou tous les profils.')
            ->setHelp(
                "Cette commande reconstruit et stocke en cache les menus de navigation.\n\n" .
                    "Deux types de menus sont générés par profil :\n" .
                    "  • Menu principal  — la barre de navigation filtrée selon les droits du profil\n" .
                    "  • Menu admin      — le panneau d'administration filtré selon les droits du profil\n\n" .
                    "Les entrées de cache sont taguées par profil (menu.profil_{id}),\n" .
                    "ce qui permet une invalidation ciblée lors d'un changement de droits.\n\n" .
                    "Usage :\n" .
                    "  php bin/console app:cache-warmup-menu"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔥 Préchauffage du cache — Menus de navigation');
        $io->text([
            'Cette commande va reconstruire le cache des menus pour chaque profil sélectionné.',
            'Les anciennes entrées de cache seront supprimées avant d\'être recréées,',
            'garantissant des données fraîches et cohérentes avec les droits actuels en base.',
        ]);
        $io->newLine();

        // ── Choix : tous les profils ou un seul ──────────────────────────────
        $choix = $io->choice(
            'Voulez-vous préchauffer le cache pour tous les profils ou pour un profil spécifique ?',
            [
                'tous' => 'Tous les profils    — reconstruit le cache de chaque profil enregistré en base',
                'un'   => 'Un seul profil      — reconstruit le cache d\'un profil précis via son identifiant',
            ],
            'tous'
        );

        // ── Chargement des profils selon le choix ────────────────────────────
        if ($choix === 'un') {
            $profilId = (int) $io->ask(
                'Entrez l\'identifiant (ID) du profil à préchauffer',
                null,
                function (?string $valeur): int {
                    if (!is_numeric($valeur) || (int) $valeur <= 0) {
                        throw new \RuntimeException('L\'identifiant doit être un nombre entier positif.');
                    }
                    return (int) $valeur;
                }
            );

            $profil = $this->entityManager->getRepository(Profil::class)->find($profilId);

            if ($profil === null) {
                $io->error(sprintf(
                    'Aucun profil trouvé avec l\'identifiant %d. Vérifiez l\'ID et relancez la commande.',
                    $profilId
                ));
                return Command::FAILURE;
            }

            $profils = [$profil];
            $io->newLine();
            $io->text(sprintf(
                'Profil sélectionné : <info>%s</info> (id: %d)',
                $profil->getDesignation(),
                $profilId
            ));
        } else {
            $profils = $this->entityManager->getRepository(Profil::class)->findAll();

            if (empty($profils)) {
                $io->warning('Aucun profil trouvé en base de données. Rien à préchauffer.');
                return Command::SUCCESS;
            }

            $io->newLine();
            $io->text(sprintf('%d profil(s) trouvé(s) en base. Démarrage du préchauffage...', count($profils)));
        }

        $io->newLine();

        // ── Confirmation avant exécution ─────────────────────────────────────
        if (!$io->confirm(
            sprintf(
                'Le cache des menus va être supprimé puis reconstruit pour %d profil(s). Continuer ?',
                count($profils)
            ),
            true
        )) {
            $io->text('Opération annulée. Aucune modification effectuée.');
            return Command::SUCCESS;
        }

        $io->newLine();

        // ── Traitement ───────────────────────────────────────────────────────
        $io->section('Reconstruction du cache en cours...');
        $io->progressStart(count($profils));

        $nbSucces = 0;
        $erreurs  = [];

        /** @var Profil $profil */
        foreach ($profils as $profil) {
            $profilId = $profil->getId();
            $io->progressAdvance();
            try {
                $this->menuService->warmupMenuProfil($profilId);
                $nbSucces++;
            } catch (\Throwable $e) {
                $erreurs[] = sprintf('Profil "%s" (id: %d) : %s', $profil->getDesignation(), $profil->getId(), $e->getMessage());
            }
        }

        $io->progressFinish();
        $io->newLine();

        // ── Résumé final ─────────────────────────────────────────────────────
        if (!empty($erreurs)) {
            $io->warning(sprintf('%d profil(s) ont rencontré une erreur :', count($erreurs)));
            foreach ($erreurs as $erreur) {
                $io->text('  ✗ ' . $erreur);
            }
            $io->newLine();
        }

        if ($nbSucces > 0) {
            $io->success(sprintf(
                '%d profil(s) mis en cache avec succès. (Menu principal + Menu admin générés pour chacun)',
                $nbSucces
            ));
        }

        return empty($erreurs) ? Command::SUCCESS : Command::FAILURE;
    }
}
