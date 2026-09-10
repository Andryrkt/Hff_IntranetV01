<?php

namespace App\Command\magasin;

use App\Model\DatabaseInformix;
use App\Service\ExcelService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportDevisNegExcelCommand extends Command
{
    protected static $defaultName = 'app:export-devis-neg-excel';

    protected function configure(): void
    {
        $this
            ->setDescription('Exporte les devis négoce en fichier Excel selon la requête spécifiée. Ligne de commande : "php bin/console app:export-devis-neg-excel"')
            ->setHelp("Cette commande vous permet d'exporter les devis négoce en fichier Excel.\n\nExemples d'utilisation :\n  php bin/console app:export-devis-neg-excel\n  php bin/console app:export-devis-neg-excel --filename=export.xlsx\n  php bin/console app:export-devis-neg-excel --succ=40 --soc=HF")
            ->addOption('filename', 'f', InputOption::VALUE_OPTIONAL, 'Nom ou chemin du fichier Excel de sortie', null)
            ->addOption('succ', 's', InputOption::VALUE_OPTIONAL, 'Code Succursale (défaut: 40)', '40')
            ->addOption('soc', 'c', InputOption::VALUE_OPTIONAL, 'Code Société (défaut: HF)', 'HF');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Exportation des devis négoce vers Excel');

        $succ = $input->getOption('succ');
        $soc = $input->getOption('soc');
        $filenameOption = $input->getOption('filename');

        $dbIrium = $_ENV['DB_NAME_IRIUM'] ?? 'ir_prod108';
        $dbIps = $_ENV['DB_NAME_IPS'] ?? 'ips_hffprod';

        $db = new DatabaseInformix();

        try {
            $io->info('Connexion à la base de données Informix...');
            $db->connect();

            $sql = "WITH devis_extract AS (
    SELECT
        nent.*,
        SUBSTR(
            nent.nent_libcde,
            7,
            INSTR(SUBSTR(nent.nent_libcde, 7), ' ') - 1
        ) AS numero_devis
    FROM ips_hffprod:informix.neg_ent nent
    WHERE nent.nent_libcde <> ''
      AND nent.nent_soc = 'HF'
      AND nent.nent_succ = '40'
      AND nent.nent_natop = 'DIR'
      AND YEAR(nent.nent_datecde) = 2026
),

-- Dernière version pour chaque devis
last_version_devis AS (
    SELECT
        de.numero_devis,
        MAX(numero_version) AS max_version,
        MIN(numero_version) AS min_version
    FROM ir_prod108:informix.devis_soumis_a_validation_neg dsvn
    inner join devis_extract de
    on de.numero_devis = dsvn.numero_devis
    GROUP BY numero_devis
),

statut_devis AS (
    SELECT
        d.numero_devis,
        d.statut_dw,
        d.date_creation AS date_premiere_soumission,
        d.date_envoye_devis_client
    FROM ir_prod108:informix.devis_soumis_a_validation_neg d
    INNER JOIN last_version_devis lv 
        ON d.numero_devis = lv.numero_devis 
        AND d.numero_version = lv.max_version
),

-- Première soumission (version MIN)
first_submission AS (
    SELECT
        d.numero_devis,
        d.date_creation AS date_premiere_soumission
    FROM ir_prod108:informix.devis_soumis_a_validation_neg d
    INNER JOIN last_version_devis lv 
        ON d.numero_devis = lv.numero_devis 
        AND d.numero_version = lv.min_version
),

relances AS (
    SELECT
        numero_devis,
        MAX(CASE WHEN numero_relance = 1 THEN date_de_relance END) AS date_relance1,
        MAX(CASE WHEN numero_relance = 2 THEN date_de_relance END) AS date_relance2,
        MAX(CASE WHEN numero_relance = 3 THEN date_de_relance END) AS date_relance3,
        COUNT(*) AS nb_relances,
        MAX(date_de_relance) AS derniere_relance,
        (TODAY - DATE(MAX(date_de_relance))) AS delai_jours
    FROM ir_prod108:informix.pointage_relance
    GROUP BY numero_devis
),

-- Dernier BC pour chaque devis
last_bc AS (
    SELECT
        de.numero_devis,
        MAX(numero_version) AS max_version
    FROM ir_prod108:informix.bc_client_soumis_neg b
    inner join devis_extract de
    on de.numero_devis = b.numero_devis
    GROUP BY numero_devis
),

bc_info AS (
    SELECT
        b.numero_devis,
        b.date_creation AS date_soumission_po,
        b.numero_bc AS numero_po
    FROM ir_prod108:informix.bc_client_soumis_neg b
    INNER JOIN last_bc lb 
        ON b.numero_devis = lb.numero_devis 
        AND b.numero_version = lb.max_version
),

-- nombre de ligne
nb_ligne AS (
    SELECT 
        de.nent_numcde, 
        COUNT(*) AS nb_ligne 
    FROM ips_hffprod:Informix.neg_lig nl
    INNER JOIN devis_extract de
        ON de.nent_numcde = nl.nlig_numcde 
    WHERE nl.nlig_codg = 'ST'
      AND nl.nlig_soc = 'HF'
      and nl.nlig_succ = '40'
    GROUP BY de.nent_numcde
)

SELECT
    -- Devis
    d.numero_devis,
    d.nent_numcli || ' - ' || d.nent_nomcli AS client,
    
    -- Statut et dates de soumission
    s.statut_dw,
    TO_CHAR(fs.date_premiere_soumission, '%d/%m/%Y') as dare_premier_soumission,
    TO_CHAR(s.date_envoye_devis_client, '%d/%m/%Y') as date_envoye_devis_client,
    
    -- Dates de relance (formatées)
    TO_CHAR(r.date_relance1, '%d/%m/%Y') AS date_relance_1,
    TO_CHAR(r.date_relance2, '%d/%m/%Y') AS date_relance_2,
    TO_CHAR(r.date_relance3, '%d/%m/%Y') AS date_relance_3,
    
    -- Informations BC/PO
    TO_CHAR(b.date_soumission_po, '%d/%m/%Y') as date_soumission_po,
    b.numero_po,
    
    -- Champs calculés
     d.nent_numcde as numero_commande_negoce,
    '' AS constructeur,
    n.nb_ligne AS nombre_piece_devis,
    TO_CHAR(d.nent_delai, '%d/%m/%Y') AS delai,
     d.nent_posf  AS facture,
     d.nent_cdeht as montant

FROM devis_extract d
LEFT JOIN statut_devis s ON d.numero_devis = s.numero_devis
LEFT JOIN first_submission fs ON d.numero_devis = fs.numero_devis
LEFT JOIN relances r ON d.numero_devis = r.numero_devis
LEFT JOIN bc_info b ON d.numero_devis = b.numero_devis
LEFT join nb_ligne n on n.nent_numcde = d.nent_numcde

WHERE 1=1  -- Pour faciliter l'ajout de filtres supplémentaires
  -- Ajoute ici d'autres conditions si nécessaire

ORDER BY d.nent_numcde DESC";

            $io->info('Exécution de la requête SQL...');
            $result = $db->executeQuery($sql);
            $rows = $db->fetchResults($result);

            $count = count($rows);
            $io->success(sprintf('%d devis trouvé(s).', $count));

            // Récupération de tous les constructeurs distincts par devis
            $constructeursMap = [];
            if (!empty($rows)) {
                $cdeToDevisMap = [];
                $allNums = [];
                foreach ($rows as $row) {
                    $cde = trim((string)($row['numero_commande_negoce'] ?? ''));
                    $dev = trim((string)($row['numero_devis'] ?? ''));
                    if ($cde !== '') {
                        $allNums[] = $cde;
                    }
                    if ($dev !== '') {
                        $allNums[] = $dev;
                    }
                    if ($cde !== '') {
                        $cdeToDevisMap[$cde] = $row;
                    }
                    if ($dev !== '') {
                        $cdeToDevisMap[$dev] = $row;
                    }
                }

                $allNums = array_unique(array_filter($allNums));

                if (!empty($allNums)) {
                    $chunks = array_chunk($allNums, 500);
                    foreach ($chunks as $chunk) {
                        $quotedNums = implode(',', array_map(function ($num) {
                            return "'" . addslashes($num) . "'";
                        }, $chunk));

                        $sqlConst = "SELECT DISTINCT nlig_numcde, TRIM(nlig_constp) AS constp
                                     FROM {$dbIps}:informix.neg_lig
                                     WHERE nlig_numcde IN ($quotedNums)
                                       AND nlig_constp IS NOT NULL
                                       AND TRIM(nlig_constp) <> ''";

                        $resConst = $db->executeQuery($sqlConst);
                        $constRows = $db->fetchResults($resConst);

                        foreach ($constRows as $cRow) {
                            $cNum = trim((string)($cRow['nlig_numcde'] ?? ''));
                            $cVal = trim((string)($cRow['constp'] ?? ''));
                            if ($cNum !== '' && $cVal !== '') {
                                if (!isset($constructeursMap[$cNum])) {
                                    $constructeursMap[$cNum] = [];
                                }
                                if (!in_array($cVal, $constructeursMap[$cNum], true)) {
                                    $constructeursMap[$cNum][] = $cVal;
                                }

                                if (isset($cdeToDevisMap[$cNum]['numero_devis'])) {
                                    $devKey = trim((string)$cdeToDevisMap[$cNum]['numero_devis']);
                                    if ($devKey !== '' && $devKey !== $cNum) {
                                        if (!isset($constructeursMap[$devKey])) {
                                            $constructeursMap[$devKey] = [];
                                        }
                                        if (!in_array($cVal, $constructeursMap[$devKey], true)) {
                                            $constructeursMap[$devKey][] = $cVal;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Préparation des données pour Excel
            $excelData = [];
            $excelData[] = [
                'Numéro devis',
                'Client',
                'Date 1ère soumission',
                'Date envoyé devis au client',
                'Relance 1',
                'Relance 2',
                'Relance 3',
                'Date soumission PO',
                'Numéro PO',
                'Numéro commande négoce',
                'Constructeur',
                'Nombre pièce devis',
                'Délai',
                'Facturé',
                'Montant HT'
            ];

            $formatDate = function ($dateVal) {
                if (empty($dateVal)) {
                    return '';
                }
                $timestamp = strtotime($dateVal);
                return $timestamp ? date('d/m/Y', $timestamp) : $dateVal;
            };

            foreach ($rows as $row) {
                $numDev = trim((string)($row['numero_devis'] ?? ''));
                $numCde = trim((string)($row['numero_commande_negoce'] ?? ''));

                $constList = [];
                if ($numDev !== '' && isset($constructeursMap[$numDev])) {
                    $constList = array_merge($constList, $constructeursMap[$numDev]);
                }
                if ($numCde !== '' && isset($constructeursMap[$numCde])) {
                    $constList = array_merge($constList, $constructeursMap[$numCde]);
                }
                $constList = array_unique(array_filter($constList));
                $constructeurs = implode(', ', $constList);

                $excelData[] = [
                    $numDev,
                    $row['client'] ?? '',
                    $formatDate($row['date_premiere_soumission'] ?? ''),
                    $row['date_envoye_devis_au_client'] ?? '',
                    $row['date_relance_1'] ?? '',
                    $row['date_relance_2'] ?? '',
                    $row['date_relance_3'] ?? '',
                    $formatDate($row['date_soumission_po'] ?? ''),
                    $row['numero_po'] ?? '',
                    $numCde,
                    $constructeurs,
                    $row['nombre_piece_devis'] ?? '',
                    $row['delai'] ?? '',
                    $row['facture'] ?? '',
                    $row['montant'] ?? '',
                ];
            }

            // Définition du fichier de sortie
            if ($filenameOption) {
                $filePath = $filenameOption;
                if (substr(strtolower($filePath), -5) !== '.xlsx') {
                    $filePath .= '.xlsx';
                }
            } else {
                $filePath = 'export_devis_neg_' . date('Y-m-d_His') . '.xlsx';
            }

            $excelService = new ExcelService();
            $savedPath = $excelService->createSpreadsheetEnregistrer($excelData, $filePath);

            $fullPath = realpath($savedPath) ?: $savedPath;
            $io->success('Exportation réussie dans le fichier : ' . $fullPath);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'exportation : ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            $db->close();
        }
    }
}
