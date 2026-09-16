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

            $sql = "SELECT 
nent.nent_datecde                                           AS date_cde_brute
                ,CASE 
                    WHEN dneg.statut_dw = '' OR dneg.statut_dw IS NULL THEN 'A traiter'
                    ELSE dneg.statut_dw
                END                                                         AS statut_dw
                ,dneg.statut_bc                                             AS statut_bc
                ,nent.nent_numcde                                           AS numero_devis
                ,(select TO_CHAR(date_creation, '%d/%m/%Y') from ir_prod108:Informix.devis_soumis_a_validation_neg d 
                where d.numero_devis = nent.nent_numcde 
                AND d.numero_version = (SELECT MIN(numero_version) 
                							FROM ir_prod108:Informix.devis_soumis_a_validation_neg 
                							WHERE numero_devis = nent.nent_numcde
                						)
                )															as date_premiere_soumission_devis
                ,TO_CHAR(nent.nent_datecde, '%d/%m/%Y')                     AS date_creation
                ,nent.nent_succ || ' - ' || nent.nent_servcrt                    AS emetteur
                ,nent.nent_numcli || ' - ' || nent.nent_nomcli                   AS client
                ,TRIM(nent.nent_refcde)                                     AS reference_client -- Libellé
                ,nent.nent_cdeht                                            AS montant_devis
                ,nent.nent_devise                                           AS devise
                ,TO_CHAR(dneg.date_envoye_devis_client, '%d/%m/%Y')         AS date_envoye_devis_au_client
                --,dneg.stop_progression_global                               AS stop_progression_global
                --,dneg.motif_stop_global                                     AS motif_stop_global

                -- Pour statut_relance_1
                ,CASE
                    WHEN rl.date_relance1 IS NOT NULL
                        THEN TO_CHAR(rl.date_relance1, '%d/%m/%Y')
                    WHEN dneg.statut_bc = 'En attente bc'
                        AND NVL(rl.nb_relances, 0) = 0
                        AND dneg.date_envoye_devis_client IS NOT NULL
                        AND (TODAY - DATE(dneg.date_envoye_devis_client)) >= 7
                        AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                        THEN 'A relancer'
                    ELSE NULL
                END AS statut_relance_1
                -- Pour statut_relance_2
                ,CASE
                    WHEN rl.date_relance2 IS NOT NULL
                        THEN TO_CHAR(rl.date_relance2, '%d/%m/%Y')
                    WHEN dneg.statut_bc = 'En attente bc'
                        AND rl.nb_relances = 1
                        AND rl.delai_jours >= 7
                        AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                        THEN 'A relancer'
                    ELSE NULL
                END AS statut_relance_2

                -- Pour statut_relance_3
                ,CASE
                    WHEN rl.date_relance3 IS NOT NULL
                        THEN TO_CHAR(rl.date_relance3, '%d/%m/%Y')
                    WHEN dneg.statut_bc = 'En attente bc'
                        AND rl.nb_relances = 2
                        AND rl.delai_jours >= 7
                        AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                        THEN 'A relancer'
                    ELSE NULL
                END AS statut_relance_3
                , case when dneg.stop_progression_global = 0 then 'non' else 'oui' end as stop_relance
                ,nent.nent_posl                                             AS position_ips -- position devis IPS
                ,b.numero_bc 												as numero_po --PO/BC client
                ,b.date_creation 											as date_soumission_po --Date soumission PO
                ,TRIM(ausr.ausr_nom)                                        AS utilisateur_createur_devis -- crée par
                ,dneg.utilisateur                                           AS soumis_par
                ,SUBSTR(nent.nent_libcde,7,INSTR(SUBSTR(nent.nent_libcde, 7), ' ') - 1) AS numero_bc_negoce --numéro BC négoce
               ,TO_CHAR(nent.nent_delai, '%d/%m/%Y') 							AS delai -- délai 
               ,nent.nent_posf 												as position_bc_negoce --Position bc négoce IPS
                -- Nbre Pièce BC négoce
               ,(select COUNT(*) AS nb_ligne 
    FROM ips_hffprod:Informix.neg_lig nl WHERE nl.nlig_codg = 'ST'
      AND nl.nlig_soc = 'HF'
      and nl.nlig_succ = '40'
      and nl.nlig_numcde = SUBSTR(nent.nent_libcde,7,INSTR(SUBSTR(nent.nent_libcde, 7), ' ') - 1)
      ) 																	as nbre_piece_bc_negoce
                ,'' AS constructeur

            FROM ips_hffprod:informix.neg_ent nent

            LEFT JOIN ips_hffprod:informix.agr_usr ausr
                ON ausr.ausr_num = nent.nent_usr
                AND ausr.ausr_soc = nent.nent_soc

            LEFT JOIN ir_prod108:Informix.devis_soumis_a_validation_neg dneg
                ON dneg.numero_devis = nent.nent_numcde
                AND dneg.numero_version = (SELECT MAX(numero_version) FROM ir_prod108:Informix.devis_soumis_a_validation_neg WHERE numero_devis = nent.nent_numcde) 

            left join ir_prod108:informix.bc_client_soumis_neg b
            	on b.numero_devis = nent.nent_numcde
            	and b.numero_version = (select MAX(bb.numero_version) from ir_prod108:informix.bc_client_soumis_neg bb where bb.numero_devis = nent.nent_numcde)
            	
            LEFT JOIN (
                SELECT
                    numero_devis as num_dev
                    ,MAX(CASE WHEN numero_relance = 1 THEN date_de_relance ELSE NULL END) AS date_relance1
                    ,MAX(CASE WHEN numero_relance = 2 THEN date_de_relance ELSE NULL END) AS date_relance2
                    ,MAX(CASE WHEN numero_relance = 3 THEN date_de_relance ELSE NULL END) AS date_relance3
                    ,COUNT(*) AS nb_relances
                    ,MAX(date_de_relance) AS derniere_relance
                    ,(TODAY - DATE(MAX(date_de_relance))) AS delai_jours
                FROM ir_prod108:Informix.pointage_relance
                GROUP BY 1
            ) rl ON rl.num_dev = nent.nent_numcde

            WHERE nent.nent_natop    = 'DEV'
                AND nent.nent_servcrt  <> 'ASS'
                AND nent.nent_numcli   NOT BETWEEN 1990000 AND 1999999
                AND nent.nent_numcli   <> 1990000
                AND nent.nent_numcde   NOT IN (19407989,19407991,19408971,19410383,19409906,19409996)
                AND (nent_datecde >= MDY(9, 1, 2025) OR nent_numcde IN ('54207000', '54206997'))
                AND nent.nent_succ <> '60'
                AND nent.nent_soc = 'HF'
                AND EXISTS (
                                SELECT 1 FROM ips_hffprod:informix.neg_lig nl
                                WHERE nl.nlig_numcde = nent.nent_numcde
                                AND nl.nlig_constp IN ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO') 
                            )
                AND NOT (dneg.statut_dw = 'A traiter'
                AND nent.nent_posl = 'TR')
       			AND  nent.nent_posl in ('--','AC','DE', 'TR') 
       			AND  nent.nent_succ = '40'  
       		ORDER BY date_cde_brute DESC";

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
                    $cde = trim((string)($row['numero_bc_negoce'] ?? ''));
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
                'Statut DW',
                'Statut BC',
                'Numéro devis',
                'Date 1ère soumission devis',
                'Date création',
                'Emetteur',
                'Client',
                'Référence client',
                'Montant devis',
                'Devise',
                'Date envoyé devis au client',
                'Statut relance 1',
                'Statut relance 2',
                'Statut relance 3',
                'Stop relance',
                'Position IPS',
                'Numéro PO',
                'Date soumission PO',
                'Utilisateur créateur devis',
                'Soumis par',
                'Numéro BC négoce',
                'Délai',
                'Position BC négoce IPS',
                'Nbre pièce BC négoce',
                'Constructeur',
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
                $numBcNegoce = trim((string)($row['numero_bc_negoce'] ?? ''));

                $constList = [];
                if ($numDev !== '' && isset($constructeursMap[$numDev])) {
                    $constList = array_merge($constList, $constructeursMap[$numDev]);
                }
                if ($numBcNegoce !== '' && isset($constructeursMap[$numBcNegoce])) {
                    $constList = array_merge($constList, $constructeursMap[$numBcNegoce]);
                }
                $constList = array_unique(array_filter($constList));
                $constructeurs = implode(', ', $constList);

                $excelData[] = [
                    $row['statut_dw'] ?? '',
                    $row['statut_bc'] ?? '',
                    $numDev,
                    $formatDate($row['date_premiere_soumission_devis'] ?? ''),
                    $formatDate($row['date_creation'] ?? ''),
                    $row['emetteur'] ?? '',
                    $row['client'] ?? '',
                    $row['reference_client'] ?? '',
                    $row['montant_devis'] ?? '',
                    $row['devise'] ?? '',
                    $formatDate($row['date_envoye_devis_au_client'] ?? ''),
                    $row['statut_relance_1'] ?? '',
                    $row['statut_relance_2'] ?? '',
                    $row['statut_relance_3'] ?? '',
                    $row['stop_relance'] ?? '',
                    $row['position_ips'] ?? '',
                    $row['numero_po'] ?? '',
                    $formatDate($row['date_soumission_po'] ?? ''),
                    $row['utilisateur_createur_devis'] ?? '',
                    $row['soumis_par'] ?? '',
                    $numBcNegoce,
                    $row['delai'] ?? '',
                    $row['position_bc_negoce'] ?? '',
                    $row['nbre_piece_bc_negoce'] ?? '',
                    $constructeurs,
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
