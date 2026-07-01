<?php

namespace App\Command\dit;

use DateTime;
use App\Model\dit\DitDevisSoumisAValidationModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour insérer les données d'un devis depuis Informix (IPS)
 * dans la table SQL Server `devis_soumis_a_validation`.
 *
 * Utilisation :
 *   php bin/console app:insert-devis-soumis-validation <numDevis> [<codeSociete>]
 *
 * Exemple :
 *   php bin/console app:insert-devis-soumis-validation 41019586
 *   php bin/console app:insert-devis-soumis-validation 41019586 HF
 */
class InsertDevisSoumisAValidationCommand extends Command
{
    protected static $defaultName = 'app:insert-devis-soumis-validation';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Insère les données d\'un devis Informix (IPS) dans la table devis_soumis_a_validation (SQL Server).')
            ->setHelp(
                "Cette commande récupère les données du devis depuis Informix (IPS)\n"
                    . "et les insère dans la table HFF_INTRANET.dbo.devis_soumis_a_validation.\n\n"
                    . "Utilisation :\n"
                    . "  php bin/console app:insert-devis-soumis-validation <numDevis> [<codeSociete>]\n\n"
                    . "Exemple :\n"
                    . "  php bin/console app:insert-devis-soumis-validation 41019586\n"
                    . "  php bin/console app:insert-devis-soumis-validation 41019586 HF\n"
            )
            ->addArgument('numDevis', InputArgument::REQUIRED, 'Numéro du devis à insérer (ex: 41019586)')
            ->addArgument('codeSociete', InputArgument::OPTIONAL, 'Code société (défaut : HF)', 'HF')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $numDevis    = $input->getArgument('numDevis');
        $codeSociete = $input->getArgument('codeSociete');

        $io->title("Insertion du devis N° $numDevis (société : $codeSociete)");

        // ── 1. Récupération des données depuis Informix ──────────────────────
        $io->section('Récupération des données depuis Informix (IPS)…');

        $model = new DitDevisSoumisAValidationModel();
        $lignes = $model->recupDevisSoumisValidation($numDevis, $codeSociete);

        if (empty($lignes)) {
            $io->error("Aucune donnée trouvée dans Informix pour le devis $numDevis (société : $codeSociete).");
            return Command::FAILURE;
        }

        $io->success(count($lignes) . ' ligne(s) trouvée(s) dans Informix.');

        // ── 2. Calcul du prochain numéro de version ──────────────────────────
        $io->section('Calcul du numéro de version…');

        $conn = $this->entityManager->getConnection();

        $versionMax = $conn->fetchOne(
            'SELECT MAX(numeroVersion) FROM devis_soumis_a_validation WHERE numeroDevis = ? AND code_societe = ?',
            [$numDevis, $codeSociete]
        );

        $prochainVersion = ($versionMax === null || $versionMax === false) ? 1 : (int)$versionMax + 1;

        $io->writeln("Numéro de version : <info>$prochainVersion</info>");

        // ── 3. Insertion dans SQL Server ─────────────────────────────────────
        $io->section('Insertion en base de données SQL Server…');

        $dateHeureSoumission = (new DateTime())->format('Y-m-d H:i:s');
        $progressBar = new ProgressBar($output, count($lignes));
        $progressBar->start();

        $nbInseres  = 0;
        $nbErreurs  = 0;

        foreach ($lignes as $ligne) {
            try {
                // Calcul de la marge revient
                $montantRevient = $ligne['montant_revient'] ?? null;
                $montantItv     = $ligne['montant_itv']     ?? null;
                $margeRevient   = null;

                if ($montantRevient !== null && $montantItv !== null && (float)$montantRevient != 0.0) {
                    $margeRevient = round(((float)$montantItv - (float)$montantRevient) / (float)$montantRevient * 100, 2);
                }

                $conn->insert('devis_soumis_a_validation', [
                    'numeroDit'            => $ligne['numero_dit']          ?? null,
                    'numeroDevis'          => $ligne['numero_devis']        ?? $numDevis,
                    'numeroItv'            => $ligne['numero_itv']          ?? null,
                    'nombreLigneItv'       => $ligne['nombre_ligne']        ?? null,
                    'montantItv'           => $montantItv                   ?? 0.00,
                    'numeroVersion'        => $prochainVersion,
                    'montantPiece'         => $ligne['montant_piece']       ?? 0.00,
                    'montantMo'            => $ligne['montant_mo']          ?? 0.00,
                    'montantAchatLocaux'   => $ligne['montant_achats_locaux'] ?? 0.00,
                    'montantFraisDivers'   => $ligne['montant_divers']      ?? 0.00,
                    'montantLubrifiants'   => $ligne['montant_lubrifiants'] ?? 0.00,
                    'libellelItv'          => $ligne['libelle_itv']         ?? null,
                    'statut'               => 'A valider atelier',
                    'dateHeureSoumission'  => $dateHeureSoumission,
                    'montantForfait'       => $ligne['montant_forfait']     ?? 0.00,
                    'natureOperation'      => $ligne['nature_operation']    ?? null,
                    'devisVenteOuForfait'  => 'DEVIS VENTE',
                    'devise'               => $ligne['devise']              ?? null,
                    'montantVente'         => $ligne['montant_vente']       ?? 0.00,
                    'num_migr'             => null,
                    'montantRevient'       => $montantRevient               ?? 0.00,
                    'margeRevient'         => $margeRevient,
                    'type'                 => 'VA',
                    'nombreLignePiece'     => 0,
                    'tache_validateur'     => null,
                    'observation'          => null,
                    'code_societe'         => $codeSociete,
                ]);

                $nbInseres++;
            } catch (\Exception $e) {
                $nbErreurs++;
                $io->newLine();
                $io->warning(
                    "Erreur lors de l'insertion de l'ITV {$ligne['numero_itv']} : " . $e->getMessage()
                );
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);

        // ── 4. Résumé ────────────────────────────────────────────────────────
        if ($nbErreurs === 0) {
            $io->success("$nbInseres ligne(s) insérée(s) avec succès pour le devis $numDevis (version $prochainVersion).");
        } else {
            $io->warning("$nbInseres ligne(s) insérée(s), $nbErreurs erreur(s) rencontrée(s).");
        }

        // ── 5. Affichage du récapitulatif ────────────────────────────────────
        $this->afficherRecapitulatif($io, $lignes, $numDevis, $prochainVersion);

        return $nbErreurs === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Affiche un tableau récapitulatif des données insérées.
     */
    private function afficherRecapitulatif(SymfonyStyle $io, array $lignes, string $numDevis, int $version): void
    {
        $io->section("Récapitulatif — Devis N° $numDevis — Version $version");

        $rows = [];
        foreach ($lignes as $ligne) {
            $rows[] = [
                $ligne['numero_itv']          ?? '-',
                $ligne['libelle_itv']         ?? '-',
                $ligne['nombre_ligne']         ?? '-',
                number_format((float)($ligne['montant_itv']     ?? 0), 2, '.', ' '),
                number_format((float)($ligne['montant_piece']   ?? 0), 2, '.', ' '),
                number_format((float)($ligne['montant_mo']      ?? 0), 2, '.', ' '),
                number_format((float)($ligne['montant_revient'] ?? 0), 2, '.', ' '),
                $ligne['devise']              ?? '-',
            ];
        }

        $io->table(
            ['N° ITV', 'Libellé ITV', 'Nb lignes', 'Mtt ITV', 'Mtt Pièce', 'Mtt MO', 'Mtt Revient', 'Devise'],
            $rows
        );
    }
}
