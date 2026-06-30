<?php

namespace App\Command\cache;

use App\Entity\admin\societe\Societe;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheWarmupAgServSocieteCommand extends Command
{
    protected static $defaultName = 'app:cache-warmup-ag-serv-societe';

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
            ->setDescription('Préchauffage du cache de tous les agences et services par société.')
            ->setHelp(
                "Cette commande reconstruit et stocke en cache les agences et services par société.\n\n" .
                    "Usage :\n" .
                    "  php bin/console app:cache-warmup-ag-serv-societe"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔒 Préchauffage du cache — Permissions des agences et services par société');
        $io->text([
            'Cette commande va reconstruire le cache des agences et services pour une ou plusieurs sociétés.',
            '',
            'Les anciennes entrées sont supprimées avant d\'être recréées,',
            'garantissant une cohérence totale avec les droits actuels en base de données.',
        ]);
        $io->newLine();

        // ── Choix : toutes les sociétés ou une seule ─────────────────────────
        $choix = $io->choice(
            'Voulez-vous préchauffer le cache pour toutes les sociétés ou pour une société spécifique ?',
            [
                'toutes' => 'Toutes les sociétés — reconstruit le cache de chaque société enregistrée en base',
                'une'    => 'Une seule société   — reconstruit le cache d\'une société précise via son code',
            ],
            'toutes'
        );

        // ── Chargement des sociétés selon le choix ───────────────────────────
        if ($choix === 'une') {
            $codeSociete = $io->ask(
                'Entrez le code de la société à préchauffer',
                null,
                function (?string $valeur): string {
                    if ($valeur === null || trim($valeur) === '') {
                        throw new \RuntimeException('Le code société ne peut pas être vide.');
                    }
                    return trim($valeur);
                }
            );

            $societe = $this->entityManager->getRepository(Societe::class)->findOneBy(['code' => $codeSociete]);

            if ($societe === null) {
                $io->error(sprintf(
                    'Aucune société trouvée avec le code "%s". Vérifiez le code et relancez la commande.',
                    $codeSociete
                ));
                return Command::FAILURE;
            }

            $societes = [$societe];
            $io->newLine();
            $io->text(sprintf(
                'Société sélectionnée : <info>%s</info> (code: %s)',
                $societe->getLibelle(),
                $codeSociete
            ));
        } else {
            $societes = $this->entityManager->getRepository(Societe::class)->findAll();

            if (empty($societes)) {
                $io->warning('Aucune société trouvée en base de données. Rien à préchauffer.');
                return Command::SUCCESS;
            }

            $io->newLine();
            $io->text(sprintf('%d société(s) trouvée(s) en base. Démarrage du préchauffage...', count($societes)));
        }

        $io->newLine();

        // ── Confirmation avant exécution ─────────────────────────────────────
        if (!$io->confirm(
            sprintf(
                'Le cache des agences et services va être supprimé puis reconstruit pour %d société(s). Continuer ?',
                count($societes)
            ),
            true
        )) {
            $io->text('Opération annulée. Aucune modification effectuée.');
            return Command::SUCCESS;
        }

        $io->newLine();

        // ── Traitement ───────────────────────────────────────────────────────
        $io->section('Reconstruction du cache en cours...');
        $io->progressStart(count($societes));

        $nbSucces = 0;
        $erreurs  = [];

        foreach ($societes as $societe) {
            $codeSociete = $societe->getCode();

            try {
                $this->userDataService->ecraserAllAgenceService($codeSociete);
                $nbSucces++;
            } catch (\Throwable $e) {
                $erreurs[] = sprintf('Société "%s" (code: %s) : %s', $societe->getLibelle(), $codeSociete, $e->getMessage());
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->newLine();

        // ── Résumé final ─────────────────────────────────────────────────────
        if (!empty($erreurs)) {
            $io->warning(sprintf('%d société(s) ont rencontré une erreur :', count($erreurs)));
            foreach ($erreurs as $erreur) {
                $io->text('  ✗ ' . $erreur);
            }
            $io->newLine();
        }

        if ($nbSucces > 0) {
            $io->success(sprintf(
                '%d société(s) mises en cache avec succès.',
                $nbSucces
            ));
        }

        return empty($erreurs) ? Command::SUCCESS : Command::FAILURE;
    }
}