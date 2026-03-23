<?php

namespace App\Command\migration;

use App\Entity\admin\utilisateur\Profil;
use App\Entity\admin\utilisateur\ProfilUser;
use App\Entity\admin\utilisateur\User;
use App\Repository\admin\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationAffectationProfilsCommand extends Command
{
    protected static $defaultName = 'app:migration:affectation-profils';

    private EntityManagerInterface $entityManager;
    private ProfilRepository $profilRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager    = $entityManager;
        $this->profilRepository = $entityManager->getRepository(Profil::class);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migration des affectations de profils (profil_id -> user_id) depuis un fichier JSON.')
            ->setHelp(
                "Cette commande importe en base de données les affectations de profils définies dans un fichier JSON.\n\n" .
                    "Pour chaque affectation, les opérations suivantes sont effectuées dans l'ordre :\n" .
                    "  1. Vérification de l'existence du profil (ref_profil + societe_id)\n" .
                    "  2. Vérification de l'existence de l'utilisateur (username)\n" .
                    "  3. Création ou mise à jour de l'affectation selon la stratégie choisie\n\n" .
                    "Les références introuvables en base (profils, utilisateurs) sont loggées\n" .
                    "en avertissement et ignorées — la migration continue sur les éléments suivants.\n\n" .
                    "Chaque affectation est traitée dans sa propre transaction : une erreur sur une affectation\n" .
                    "n'impacte pas les autres.\n\n" .
                    "Structure JSON attendue :\n" .
                    "  [\n" .
                    "    {\n" .
                    "      \"ref_profil\": \"SUP-ADMIN\",\n" .
                    "      \"societe_id\": 1,\n" .
                    "      \"username\": \"admin\"\n" .
                    "    }\n" .
                    "  ]\n\n" .
                    "Exemples d'utilisation :\n" .
                    "  Simulation complète (rien n'est écrit en base) :\n" .
                    "    php bin/console app:migration:affectation-profils --dry-run\n\n" .
                    "  Migration réelle avec fichier par défaut :\n" .
                    "    php bin/console app:migration:affectation-profils\n\n" .
                    "  Migration d'une seule affectation :\n" .
                    "    php bin/console app:migration:affectation-profils --profil=SUP-ADMIN --username=admin\n\n" .
                    "  Fichier JSON personnalisé :\n" .
                    "    php bin/console app:migration:affectation-profils --fichier=/chemin/vers/affectations.json\n\n" .
                    "  Ignorer tous les doublons sans demander :\n" .
                    "    php bin/console app:migration:affectation-profils --doublon=ignorer\n\n" .
                    "  Mettre à jour tous les doublons sans demander :\n" .
                    "    php bin/console app:migration:affectation-profils --doublon=mettre-a-jour"
            )
            ->addOption(
                'fichier',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Chemin vers le fichier JSON des affectations',
                dirname(__DIR__, 3) . '/config/migration/affectations_profils.json'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Simule la migration sans écrire en base de données'
            )
            ->addOption(
                'profil',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Filtrer sur un ref_profil spécifique'
            )
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Filtrer sur un username spécifique'
            )
            ->addOption(
                'doublon',
                null,
                InputOption::VALUE_OPTIONAL,
                'Stratégie de gestion des doublons : demander, ignorer ou mettre-a-jour',
                'demander'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔄 Migration des affectations de profils');
        $io->text([
            'Cette commande importe en base de données les affectations de profils définies dans un fichier JSON.',
            'Pour chaque affectation, les opérations suivantes seront effectuées :',
            '  • Vérification de l\'existence du profil (ref_profil + societe_id)',
            '  • Vérification de l\'existence de l\'utilisateur (username)',
            '  • Création ou mise à jour de l\'affectation selon la stratégie choisie',
            '',
            'Les références introuvables en base (profils, utilisateurs) sont loggées',
            'en avertissement et ignorées — la migration continue sur les éléments suivants.',
        ]);
        $io->newLine();

        // ── Chargement du fichier JSON ──────────────────────────────────────
        $fichier = $input->getOption('fichier');
        if (!file_exists($fichier)) {
            $io->error(sprintf('Le fichier spécifié n\'existe pas : %s', $fichier));
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($fichier);
        if ($jsonContent === false) {
            $io->error(sprintf('Impossible de lire le fichier : %s', $fichier));
            return Command::FAILURE;
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error(sprintf('Erreur de parsing JSON : %s', json_last_error_msg()));
            return Command::FAILURE;
        }

        if (!is_array($data)) {
            $io->error('Le fichier JSON doit contenir un tableau d\'affectations.');
            return Command::FAILURE;
        }

        $io->text(sprintf('Fichier chargé : <info>%s</info>', $fichier));
        $io->text(sprintf('%d affectation(s) potentielle(s) trouvée(s).', count($data)));
        $io->newLine();

        // ── Filtrage des affectations ──────────────────────────────────────
        $refProfilFiltre = $input->getOption('profil');
        $usernameFiltre  = $input->getOption('username');

        if ($refProfilFiltre || $usernameFiltre) {
            $data = array_filter($data, function ($item) use ($refProfilFiltre, $usernameFiltre) {
                if ($refProfilFiltre && $item['ref_profil'] !== $refProfilFiltre) {
                    return false;
                }
                if ($usernameFiltre && $item['username'] !== $usernameFiltre) {
                    return false;
                }
                return true;
            });

            $io->text(sprintf(
                'Filtrage appliqué : ref_profil=%s, username=%s',
                $refProfilFiltre ?? 'tous',
                $usernameFiltre ?? 'tous'
            ));
            $io->text(sprintf('%d affectation(s) après filtrage.', count($data)));
            $io->newLine();
        }

        if (empty($data)) {
            $io->warning('Aucune affectation à traiter après filtrage.');
            return Command::SUCCESS;
        }

        // ── Stratégie de gestion des doublons ──────────────────────────────
        $doublonStrategy = $input->getOption('doublon');
        $dryRun          = $input->getOption('dry-run');

        if ($dryRun) {
            $io->warning('Mode simulation');
            $io->text('Aucune modification ne sera apportée à la base de données.');
            $io->newLine();
        } else {
            $io->text(sprintf('Stratégie de gestion des doublons : <info>%s</info>', $doublonStrategy));
            $io->newLine();
        }

        // ── Statistiques ───────────────────────────────────────────────────
        $stats = [
            'total'          => count($data),
            'crees'          => 0,
            'mis_a_jour'     => 0,
            'doublons'       => 0,
            'profils_inconnus' => 0,
            'utilisateurs_inconnus' => 0,
            'erreurs'        => 0,
        ];

        // ── Traitement de chaque affectation ──────────────────────────────
        foreach ($data as $index => $item) {
            $io->text(sprintf(
                '[%d/%d] Traitement de l\'affectation : ref_profil=%s, username=%s',
                $index + 1,
                $stats['total'],
                $item['ref_profil'],
                $item['username']
            ));

            $this->entityManager->clear();

            // ── Vérification du profil ─────────────────────────────────────
            $profil = $this->profilRepository->findOneBy([
                'ref_profil' => $item['ref_profil'],
                'societe'    => $item['societe_id'],
            ]);

            if ($profil === null) {
                $io->warning(sprintf(
                    '  ✗ Profil introuvable : ref_profil=%s, societe_id=%d. Affectation ignorée.',
                    $item['ref_profil'],
                    $item['societe_id']
                ));
                $stats['profils_inconnus']++;
                $stats['erreurs']++;
                continue;
            }

            // ── Vérification de l'utilisateur ──────────────────────────────
            $utilisateur = $this->entityManager->getRepository(User::class)->findOneBy([
                'username' => $item['username'],
            ]);

            if ($utilisateur === null) {
                $io->warning(sprintf(
                    '  ✗ Utilisateur introuvable : username=%s. Affectation ignorée.',
                    $item['username']
                ));
                $stats['utilisateurs_inconnus']++;
                $stats['erreurs']++;
                continue;
            }


            // ── Vérification de l'affectation existante ────────────────────
            $affectationExistante = $utilisateur->getProfils()->contains($profil);

            if ($affectationExistante !== null) {
                $stats['doublons']++;

                if ($dryRun) {
                    $io->text('  ⚠️  Doublon détecté (simulation) — sera ignoré.');
                    continue;
                }

                switch ($doublonStrategy) {
                    case 'ignorer':
                        $io->text('  ⚠️  Doublon détecté — ignoré (stratégie: ignorer).');
                        continue 2;

                    case 'mettre-a-jour':
                        $io->text('  🔄  Doublon détecté — ignoré car relation ManyToMany (pas de mise à jour applicable).');
                        $stats['doublons']++;
                        continue 2;

                    case 'demander':
                    default:
                        $io->text('  ❓  Doublon détecté — que faire ?');
                        $choix = $io->choice(
                            'Que faire avec cette affectation existante ?',
                            [
                                'ignorer'  => 'Ignorer (ne pas modifier)',
                                'annuler'  => 'Annuler la migration',
                            ],
                            'ignorer'
                        );

                        if ($choix === 'annuler') {
                            $io->warning('Migration annulée par l\'utilisateur.');
                            return Command::SUCCESS;
                        }

                        $io->text('  ⚠️  Ignorer demandé.');
                        continue 2;
                }
            }


            // ── Création de l'affectation ───────────────────────────────────
            $utilisateur->addProfil($profil);

            $stats['crees']++;
            $io->text('  ✅  Affectation créée.');

            // ── Flush périodique pour éviter les timeouts ───────────────────
            if (($index + 1) % 50 === 0) {
                $this->entityManager->flush();
                $io->text(sprintf('  💾  %d affectation(s) enregistrée(s) en base...', $index + 1));
            }
        }

        // ── Flush final ──────────────────────────────────────────────────
        $this->entityManager->flush();

        // ── Récapitulatif ─────────────────────────────────────────────────
        $io->newLine();
        $io->section('📊 Récapitulatif de la migration');

        $io->text([
            sprintf('Total des affectations traitées : <info>%d</info>', $stats['total']),
            sprintf('  • Créées : <info>%d</info>', $stats['crees']),
            sprintf('  • Mises à jour : <info>%d</info>', $stats['mis_a_jour']),
            sprintf('  • Doublons détectés : <info>%d</info>', $stats['doublons']),
            sprintf('  • Profils introuvables : <info>%d</info>', $stats['profils_inconnus']),
            sprintf('  • Utilisateurs introuvables : <info>%d</info>', $stats['utilisateurs_inconnus']),
            sprintf('  • Erreurs (affectations ignorées) : <info>%d</info>', $stats['erreurs']),
        ]);

        if ($dryRun) {
            $io->warning('Mode simulation : aucune modification n\'a été apportée à la base de données.');
            return Command::SUCCESS;
        }

        if ($stats['erreurs'] > 0) {
            $io->warning(sprintf(
                'La migration est terminée avec %d erreur(s). Vérifiez les avertissements ci-dessus.',
                $stats['erreurs']
            ));
        } else {
            $io->success('La migration des affectations de profils s\'est terminée avec succès !');
        }

        return Command::SUCCESS;
    }
}
