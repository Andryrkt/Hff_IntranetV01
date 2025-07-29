<?php

namespace App\Tests\Service\migration;

use App\Model\dit\DitModel;
use App\Repository\dit\DitRepository;
use App\Service\FusionPdf;
use App\Service\genererPdf\GenererPdfDit;
use App\Service\migration\MigrationPdfDitService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use App\Entity\dit\DemandeIntervention;

class MigrationPdfDitServiceTest extends TestCase
{
    private $ditRepositoryMock;
    private $ditModelMock;
    private $fusionPdfMock;
    private $genererPdfDitMock;
    private $loggerMock;
    private $migrationPdfDitService;

    protected static $tempLogDir;
    protected static $tempMigrationDir;
    protected static $tempUploadDir;

    public static function setUpBeforeClass(): void
    {
        self::$tempLogDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hff_intranet_test_logs';
        if (!is_dir(self::$tempLogDir)) {
            mkdir(self::$tempLogDir, 0777, true);
        }
        // Create the 'log' subdirectory
        $logSubdir = self::$tempLogDir . DIRECTORY_SEPARATOR . 'log';
        if (!is_dir($logSubdir)) {
            mkdir($logSubdir, 0777, true);
        }
        $_ENV['BASE_PATH_LOG'] = self::$tempLogDir;

        self::$tempMigrationDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hff_intranet_test_migration';
        if (!is_dir(self::$tempMigrationDir)) {
            mkdir(self::$tempMigrationDir, 0777, true);
        }

        self::$tempUploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hff_intranet_test_upload';
        if (!is_dir(self::$tempUploadDir)) {
            mkdir(self::$tempUploadDir, 0777, true);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (is_dir(self::$tempLogDir)) {
            // Remove the directory and its contents
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::$tempLogDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }
            rmdir(self::$tempLogDir);
        }
        unset($_ENV['BASE_PATH_LOG']);

        if (is_dir(self::$tempMigrationDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::$tempMigrationDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }
            rmdir(self::$tempMigrationDir);
        }

        if (is_dir(self::$tempUploadDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::$tempUploadDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }
            rmdir(self::$tempUploadDir);
        }
    }

    protected function setUp(): void
    {
        $this->ditRepositoryMock = $this->createMock(DitRepository::class);
        $this->ditModelMock = $this->createMock(DitModel::class);
        $this->fusionPdfMock = $this->createMock(FusionPdf::class);
        $this->genererPdfDitMock = $this->createMock(GenererPdfDit::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        // Mock the EntityManagerInterface for the constructor of MigrationPdfDitService
        $entityManagerMock = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManagerMock->method('getRepository')->willReturn($this->ditRepositoryMock);

        $this->migrationPdfDitService = new MigrationPdfDitService($entityManagerMock, $this->loggerMock, $this->ditModelMock, self::$tempMigrationDir, self::$tempUploadDir);

        // Mock the static getGenerator method of the Controller class
        $urlGeneratorMock = $this->createMock(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::class);
        $urlGeneratorMock->method('generate')->willReturn('/mocked-url');

        // Use reflection to set the static property
        $reflectionClass = new \ReflectionClass(\App\Controller\Controller::class);
        $generatorProperty = $reflectionClass->getProperty('generator');
        $generatorProperty->setAccessible(true);
        $generatorProperty->setValue(null, $urlGeneratorMock);

        // Create dummy files for testing
        file_put_contents(self::$tempMigrationDir . DIRECTORY_SEPARATOR . 'existing_file.pdf', 'dummy pdf content');
        file_put_contents(self::$tempMigrationDir . DIRECTORY_SEPARATOR . 'duplicate_file.pdf', 'dummy pdf content');
        file_put_contents(self::$tempUploadDir . DIRECTORY_SEPARATOR . 'DIT_TEST_001_AG001.pdf', 'dummy main pdf content');
        file_put_contents(self::$tempUploadDir . DIRECTORY_SEPARATOR . 'DIT_TEST_002_AG002.pdf', 'dummy main pdf content');
    }

    protected function tearDown(): void
    {
        // Clean up dummy files after each test
        @unlink(self::$tempMigrationDir . DIRECTORY_SEPARATOR . 'existing_file.pdf');
        @unlink(self::$tempMigrationDir . DIRECTORY_SEPARATOR . 'duplicate_file.pdf');
        @unlink(self::$tempUploadDir . DIRECTORY_SEPARATOR . 'DIT_TEST_001_AG001.pdf');
        @unlink(self::$tempUploadDir . DIRECTORY_SEPARATOR . 'DIT_TEST_002_AG002.pdf');
    }

    public function testDummyFilesExist()
    {
        $existingFilePath = self::$tempMigrationDir . DIRECTORY_SEPARATOR . 'existing_file.pdf';
        $duplicateFilePath = self::$tempMigrationDir . DIRECTORY_SEPARATOR . 'duplicate_file.pdf';
        $mainPdfPath1 = self::$tempUploadDir . DIRECTORY_SEPARATOR . 'DIT_TEST_001_AG001.pdf';
        $mainPdfPath2 = self::$tempUploadDir . DIRECTORY_SEPARATOR . 'DIT_TEST_002_AG002.pdf';

        $this->assertFileExists($existingFilePath);
        $this->assertFileExists($duplicateFilePath);
        $this->assertFileExists($mainPdfPath1);
        $this->assertFileExists($mainPdfPath2);
    }

    public function testFusionPdfMigrationsHandlesMissingFiles()
    {
        $dit = new DemandeIntervention();
        $dit->setNumeroDemandeIntervention('DIT_TEST_001');
        $dit->setAgenceServiceEmetteur('AG-001');
        $dit->setPieceJoint01('missing_file.pdf');
        $dit->setPieceJoint02('existing_file.pdf');

        // Expect a warning for the missing file
        $this->loggerMock->expects($this->once())
                         ->method('warning')
                         ->with($this->stringContains('Fichier de pièce jointe manquant'));

        // Expect mergePdfs to be called with only the existing file
        $this->fusionPdfMock->expects($this->once())
                         ->method('mergePdfs')
                         ->with($this->callback(function($files) {
                             $this->assertCount(2, $files); // mainPdf + existing_file.pdf
                             $this->assertStringContainsString('existing_file.pdf', $files[1]);
                             return true;
                         }));

        // Call the private method using reflection
        $reflection = new \ReflectionClass(MigrationPdfDitService::class);
        $method = $reflection->getMethod('fusionPdfmigrations');
        $method->setAccessible(true);

        $method->invoke($this->migrationPdfDitService, $dit, $this->fusionPdfMock);
    }

    public function testFusionPdfMigrationsAvoidsDuplicates()
    {
        $dit = new DemandeIntervention();
        $dit->setNumeroDemandeIntervention('DIT_TEST_002');
        $dit->setAgenceServiceEmetteur('AG-002');
        $dit->setPieceJoint01('duplicate_file.pdf');
        $dit->setPieceJoint02('duplicate_file.pdf');

        // Expect mergePdfs to be called with only one instance of the duplicate file
        $this->fusionPdfMock->expects($this->once())
                         ->method('mergePdfs')
                         ->with($this->callback(function($files) {
                             $this->assertCount(2, $files); // mainPdf + one duplicate
                             $this->assertStringContainsString('duplicate_file.pdf', $files[1]);
                             return true;
                         }));

        // Call the private method using reflection
        $reflection = new \ReflectionClass(MigrationPdfDitService::class);
        $method = $reflection->getMethod('fusionPdfmigrations');
        $method->setAccessible(true);

        $method->invoke($this->migrationPdfDitService, $dit, $this->fusionPdfMock);
    }
}