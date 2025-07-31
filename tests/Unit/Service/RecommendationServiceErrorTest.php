<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\RecommendationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use InvalidArgumentException;
use ReflectionClass;

class RecommendationServiceErrorTest extends TestCase
{
    private LoggerInterface $logger;
    private string $originalPath;
    private string $backupPath;
    private bool $fileWasBackedUp = false;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->originalPath = __DIR__ . '/../../../data/movies.php';
        $this->backupPath = __DIR__ . '/../../../data/movies_backup.php';
    }

    protected function tearDown(): void
    {
        $this->restoreMoviesFileFromBackup();
        parent::tearDown();
    }

    /**
     * Temporarily moves the original movies.php file to backup file
     */
    private function moveMoviesFileToBackup(): void
    {
        if (file_exists($this->originalPath) && !$this->fileWasBackedUp) {
            rename($this->originalPath, $this->backupPath);
            $this->fileWasBackedUp = true;
        }
    }

    /**
     * Restores the original movies.php file from backup
     */
    private function restoreMoviesFileFromBackup(): void
    {
        if ($this->fileWasBackedUp) {
            if (file_exists($this->originalPath)) {
                unlink($this->originalPath);
            }

            if (file_exists($this->backupPath)) {
                rename($this->backupPath, $this->originalPath);
            }

            $this->fileWasBackedUp = false;
        }
    }

    private function createMoviesFileWithContent(string $content): void
    {
        file_put_contents($this->originalPath, $content);
    }

    public function testLoadMoviesThrowsExceptionWhenFileNotExists(): void
    {
        $this->moveMoviesFileToBackup();
        $service = new RecommendationService($this->logger);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File data/movies.php not found');
        
        try {
            $service->getRandomMovies();
        } finally {
            $this->restoreMoviesFileFromBackup();
        }
    }

    public function testGetRandomMoviesThrowsExceptionForNegativeCount(): void
    {
        $service = new RecommendationService($this->logger);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of movies to retrieve must be greater than 0');
        $service->getRandomMovies(-1);
    }

    public function testGetRandomMoviesThrowsExceptionForZeroCount(): void
    {
        // Arrange
        $service = new RecommendationService($this->logger);

        // Assert & Act
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of movies to retrieve must be greater than 0');
        $service->getRandomMovies(0);
    }

    public function testLoggingWhenMovieFileNotFound(): void
    {
        $this->moveMoviesFileToBackup();

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Movies file not found',
                $this->arrayHasKey('file_path')
            );

        $service = new RecommendationService($this->logger);

        try {
            $service->getRandomMovies();
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('File data/movies.php not found', $e->getMessage());
        } finally {
            $this->restoreMoviesFileFromBackup();
        }
    }

    public function testServiceUsesErrorMessagesTrait(): void
    {
        $service = new RecommendationService($this->logger);
        $reflection = new ReflectionClass($service);

        $this->assertTrue(
            $reflection->hasMethod('getErrorMessage'),
            'RecommendationService should use ErrorMessagesTrait'
        );
    }

    public function testLoadMoviesThrowsExceptionForInvalidFileFormat(): void
    {
        $this->moveMoviesFileToBackup();
        $this->createMoviesFileWithContent('<?php $invalidData = "not an array";');

        $service = new RecommendationService($this->logger);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find $movies array in movies.php file');
        
        try {
            $service->getRandomMovies();
        } finally {
            $this->restoreMoviesFileFromBackup();
        }
    }

    public function testGetRandomMoviesReturnsAllWhenCountExceedsAvailable(): void
    {
        $service = new RecommendationService($this->logger);

        $result = $service->getRandomMovies(1000);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertLessThan(1000, count($result));
    }

    public function testGetRandomMoviesReturnsEmptyArrayWhenNoMoviesAvailable(): void
    {
        $this->moveMoviesFileToBackup();
        $this->createMoviesFileWithContent('<?php $movies = [];');

        $service = new RecommendationService($this->logger);

        $result = $service->getRandomMovies();

        $this->assertIsArray($result);
        $this->assertEmpty($result);

        $this->restoreMoviesFileFromBackup();
    }

    public function testSuccessfulLogging(): void
    {
        $this->logger->expects($this->atLeastOnce())
            ->method('info');

        $service = new RecommendationService($this->logger);

        $service->getRandomMovies();

        $this->assertTrue(true);
    }

    public function testWarningLoggingWhenNoMoviesAvailable(): void
    {
        $this->moveMoviesFileToBackup();
        $this->createMoviesFileWithContent('<?php $movies = [];');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('No movies available for random selection');

        $service = new RecommendationService($this->logger);

        $service->getRandomMovies();

        $this->restoreMoviesFileFromBackup();
    }

    public function testAllMethodsWithEmptyMoviesArray(): void
    {
        $this->moveMoviesFileToBackup();
        $this->createMoviesFileWithContent('<?php $movies = [];');

        $service = new RecommendationService($this->logger);

        $randomMovies = $service->getRandomMovies();
        $wEvenMovies = $service->getMoviesWithWEvenLength();
        $multiWordMovies = $service->getMultiWordMovies();

        $this->assertIsArray($randomMovies);
        $this->assertEmpty($randomMovies);
        $this->assertIsArray($wEvenMovies);
        $this->assertEmpty($wEvenMovies);
        $this->assertIsArray($multiWordMovies);
        $this->assertEmpty($multiWordMovies);

        $this->restoreMoviesFileFromBackup();
    }

    public function testMoviesArrayWithInvalidData(): void
    {
        $this->moveMoviesFileToBackup();
        $this->createMoviesFileWithContent('<?php $movies = ["valid movie", null, "", 123, []];');

        $service = new RecommendationService($this->logger);

        try {
            // Methods should filter out invalid data
            $randomMovies = $service->getRandomMovies();
            $wEvenMovies = $service->getMoviesWithWEvenLength();
            $multiWordMovies = $service->getMultiWordMovies();

            // Assert - check if invalid data was filtered out
            $this->assertIsArray($randomMovies);
            $this->assertIsArray($wEvenMovies);
            $this->assertIsArray($multiWordMovies);

            // Check if only valid strings were preserved
            foreach ($randomMovies as $movie) {
                $this->assertIsString($movie);
                $this->assertNotEmpty($movie);
            }

        } finally {
            $this->restoreMoviesFileFromBackup();
        }
    }

    public function testCanReloadMoviesAfterError(): void
    {
        $this->moveMoviesFileToBackup();
        $service = new RecommendationService($this->logger);

        try {
            $service->getRandomMovies();
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
        }

        $this->restoreMoviesFileFromBackup();

        $result = $service->getRandomMovies();
        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
    }
}