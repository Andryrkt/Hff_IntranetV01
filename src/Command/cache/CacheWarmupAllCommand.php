<?php

namespace App\Command\cache;

use App\Entity\admin\utilisateur\Profil;
use App\Service\navigation\MenuService;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheWarmupAllCommand extends Command
{
    protected static $defaultName = 'app:cache-warmup-all';

    private EntityManagerInterface $entityManager;
    private UserDataService $userDataService;
    private MenuService $menuService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserDataService $userDataService,
        MenuService $menuService
    ) {
        parent::__construct();

        $this->entityManager   = $entityManager;
        $this->userDataService = $userDataService;
        $this->menuService     = $menuService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Préchauffage complet du cache (sécurité, menus, agences/services) pour un ou tous les profils.')
            ->setHelp(
                "Cette commande reconstruit intégralement le cache pour chaque profil sélectionné.\n\n" .
                    "Pour chaque profil, les opérations suivantes sont effectuées dans l'ordre :\n" .
                    "  1. Suppression physique des clés de cache existantes (sécurité + menus)\n" .
                    "  2. Invalidation des versions (sécurité + menus)\n" .
                    "  3. Reconstruction du cache de sécurité   — pages accessibles + permissions par route\n" .
                    "  4. Reconstruction du cache des menus     — menu principal + menu admin\n" .
                    "  5. Reconstruction du cache agences/services — groupé par id et par code application\n\n" .
                    "Les entrées sont taguées par profil, ce qui permet une invalidation groupée\n" .
                    "dès qu'un droit est modifié pour ce profil.\n\n" .
                    "Usage :\n" .
                    "  php bin/console app:cache-warmup-all"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔥 Préchauffage complet du cache — Sécurité, Menus & Agences/Services');
        $io->text([
            'Cette commande va reconstruire intégralement le cache pour chaque profil sélectionné.',
            'Pour chaque profil, les opérations suivantes seront effectuées :',
            '  • Suppression physique + invalidation des versions (sécurité & menus)',
            '  • Reconstruction du cache de sécurité (pages accessibles + permissions par route)',
            '  • Reconstruction du cache des menus (menu principal + menu admin)',
            '  • Reconstruction du cache des agences et services (par id et par code application)',
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

            // ── Sous-choix : recherche par ID ou par désignation ─────────────
            $critere = $io->choice(
                'Comment souhaitez-vous identifier le profil ?',
                [
                    'id'          => 'Par identifiant   — recherche exacte sur l\'ID numérique',
                    'designation' => 'Par désignation   — recherche par le nom du profil (insensible à la casse)',
                ],
                'id'
            );

            if ($critere === 'id') {
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
            } else {
                $designation = $io->ask(
                    'Entrez la désignation du profil à préchauffer',
                    null,
                    function (?string $valeur): string {
                        $valeur = trim((string) $valeur);
                        if ($valeur === '') {
                            throw new \RuntimeException('La désignation ne peut pas être vide.');
                        }
                        return $valeur;
                    }
                );

                $profil = $this->entityManager->getRepository(Profil::class)
                    ->findOneBy(['designation' => $designation]);

                if ($profil === null) {
                    $io->error(sprintf(
                        'Aucun profil trouvé avec la désignation "%s". Vérifiez le nom et relancez la commande.',
                        $designation
                    ));
                    return Command::FAILURE;
                }
            }

            $profils = [$profil];
            $io->newLine();
            $io->text(sprintf(
                'Profil sélectionné : <info>%s</info> (id: %d)',
                $profil->getDesignation(),
                $profil->getId()
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
                'Le cache complet (sécurité, menus, agences/services) va être supprimé puis reconstruit pour %d profil(s). Continuer ?',
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

        $nbSucces       = 0;
        $nbRoutesTotal  = 0;
        $nbCodeAppTotal = 0;
        $erreurs        = [];

        /** @var Profil $profil */
        foreach ($profils as $profil) {
            $profilId = $profil->getId();
            try {
                // 1. Suppression physique des clés de cache
                $this->userDataService->supprimerClesPhysiques($profilId, $profil);
                $this->menuService->supprimerClesPhysiques($profilId);

                // 2. Invalidation des versions
                $this->userDataService->invaliderVersion($profilId);
                $this->menuService->invaliderVersion($profilId);

                // 3. Basculer sur le profil à reconstruire
                $this->userDataService->setProfilId($profilId);

                // 4. Reconstruction du cache de sécurité
                $nbRoutes = $this->userDataService->reconstruireSecurityProfil($profil);
                $nbRoutesTotal += $nbRoutes;

                // 5. Reconstruction du cache des menus
                $this->menuService->reconstruireMenuProfil($profilId);

                // 6. Reconstruction du cache des agences et services
                $nbCodeApp = $this->userDataService->reconstruireAgServProfil($profil);
                $nbCodeAppTotal += $nbCodeApp;

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
            $io->success(
                " - $nbSucces profil(s) mis en cache avec succès.\n" .
                    " - $nbRoutesTotal entrée(s) de permissions de sécurité générées au total.\n" .
                    " - $nbCodeAppTotal entrée(s) d'applications (configurées avec agences services autorisés) générées au total"
            );
        }

        return empty($erreurs) ? Command::SUCCESS : Command::FAILURE;
    }
}
