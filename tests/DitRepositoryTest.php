<?php

namespace App\Tests;

use App\Repository\dit\DitRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DitRepositoryTest extends TestCase
{
    private $entityManager;
    private $ditRepository;

    protected function setUp(): void
    {
        // Mock the EntityManagerInterface
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock the DitRepository and its methods
        $this->ditRepository = $this->getMockBuilder(DitRepository::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        // Configure the mock to return an empty array for findDitMigration
        $this->ditRepository->method('findDitMigration')->willReturn([]);
    }

    public function testFindDitMigrationReturnsArray()
    {
        $result = $this->ditRepository->findDitMigration();
        $this->assertIsArray($result);
    }
}