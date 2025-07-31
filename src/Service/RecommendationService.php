<?php

declare(strict_types=1);

namespace App\Service;

use App\Traits\ErrorMessagesTrait;
use InvalidArgumentException;
use Monolog\Logger;
use RuntimeException;
use Psr\Log\LoggerInterface;

class RecommendationService
{
    use ErrorMessagesTrait;
    private array $movies = [];
    private bool $moviesLoaded = false;
    protected const string MOVIES_FILE_PATH = __DIR__ . '/../../data/movies.php';

    public function __construct(private readonly LoggerInterface $logger) { }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function loadMovies(): void
    {
        if ($this->moviesLoaded) {
            $this->logger->debug('Movies already loaded, skipping reload');
            return;
        }

        $this->logger->info('Starting to load movies from file', ['file_path' => self::MOVIES_FILE_PATH]);

        if (!file_exists(self::MOVIES_FILE_PATH)) {
            $errorMessage = $this->getErrorMessage('movies_not_found');
            $this->logger->error('Movies file not found', [
                'file_path' => self::MOVIES_FILE_PATH,
                'error_message' => $errorMessage
            ]);
            throw new \RuntimeException($errorMessage);
        }

        include self::MOVIES_FILE_PATH;
        
        if (!isset($movies) || !is_array($movies)) {
            $errorMessage = $this->getErrorMessage('invalid_movies_format');
            $this->logger->error('Invalid movies format in file', [
                'file_path' => self::MOVIES_FILE_PATH,
                'error_message' => $errorMessage
            ]);
            throw new \RuntimeException($errorMessage);
        }

        $originalCount = count($movies);

        $sanitizedMovies = $this->sanitizeMovies($movies);
        $sanitizedCount = count($sanitizedMovies);
        $this->movies = array_unique($sanitizedMovies);
        $uniqueCount = count($this->movies);
        
        $this->logger->info('Movies loaded successfully', [
            'original_count' => $originalCount,
            'sanitized_count' => $sanitizedCount,
            'unique_count' => $uniqueCount,
            'invalid_entries_removed' => $originalCount - $sanitizedCount,
            'duplicates_removed' => $sanitizedCount - $uniqueCount
        ]);
      
        $this->moviesLoaded = true;
    }

    private function isValidMovie($movie): bool
    {
        return is_string($movie) && trim($movie) !== '';
    }

    private function sanitizeMovies(array $movies): array
    {
        $validMovies = [];
        $invalidCount = 0;

        foreach ($movies as $movie) {
            if ($this->isValidMovie($movie)) {
                $trimmedMovie = trim($movie);
                $validMovies[] = $trimmedMovie;
            } else {
                $invalidCount++;
                if ($this->logger instanceof Logger && $this->logger->isHandling(Logger::DEBUG)) {
                    $this->logger->debug('Skipping invalid movie entry during load', [
                        'movie' => $movie,
                        'type' => gettype($movie)
                    ]);
                }
            }
        }

        if ($invalidCount > 0) {
            $this->logger->warning('Found invalid movie entries during load', [
                'invalid_count' => $invalidCount,
                'valid_count' => count($validMovies)
            ]);
        }

        return $validMovies;
    }

    /**
     * @param int $count
     * @return array
     * @throws InvalidArgumentException
     */
    public function getRandomMovies(int $count = 3): array
    {
        $this->logger->info('Getting random movies', ['requested_count' => $count]);
        
        $this->loadMovies();

        if (empty($this->movies)) {
            $this->logger->warning('No movies available for random selection');
            return [];
        }

        if ($count <= 0) {
            throw new InvalidArgumentException($this->getErrorMessage('non_positive_movie_count'));
        }

        if ($count >= count($this->movies)) {
            $this->logger->info('Requested count exceeds available movies, returning all movies', [
                'requested_count' => $count,
                'available_count' => count($this->movies)
            ]);
            return $this->movies;
        }

        $randomKeys = array_rand($this->movies, $count);

        if (!is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }

        $randomMovies = [];
        foreach ($randomKeys as $key) {
            $randomMovies[] = $this->movies[$key];
        }

        $this->logger->info('Random movies selected successfully', [
            'returned_count' => count($randomMovies),
            'movies' => $randomMovies
        ]);

        return $randomMovies;
    }

    /**
     * @return array
     */
    public function getMoviesWithWEvenLength(): array
    {
        $this->logger->info('Getting movies starting with W and having even length');
        
        $this->loadMovies();

        if (empty($this->movies)) {
            $this->logger->warning('No movies available for W even length filter');
            return [];
        }

        $filteredMovies = array_filter($this->movies, function($movie) {
            if (empty($movie) || !is_string($movie)) {
                if ($this->logger instanceof Logger && $this->logger->isHandling(Logger::DEBUG)) {
                    $this->logger->debug('Skipping invalid movie entry', ['movie' => $movie]);
                }
                return false;
            }

            if (strlen($movie) === 0) {
                $this->logger->debug('Skipping empty movie title');
                return false;
            }

            $startsWithW = strtolower($movie[0]) === 'w';
            $hasEvenLength = mb_strlen($movie, 'UTF-8') % 2 === 0;
            $matches = $startsWithW && $hasEvenLength;

            if ($matches) {
                $this->logger->debug('Movie matches W even length criteria', [
                    'movie' => $movie,
                    'length' => strlen($movie)
                ]);
            }

            return $matches;
        });

        $this->logger->info('W even length movies filtered successfully', [
            'total_movies' => count($this->movies),
            'filtered_count' => count($filteredMovies)
        ]);

        return $filteredMovies;
    }

    /**
     * @return array
     */
    public function getMultiWordMovies(): array
    {
        $this->logger->info('Getting multi-word movies');
        
        $this->loadMovies();

        if (empty($this->movies)) {
            $this->logger->warning('No movies available for multi-word filter');
            return [];
        }

        $filteredMovies = array_filter($this->movies, function($movie) {
            if (!is_string($movie) || trim($movie) === '') {
                if ($this->logger instanceof Logger && $this->logger->isHandling(Logger::DEBUG)) {
                    $this->logger->debug('Skipping invalid movie entry', ['movie' => $movie]);
                }
                return false;
            }

            $trimmedMovie = trim($movie);

            if (!str_contains($trimmedMovie, ' ')) {
                $this->logger->debug('Movie is single word, skipping', ['movie' => $trimmedMovie]);
                return false;
            }

            $wordCount = str_word_count($trimmedMovie);
            $isMultiWord = $wordCount > 1;

            if ($isMultiWord) {
                $this->logger->debug('Movie is multi-word', [
                    'movie' => $trimmedMovie,
                    'word_count' => $wordCount
                ]);
            }

            return $isMultiWord;
        });

        $this->logger->info('Multi-word movies filtered successfully', [
            'total_movies' => count($this->movies),
            'filtered_count' => count($filteredMovies)
        ]);

        return $filteredMovies;
    }
}
