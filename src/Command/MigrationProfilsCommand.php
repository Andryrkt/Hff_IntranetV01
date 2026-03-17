<?php

namespace App\Command;

use App\Entity\admin\AgenceService;
use App\Entity\admin\Application;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\utilisateur\Profil;
use App\Repository\admin\AgenceServiceRepository;
use App\Repository\admin\ApplicationRepository;
use App\Repository\admin\historisation\pageConsultation\PageHffRepository;
use App\Repository\admin\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationProfilsCommand extends Command
{
    protected static $defaultName = 'app:migration:profils';

    private EntityManagerInterface $entityManager;
    private ProfilRepository $profilRepository;
    private ApplicationRepository $applicationRepository;
    private PageHffRepository $hffPageRepository;
    private AgenceServiceRepository $agenceServiceRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager           = $entityManager;
        $this->profilRepository        = $entityManager->getRepository(Profil::class);
        $this->applicationRepository   = $entityManager->getRepository(Application::class);
        $this->hffPageRepository       = $entityManager->getRepository(PageHff::class);
        $this->agenceServiceRepository = $entityManager->getRepository(AgenceService::class);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migration des profils, applications, pages et agences/services depuis un fichier JSON.')
            ->setHelp(
                "Cette commande importe en base de données les profils définis dans un fichier JSON.\n\n" .
                    "Pour chaque profil, les opérations suivantes sont effectuées dans l'ordre :\n" .
                    "  1. Vérification de l'existence du profil (ref_profil + societe_id)\n" .
                    "  2. Création ou mise à jour du profil selon la stratégie choisie\n" .
                    "  3. Résolution et liaison des applications par code_app\n" .
                    "  4. Résolution et liaison des pages par nom_route + application\n" .
                    "  5. Résolution et liaison des agences/services par code_agence + code_service\n\n" .
                    "Les références introuvables en base (pages, agences/services) sont loggées\n" .
                    "en avertissement et ignorées — la migration continue sur les éléments suivants.\n\n" .
                    "Chaque profil est traité dans sa propre transaction : une erreur sur un profil\n" .
                    "n'impacte pas les autres.\n\n" .
                    "Structure JSON attendue :\n" .
                    "  [\n" .
                    "    {\n" .
                    "      \"ref_profil\": \"SUP-ADMIN\",\n" .
                    "      \"designation_profil\": \"SUPER ADMINISTRATEUR\",\n" .
                    "      \"societe_id\": 1,\n" .
                    "      \"applications\": [\n" .
                    "        {\n" .
                    "          \"code_app\": \"INTRANET\",\n" .
                    "          \"pages\": [\n" .
                    "            {\n" .
                    "              \"nom_route\": \"dashboard_index\",\n" .
                    "              \"peut_voir\": true,\n" .
                    "              \"peut_voir_liste_avec_debiteur\": false,\n" .
                    "              \"peut_multi_succursale\": false,\n" .
                    "              \"peut_supprimer\": true,\n" .
                    "              \"peut_exporter\": true\n" .
                    "            }\n" .
                    "          ],\n" .
                    "          \"agences_services\": [\n" .
                    "            { \"code_agence\": \"HFF-TANA\", \"code_service\": \"DSI\" }\n" .
                    "          ]\n" .
                    "        }\n" .
                    "      ]\n" .
                    "    }\n" .
                    "  ]\n\n" .
                    "Exemples d'utilisation :\n" .
                    "  Simulation complète (rien n'est écrit en base) :\n" .
                    "    php bin/console app:migration:profils --dry-run\n\n" .
                    "  Migration réelle avec fichier par défaut :\n" .
                    "    php bin/console app:migration:profils\n\n" .
                    "  Migration d'un seul profil :\n" .
                    "    php bin/console app:migration:profils --profil=SUP-ADMIN\n\n" .
                    "  Fichier JSON personnalisé :\n" .
                    "    php bin/console app:migration:profils --fichier=/chemin/vers/profils.json\n\n" .
                    "  Ignorer tous les doublons sans demander :\n" .
                    "    php bin/console app:migration:profils --doublon=ignorer\n\n" .
                    "  Mettre à jour tous les doublons sans demander :\n" .
                    "    php bin/console app:migration:profils --doublon=mettre-a-jour\n"
            )
            ->addOption(
                'fichier',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Chemin vers le fichier JSON des profils',
                dirname(__DIR__, 3) . '/config/migration/profils.json'
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
                'Migrer uniquement un profil précis (ref_profil)'
            )
            ->addOption(
                'doublon',
                null,
                InputOption::VALUE_OPTIONAL,
                'Comportement par défaut pour les doublons : ignorer|mettre-a-jour|demander',
                'demander'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io            = new SymfonyStyle($input, $output);
        $dryRun        = $input->getOption('dry-run');
        $filtre        = $input->getOption('profil');
        $doublonDefaut = $input->getOption('doublon');

        $io->title('📦 Migration des profils' . ($dryRun ? ' [DRY-RUN]' : ''));

        if ($dryRun) {
            $io->note('Mode DRY-RUN activé — aucune modification ne sera persistée en base.');
        }

        // ── Chargement du JSON ────────────────────────────────────────────────
        $fichier = $input->getOption('fichier');

        if (!file_exists($fichier)) {
            $io->error("Fichier introuvable : $fichier");
            return Command::FAILURE;
        }

        $donnees = json_decode(file_get_contents($fichier), true);
        if (!is_array($donnees) || empty($donnees)) {
            $io->error('Le fichier JSON est invalide ou vide.');
            return Command::FAILURE;
        }

        // Filtrage optionnel sur un profil précis
        if ($filtre !== null) {
            $donnees = array_values(array_filter(
                $donnees,
                fn(array $p) => $p['ref_profil'] === $filtre
            ));
            if (empty($donnees)) {
                $io->error("Aucun profil trouvé avec ref_profil = \"$filtre\" dans le fichier.");
                return Command::FAILURE;
            }
        }

        $io->text(sprintf('%d profil(s) à traiter.', count($donnees)));
        $io->newLine();

        // ── Pré-analyse : détecter les doublons avant de commencer ───────────
        $io->section('Pré-analyse des doublons...');
        $doublons = [];

        foreach ($donnees as $data) {
            $profilExistant = $this->profilRepository->findOneBy([
                'refProfil' => $data['ref_profil'],
                'societe'   => $data['societe_id'],
            ]);

            if ($profilExistant !== null) {
                $doublons[$data['ref_profil']] = $profilExistant;
            }
        }

        if (empty($doublons)) {
            $io->text('✔ Aucun doublon détecté.');
        } else {
            $io->warning(sprintf(
                '%d profil(s) existent déjà en base : %s',
                count($doublons),
                implode(', ', array_keys($doublons))
            ));
        }

        $io->newLine();

        // ── Choix global pour les doublons ────────────────────────────────────
        $strategieGlobale = null;

        if (!empty($doublons) && $doublonDefaut === 'demander') {
            $choixGlobal = $io->choice(
                'Des doublons ont été détectés. Comment souhaitez-vous les gérer ?',
                [
                    'cas-par-cas'  => 'Décider au cas par cas pour chaque profil doublon',
                    'ignorer-tous' => 'Ignorer tous les doublons (skip)',
                    'maj-tous'     => 'Mettre à jour tous les doublons (écrase désignation + recrée les liaisons)',
                ],
                'cas-par-cas'
            );

            if ($choixGlobal === 'ignorer-tous') {
                $strategieGlobale = 'ignorer';
            } elseif ($choixGlobal === 'maj-tous') {
                $strategieGlobale = 'mettre-a-jour';
            }
            // null => cas par cas
        } elseif ($doublonDefaut !== 'demander') {
            $strategieGlobale = $doublonDefaut;
        }

        // ── Confirmation finale ───────────────────────────────────────────────
        if (!$io->confirm(
            sprintf('Lancer la migration pour %d profil(s) ?', count($donnees)),
            true
        )) {
            $io->text('Opération annulée.');
            return Command::SUCCESS;
        }

        $io->newLine();

        // ── Traitement ───────────────────────────────────────────────────────
        $conn           = $this->entityManager->getConnection();
        $avertissements = [];
        $stats          = [
            'profils_crees'   => 0,
            'profils_maj'     => 0,
            'profils_ignores' => 0,
            'pages_liees'     => 0,
            'pages_ignorees'  => 0,
            'agserv_liees'    => 0,
            'agserv_ignorees' => 0,
        ];

        $io->section('Migration en cours...');
        $io->progressStart(count($donnees));

        foreach ($donnees as $data) {
            $refProfil   = $data['ref_profil'];
            $designation = $data['designation_profil'];
            $societeId   = (int) $data['societe_id'];
            $now         = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $estDoublon  = isset($doublons[$refProfil]);

            try {
                $conn->beginTransaction();

                // ── Gestion du doublon ────────────────────────────────────
                if ($estDoublon) {
                    $profilExistant = $doublons[$refProfil];
                    $profilId       = $profilExistant->getId();
                    $strategie      = $strategieGlobale;

                    if ($strategie === null) {
                        // Pause + question interactive
                        $io->progressFinish();
                        $io->newLine();
                        $io->note(sprintf(
                            'Le profil "%s" (id: %d, societe_id: %d) existe déjà en base.',
                            $refProfil,
                            $profilId,
                            $societeId
                        ));

                        $strategie = $io->choice(
                            "Que faire pour \"$refProfil\" ?",
                            [
                                'ignorer'       => 'Ignorer — conserver les données existantes, passer au suivant',
                                'mettre-a-jour' => 'Mettre à jour — écraser la désignation et recréer toutes les liaisons',
                            ],
                            'ignorer'
                        );

                        $io->newLine();
                        $restants = count($donnees)
                            - $stats['profils_crees']
                            - $stats['profils_maj']
                            - $stats['profils_ignores']
                            - 1;
                        $io->progressStart($restants);
                    }

                    if ($strategie === 'ignorer') {
                        $avertissements[] = "⚠ Profil \"$refProfil\" déjà existant — ignoré.";
                        $stats['profils_ignores']++;
                        $conn->rollBack();
                        $io->progressAdvance();
                        continue;
                    }

                    // Mise à jour : suppression des anciennes liaisons puis recréation
                    if (!$dryRun) {
                        $appProfilIds = $conn->fetchFirstColumn(
                            'SELECT id FROM application_profil WHERE profil_id = ?',
                            [$profilId]
                        );

                        if (!empty($appProfilIds)) {
                            $placeholders = implode(',', array_fill(0, count($appProfilIds), '?'));
                            $conn->executeStatement(
                                "DELETE FROM application_profil_agence_service WHERE application_profil_id IN ($placeholders)",
                                $appProfilIds
                            );
                            $conn->executeStatement(
                                "DELETE FROM application_profil_page WHERE application_profil_id IN ($placeholders)",
                                $appProfilIds
                            );
                            $conn->executeStatement(
                                'DELETE FROM application_profil WHERE profil_id = ?',
                                [$profilId]
                            );
                        }

                        $conn->executeStatement(
                            'UPDATE profil SET designation_profil = ?, date_modification = ? WHERE id = ?',
                            [$designation, $now, $profilId]
                        );
                    }

                    $stats['profils_maj']++;
                } else {
                    // ── Création d'un nouveau profil ──────────────────────
                    if (!$dryRun) {
                        $conn->executeStatement(
                            'INSERT INTO profil (ref_profil, designation_profil, date_creation, date_modification, societe_id)
                             VALUES (?, ?, ?, ?, ?)',
                            [$refProfil, $designation, $now, $now, $societeId]
                        );
                        $profilId = (int) $conn->lastInsertId();
                    } else {
                        $profilId = 0;
                    }

                    $stats['profils_crees']++;
                }

                // ── Applications + pages + agences/services ───────────────
                foreach ($data['applications'] as $appData) {
                    $codeApp     = $appData['code_app'];
                    $application = $this->applicationRepository->findOneBy(['codeApp' => $codeApp]);

                    if ($application === null) {
                        $avertissements[] = "⚠ [Profil \"$refProfil\"] Application \"$codeApp\" introuvable — ignorée.";
                        continue;
                    }

                    $applicationId = $application->getId();

                    if (!$dryRun) {
                        $conn->executeStatement(
                            'INSERT INTO application_profil (application_id, profil_id) VALUES (?, ?)',
                            [$applicationId, $profilId]
                        );
                        $applicationProfilId = (int) $conn->lastInsertId();
                    } else {
                        $applicationProfilId = 0;
                    }

                    // ── Pages ─────────────────────────────────────────────
                    foreach ($appData['pages'] ?? [] as $pageData) {
                        $nomRoute = $pageData['nom_route'];
                        $page     = $this->hffPageRepository->findOneBy([
                            'nomRoute'    => $nomRoute,
                            'application' => $application,
                        ]);

                        if ($page === null) {
                            $avertissements[] = "⚠ [Profil \"$refProfil\" / App \"$codeApp\"] Page \"$nomRoute\" introuvable ou non liée à l'application — ignorée.";
                            $stats['pages_ignorees']++;
                            continue;
                        }

                        if (!$dryRun) {
                            $conn->executeStatement(
                                'INSERT INTO application_profil_page
                                    (application_profil_id, page_id, peut_voir,
                                     peut_voir_liste_avec_debiteur, peut_multi_succursale,
                                     peut_supprimer, peut_exporter)
                                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                                [
                                    $applicationProfilId,
                                    $page->getId(),
                                    (int) ($pageData['peut_voir'] ?? 1),
                                    (int) ($pageData['peut_voir_liste_avec_debiteur'] ?? 0),
                                    (int) ($pageData['peut_multi_succursale'] ?? 0),
                                    (int) ($pageData['peut_supprimer'] ?? 0),
                                    (int) ($pageData['peut_exporter'] ?? 0),
                                ]
                            );
                        }

                        $stats['pages_liees']++;
                    }

                    // ── Agences / Services ────────────────────────────────
                    foreach ($appData['agences_services'] ?? [] as $agServData) {
                        $codeAgence  = $agServData['code_agence'];
                        $codeService = $agServData['code_service'];

                        $agenceService = $this->agenceServiceRepository
                            ->findOneByCodeAgenceAndCodeService($codeAgence, $codeService);

                        if ($agenceService === null) {
                            $avertissements[] = "⚠ [Profil \"$refProfil\" / App \"$codeApp\"] AgenceService \"$codeAgence/$codeService\" introuvable — ignoré.";
                            $stats['agserv_ignorees']++;
                            continue;
                        }

                        if (!$dryRun) {
                            $conn->executeStatement(
                                'INSERT INTO application_profil_agence_service
                                    (application_profil_id, agence_service_id)
                                 VALUES (?, ?)',
                                [$applicationProfilId, $agenceService->getId()]
                            );
                        }

                        $stats['agserv_liees']++;
                    }
                }

                if (!$dryRun) {
                    $conn->commit();
                } else {
                    $conn->rollBack();
                }
            } catch (\Throwable $e) {
                if ($conn->isTransactionActive()) {
                    $conn->rollBack();
                }
                $avertissements[] = sprintf('✗ ERREUR profil "%s" : %s', $refProfil, $e->getMessage());
                $stats['profils_ignores']++;
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->newLine();

        // ── Avertissements ───────────────────────────────────────────────────
        if (!empty($avertissements)) {
            $io->section('Avertissements & anomalies');
            foreach ($avertissements as $msg) {
                $io->text('  ' . $msg);
            }
            $io->newLine();
        }

        // ── Résumé final ─────────────────────────────────────────────────────
        $io->success(
            ($dryRun ? "[DRY-RUN] Simulation terminée.\n" : "Migration terminée.\n") .
                "  • {$stats['profils_crees']} profil(s) créé(s)\n" .
                "  • {$stats['profils_maj']} profil(s) mis à jour\n" .
                "  • {$stats['profils_ignores']} profil(s) ignoré(s) / en erreur\n" .
                "  • {$stats['pages_liees']} page(s) liée(s)\n" .
                "  • {$stats['pages_ignorees']} page(s) ignorée(s)\n" .
                "  • {$stats['agserv_liees']} agence/service lié(s)\n" .
                "  • {$stats['agserv_ignorees']} agence/service ignoré(s)"
        );

        return empty(array_filter($avertissements, fn($m) => str_starts_with($m, '✗')))
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
