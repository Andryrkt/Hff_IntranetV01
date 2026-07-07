<?php

namespace App\Command\dit;

use DateTime;
use App\Model\dit\DitOrSoumisAValidationModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour insérer les données des OR depuis Informix (IPS)
 * dans la table SQL Server `ors_soumis_a_validation`.
 *
 * La liste des numéros OR est définie directement dans la commande.
 *
 * Utilisation :
 *   php bin/console app:insert-or-soumis-validation
 *   php bin/console app:insert-or-soumis-validation --code-societe=HF
 *   php bin/console app:insert-or-soumis-validation --dry-run
 *
 * Pour passer une liste personnalisée :
 *   php bin/console app:insert-or-soumis-validation --numeros=16425325,16425378,41329720
 */
class InsertOrSoumisAValidationCommand extends Command
{
    protected static $defaultName = 'app:insert-or-soumis-validation';

    /**
     * Liste des numéros OR à insérer (définis dans la demande utilisateur).
     * Modifiez cette liste si nécessaire ou utilisez l'option --numeros.
     */
    private const LISTE_NUMEROS_OR_PAR_DEFAUT = [
        '16425325',
        '16425378',
        '16425379',
        '21322216',
        '21322217',
        '21322218',
        '41329720',
        '41330126',
        '41330153',
        '41330163',
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Insère les données des OR Informix (IPS) dans la table ors_soumis_a_validation (SQL Server).')
            ->setHelp(
                "Cette commande récupère les données de chaque OR depuis Informix (IPS)\n"
                . "et les insère dans la table HFF_INTRANET.dbo.ors_soumis_a_validation.\n\n"
                . "Utilisation :\n"
                . "  php bin/console app:insert-or-soumis-validation\n"
                . "  php bin/console app:insert-or-soumis-validation --code-societe=HF\n"
                . "  php bin/console app:insert-or-soumis-validation --numeros=16425325,16425378\n"
                . "  php bin/console app:insert-or-soumis-validation --dry-run\n"
            )
            ->addOption(
                'code-societe',
                null,
                InputOption::VALUE_OPTIONAL,
                'Code société Informix (défaut : HF)',
                'HF'
            )
            ->addOption(
                'numeros',
                null,
                InputOption::VALUE_OPTIONAL,
                'Liste de numéros OR séparés par des virgules (remplace la liste par défaut)',
                null
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Simule l\'insertion sans écrire en base de données'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $codeSociete = $input->getOption('code-societe');
        $isDryRun    = $input->getOption('dry-run');

        // Détermination de la liste des numéros OR
        $numerosOption = $input->getOption('numeros');
        if ($numerosOption !== null) {
            $numerosOr = array_filter(array_map('trim', explode(',', $numerosOption)));
        } else {
            $numerosOr = self::LISTE_NUMEROS_OR_PAR_DEFAUT;
        }

        $io->title('Insertion des OR dans ors_soumis_a_validation');
        $io->writeln("Société    : <info>$codeSociete</info>");
        $io->writeln("Mode       : <info>" . ($isDryRun ? 'DRY-RUN (simulation)' : 'RÉEL') . "</info>");
        $io->writeln("Nb OR      : <info>" . count($numerosOr) . "</info>");
        $io->writeln("Numéros OR : <info>" . implode(', ', $numerosOr) . "</info>");
        $io->newLine();

        $model = new DitOrSoumisAValidationModel();
        $conn  = $this->entityManager->getConnection();

        $now             = new DateTime();
        $dateSoumission  = $now->format('Y-m-d');
        $heureSoumission = $now->format('H:i');

        $totalInseres = 0;
        $totalErreurs = 0;
        $recapOrs     = [];

        // ── Traitement de chaque numéro OR ───────────────────────────────────
        foreach ($numerosOr as $numOr) {
            $io->section("Traitement OR N° $numOr");

            // 1. Récupération depuis Informix avec la requête étendue (avec ausr_nom)
            $lignes = $this->recupOrAvecCreateur($model, $numOr, $codeSociete);

            if (empty($lignes)) {
                $io->warning("Aucune donnée trouvée dans Informix pour l'OR $numOr (société : $codeSociete). Ignoré.");
                $recapOrs[] = [
                    'numero_or' => $numOr,
                    'nb_lignes' => 0,
                    'nb_inseres' => 0,
                    'statut' => 'IGNORÉ (aucune donnée)',
                ];
                continue;
            }

            // 2. Calcul du numéro de version
            $versionMax = $conn->fetchOne(
                'SELECT MAX(numeroVersion) FROM ors_soumis_a_validation WHERE numeroOR = ? AND code_societe = ?',
                [$numOr, $codeSociete]
            );
            $prochainVersion = ($versionMax === null || $versionMax === false) ? 1 : (int)$versionMax + 1;

            $io->writeln(
                "  → <info>" . count($lignes) . " ligne(s)</info> trouvée(s) | Version : <info>$prochainVersion</info>"
            );

            // 3. Récupération du numéro DIT depuis Informix
            $numDit = $lignes[0]['numero_dit'] ?? null;

            // 4. Insertion de chaque ligne ITV
            $nbInseres = 0;
            $nbErreurs = 0;

            $progressBar = new ProgressBar($output, count($lignes));
            $progressBar->start();

            foreach ($lignes as $ligne) {
                try {
                    if (!$isDryRun) {
                        $conn->insert('ors_soumis_a_validation', [
                            'numeroOR'                  => $numOr,
                            'numeroItv'                 => $ligne['numero_itv']           ?? 0,
                            'nombreLigneItv'            => $ligne['nombre_ligne']          ?? 0,
                            'montantItv'                => $ligne['montant_itv']           ?? 0.00,
                            'numeroVersion'             => $prochainVersion,
                            'montantPiece'              => $ligne['montant_piece']         ?? 0.00,
                            'montantMo'                 => $ligne['montant_mo']            ?? 0.00,
                            'montantAchatLocaux'        => $ligne['montant_achats_locaux'] ?? 0.00,
                            'montantFraisDivers'        => $ligne['montant_divers']        ?? 0.00,
                            'montantLubrifiants'        => $ligne['montant_lubrifiants']   ?? 0.00,
                            'libellelItv'               => $ligne['libelle_itv']           ?? null,
                            'dateSoumission'            => $dateSoumission,
                            'heureSoumission'           => $heureSoumission,
                            'statut'                    => 'Soumis à validation',
                            'migration'                 => 1,
                            'numeroDIT'                 => $numDit,
                            'observation'               => null,
                            'piece_faible_activite_achat' => 0,
                            'date_validation_or'        => null,
                            'code_societe'              => $codeSociete,
                        ]);
                    }
                    $nbInseres++;
                } catch (\Exception $e) {
                    $nbErreurs++;
                    $io->newLine();
                    $io->warning(
                        "  Erreur ITV {$ligne['numero_itv']} (OR $numOr) : " . $e->getMessage()
                    );
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $io->newLine();

            $totalInseres += $nbInseres;
            $totalErreurs += $nbErreurs;

            $statutRecap = $isDryRun ? "SIMULÉ ($nbInseres lignes)" : ($nbErreurs === 0 ? "OK ($nbInseres insérées)" : "PARTIEL ($nbInseres ok / $nbErreurs erreurs)");

            $recapOrs[] = [
                'numero_or'   => $numOr,
                'numero_dit'  => $numDit ?? '-',
                'nb_lignes'   => count($lignes),
                'nb_inseres'  => $nbInseres,
                'version'     => $prochainVersion,
                'statut'      => $statutRecap,
            ];

            if ($nbErreurs === 0) {
                $io->writeln("  ✅ OR $numOr : $nbInseres ligne(s) " . ($isDryRun ? 'simulée(s)' : 'insérée(s)') . " (version $prochainVersion)");
            } else {
                $io->writeln("  ⚠️  OR $numOr : $nbInseres OK / $nbErreurs erreur(s)");
            }
        }

        // ── Récapitulatif final ───────────────────────────────────────────────
        $io->newLine();
        $io->section('Récapitulatif général');
        $io->table(
            ['N° OR', 'N° DIT', 'Nb ITV', 'Nb insérées', 'Version', 'Statut'],
            array_map(fn($r) => [
                $r['numero_or'],
                $r['numero_dit']  ?? '-',
                $r['nb_lignes'],
                $r['nb_inseres'],
                $r['version']     ?? '-',
                $r['statut'],
            ], $recapOrs)
        );

        if ($totalErreurs === 0) {
            $io->success(
                "$totalInseres ligne(s) au total " . ($isDryRun ? 'simulée(s)' : 'insérée(s)') . " avec succès pour " . count($numerosOr) . " OR."
            );
        } else {
            $io->warning("$totalInseres ligne(s) insérée(s), $totalErreurs erreur(s) au total.");
        }

        return $totalErreurs === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Récupère les données d'un OR depuis Informix avec la jointure agr_usr
     * (pour récupérer le créateur). Correspond exactement à la requête fournie.
     *
     * @param DitOrSoumisAValidationModel $model
     * @param string                      $numOr
     * @param string                      $codeSociete
     * @return array
     */
    private function recupOrAvecCreateur(DitOrSoumisAValidationModel $model, string $numOr, string $codeSociete): array
    {
        // On utilise la méthode recupOrSoumisValidation du model existant
        // puis on enrichit avec la jointure agr_usr si besoin.
        // La requête ci-dessous correspond exactement à celle fournie dans la demande.
        $data = $this->executerRequeteOrInformix($model, $numOr, $codeSociete);

        // Normalisation des clés (Informix retourne parfois en majuscules ou minuscules)
        return array_map(function (array $row): array {
            return [
                'slor_numor'           => $row['slor_numor']          ?? $row['SLOR_NUMOR']          ?? null,
                'numero_dit'           => $row['numero_dit']          ?? $row['NUMERO_DIT']          ?? null,
                'numero_itv'           => $row['numero_itv']          ?? $row['NUMERO_ITV']          ?? 0,
                'libelle_itv'          => $row['libelle_itv']         ?? $row['LIBELLE_ITV']         ?? null,
                'nombre_ligne'         => $row['nombre_ligne']        ?? $row['NOMBRE_LIGNE']        ?? 0,
                'montant_itv'          => $row['montant_itv']         ?? $row['MONTANT_ITV']         ?? 0.00,
                'montant_piece'        => $row['montant_piece']       ?? $row['MONTANT_PIECE']       ?? 0.00,
                'montant_mo'           => $row['montant_mo']          ?? $row['MONTANT_MO']          ?? 0.00,
                'montant_achats_locaux'=> $row['montant_achats_locaux'] ?? $row['MONTANT_ACHATS_LOCAUX'] ?? 0.00,
                'montant_divers'       => $row['montant_divers']      ?? $row['MONTANT_DIVERS']      ?? 0.00,
                'montant_lubrifiants'  => $row['montant_lubrifiants'] ?? $row['MONTANT_LUBRIFIANTS'] ?? 0.00,
                'createur_or'          => $row['createur_or']         ?? $row['CREATEUR_OR']         ?? null,
            ];
        }, $data);
    }

    /**
     * Exécute la requête Informix exacte (avec agr_usr pour le créateur).
     * Utilise le modèle existant pour la connexion ODBC.
     */
    private function executerRequeteOrInformix(DitOrSoumisAValidationModel $model, string $numOr, string $codeSociete): array
    {
        // On appelle recupOrSoumisValidation qui contient déjà la même requête (sans ausr_nom)
        // puis on mappe les colonnes vers les noms attendus.
        $raw = $model->recupOrSoumisValidation($numOr, $codeSociete);

        // Si la méthode existante est suffisante, on retourne directement
        if (!empty($raw)) {
            return array_map(function (array $row) use ($numOr): array {
                return [
                    'slor_numor'           => $row['slor_numor']           ?? $numOr,
                    'numero_dit'           => $row['numero_dit']           ?? ($row['NUMERo_DIT'] ?? null),
                    'numero_itv'           => $row['numero_itv']           ?? ($row['NUMERO_ITV'] ?? 0),
                    'libelle_itv'          => $row['libelle_itv']          ?? ($row['LIBELLE_ITV'] ?? null),
                    'nombre_ligne'         => $row['nombre_ligne']         ?? ($row['NOMBRE_LIGNE'] ?? 0),
                    'montant_itv'          => $row['montant_itv']          ?? ($row['MONTANT_ITV'] ?? 0.00),
                    'montant_piece'        => $row['montant_piece']        ?? ($row['MONTANT_PIECE'] ?? 0.00),
                    'montant_mo'           => $row['montant_mo']           ?? ($row['MONTANT_MO'] ?? 0.00),
                    'montant_achats_locaux'=> $row['montant_achats_locaux'] ?? ($row['MONTANT_ACHATS_LOCAUX'] ?? 0.00),
                    'montant_divers'       => $row['montant_divers']       ?? ($row['MONTANT_DIVERS'] ?? 0.00),
                    'montant_lubrifiants'  => $row['montant_lubrifiants']  ?? ($row['MONTANT_LUBRIFIANTS'] ?? 0.00),
                    'createur_or'          => $row['createur_or']          ?? ($row['CREATEUR_OR'] ?? null),
                ];
            }, $raw);
        }

        return [];
    }
}
