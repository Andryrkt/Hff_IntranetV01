<?php

namespace App\Command\cache;

use App\Entity\admin\utilisateur\Profil;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheWarmupSecurityCommand extends Command
{
    protected static $defaultName = 'app:cache-warmup-security';

    private EntityManagerInterface $entityManager;
    private UserDataService $userDataService;

    public function __construct(EntityManagerInterface $entityManager, UserDataService $userDataService)
    {
        parent::__construct();

        $this->entityManager   = $entityManager;
        $this->userDataService = $userDataService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Préchauffage du cache des permissions par route pour un ou tous les profils.')
            ->setHelp(
                "Cette commande reconstruit et stocke en cache les permissions de sécurité.\n\n" .
                    "Deux types d'entrées sont générées par profil :\n" .
                    "  • Pages du profil     — la liste des pages accessibles (peutVoir = true) pour construire les menus\n" .
                    "  • Permissions/route   — les 5 droits (voir, ajouter, modifier, supprimer, exporter)\n" .
                    "                          mis en cache séparément pour chaque route du profil\n\n" .
                    "Les entrées sont taguées par profil, ce qui permet une invalidation groupée\n" .
                    "dès qu'un droit est modifié pour ce profil.\n\n" .
                    "Usage :\n" .
                    "  php bin/console app:cache-warmup-security"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔒 Préchauffage du cache — Permissions de sécurité');
        $io->text([
            'Cette commande va reconstruire le cache des permissions pour chaque profil sélectionné.',
            'Pour chaque profil, deux types d\'entrées sont générées :',
            '  • La liste des pages accessibles (utilisée pour construire les menus et la navigation)',
            '  • Les permissions détaillées pour chaque route (voir, ajouter, modifier, supprimer, exporter)',
            '',
            'Les anciennes entrées sont supprimées avant d\'être recréées,',
            'garantissant une cohérence totale avec les droits actuels en base de données.',
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
                'Le cache des permissions va être supprimé puis reconstruit pour %d profil(s). Continuer ?',
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

        $nbSucces      = 0;
        $nbRoutesTotal = 0;
        $erreurs       = [];

        foreach ($profils as $profil) {
            try {
                $nbRoutes = $this->userDataService->warmupSecurityProfil($profil);
                $nbRoutesTotal += $nbRoutes;
                $nbSucces++;
            } catch (\Throwable $e) {
                $erreurs[] = sprintf('Profil "%s" (id: %d) : %s', $profil->getDesignation(), $profil->getId(), $e->getMessage());
            }

            $io->progressAdvance();
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
                "%d profil(s) mis en cache avec succès.\n%d entrée(s) de permissions générées au total. (1 entrée « pages » + 1 par route pour chaque profil)",
                $nbSucces,
                $nbRoutesTotal + $nbSucces // +1 par profil pour l'entrée "pages"
            ));
        }

        return empty($erreurs) ? Command::SUCCESS : Command::FAILURE;
    }
}
