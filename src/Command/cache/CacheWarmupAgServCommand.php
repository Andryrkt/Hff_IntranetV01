<?php

namespace App\Command\cache;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\utilisateur\Profil;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheWarmupAgServCommand extends Command
{
    protected static $defaultName = 'app:cache-warmup-ag-serv';

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
            ->setDescription('Préchauffage du cache des agences et services par application pour un ou tous les profils.')
            ->setHelp(
                "Cette commande reconstruit et stocke en cache les agences et services.\n\n" .
                    "Deux types d'entrées sont générées par profil :\n" .
                    "  • Groupés par ID            — la liste des agences et services par application dans un tableau avec clé id de l'agence-service\n" .
                    "  • Groupés par couple code   — la liste des agence-service dans un tableau avec clé code de l'application\n" .
                    "                          mis en cache séparément pour chaque application du profil\n\n" .
                    "Les entrées sont taguées par profil, ce qui permet une invalidation groupée\n" .
                    "dès qu'un droit est modifié pour ce profil.\n\n" .
                    "Usage :\n" .
                    "  php bin/console app:cache-warmup-ag-serv"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔒 Préchauffage du cache — Permissions des agences et services');
        $io->text([
            'Cette commande va reconstruire le cache des agences et services pour chaque profil sélectionné.',
            'Pour chaque profil, deux types d\'entrées sont générées :',
            '  • La liste des agences et services par application dans un tableau avec clé id de l\'agence-service',
            '  • La liste des agence-service dans un tableau avec clé code de l\'application',
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
                'Le cache des agences et services va être supprimé puis reconstruit pour %d profil(s). Continuer ?',
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
                $nbRoutes = $this->warmupAgServProfil($profil);
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

    // =========================================================================
    //  LOGIQUE DE PRÉCHAUFFAGE
    // =========================================================================

    /**
     * Reconstruit les entrées de cache pour un profil donné.
     * Retourne le nombre de routes mises en cache.
     */
    private function warmupAgServProfil(Profil $profil): int
    {
        $codeApps = $this->getApplicationsForProfil($profil);
        foreach ($codeApps as $codeApp) {
            $this->userDataService->ecraserAgenceServiceGroupById($codeApp, $profil);
            $this->userDataService->ecraserAgenceServiceGroupByCode($codeApp, $profil);
        }

        return 2 * count($codeApps);
    }

    /**
     * Récupère toutes les applications pour un profil
     * en naviguant dans les relations Doctrine (ApplicationProfil → Application).
     * Retourne un tableau de noms de routes dédupliqués.
     */
    private function getApplicationsForProfil(Profil $profil): array
    {
        $applications = [];

        /** @var ApplicationProfil $applicationProfil */
        foreach ($profil->getApplicationProfils() as $applicationProfil) {
            $applications[] = $applicationProfil->getApplication()->getCodeApp();
        }

        return $applications;
    }
}
