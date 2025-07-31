<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\RecommendationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use InvalidArgumentException;

class RecommendationServiceTest extends TestCase
{
    private RecommendationService $recommendationService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->recommendationService = new RecommendationService($this->logger);
    }

    public function testGetRandomMoviesReturnsThreeRandomTitles(): void
    {
        $expectedCount = 3;

        $result = $this->recommendationService->getRandomMovies($expectedCount);

        $this->assertIsArray($result);
        $this->assertCount($expectedCount, $result);

        foreach ($result as $movie) {
            $this->assertIsString($movie);
            $this->assertNotEmpty($movie);
        }

        $this->assertEquals($result, array_unique($result));
    }

    public function testGetRandomMoviesDefaultCountIsThree(): void
    {
        $result = $this->recommendationService->getRandomMovies();

        $this->assertCount(3, $result);
    }

    public function testGetRandomMoviesThrowsExceptionForNonPositiveCount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->recommendationService->getRandomMovies(0);
    }

    public function testGetMoviesWithWEvenLengthReturnsOnlyMoviesStartingWithWAndEvenLength(): void
    {
        $result = $this->recommendationService->getMoviesWithWEvenLength();

        $this->assertIsArray($result);
        
        foreach ($result as $movie) {
            $this->assertTrue(
                strtolower($movie[0]) === 'w',
                "Movie '{$movie}' does not start with the letter 'W'"
            );

            $this->assertEquals(
                0,
                mb_strlen($movie, 'UTF-8') % 2,
                "Movie '{$movie}' does not have an even number of characters (length: " . mb_strlen($movie, 'UTF-8') . ")"
            );
        }
    }

    public function testGetMoviesWithWEvenLengthWithKnownData(): void
    {
        $result = $this->recommendationService->getMoviesWithWEvenLength();

        $expectedMovies = ['Whiplash', 'Wyspa tajemnic', 'Władca Pierścieni: Drużyna Pierścienia'];
        
        foreach ($expectedMovies as $expectedMovie) {
            $this->assertContains(
                $expectedMovie, 
                $result,
                "Movie '{$expectedMovie}' should be in the results"
            );
        }
    }

    public function testGetMultiWordMoviesReturnsOnlyMultiWordTitles(): void
    {
        $result = $this->recommendationService->getMultiWordMovies();

        $this->assertIsArray($result);
        
        foreach ($result as $movie) {
            $wordCount = str_word_count(trim($movie));
            
            $this->assertGreaterThan(
                1,
                $wordCount,
                "Movie '{$movie}' does not consist of more than one word (word count: {$wordCount})"
            );

            $this->assertTrue(
                str_contains(trim($movie), ' '),
                "Movie '{$movie}' doesn't contain spaces, so probably is not multi-word"
            );
        }
    }

    public function testGetMultiWordMoviesExcludesSingleWordTitles(): void
    {
        $result = $this->recommendationService->getMultiWordMovies();

        $singleWordMovies = ['Matrix', 'Django', 'Incepcja', 'Gladiator', 'Pianista', 'Siedem'];
        
        foreach ($singleWordMovies as $singleWord) {
            $this->assertNotContains(
                $singleWord,
                $result,
                "Single word movie '{$singleWord}' should not be in results"
            );
        }
    }

    public function testGetMultiWordMoviesIncludesKnownMultiWordTitles(): void
    {
        $result = $this->recommendationService->getMultiWordMovies();

        $expectedMultiWordMovies = [
            'Pulp Fiction',
            'Skazani na Shawshank',
            'Dwunastu gniewnych ludzi',
            'Leon zawodowiec',
            'Fight Club',
            'Forrest Gump'
        ];
        
        foreach ($expectedMultiWordMovies as $multiWordMovie) {
            $this->assertContains(
                $multiWordMovie,
                $result,
                "Multi-word movie '{$multiWordMovie}' should be in the results"
            );
        }
    }
}
