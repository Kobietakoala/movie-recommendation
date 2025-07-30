<?php

declare(strict_types=1);

namespace App\Service;

use App\Traits\ErrorMessagesTrait;

class RecommendationService
{
    use ErrorMessagesTrait;
    private array $movies = [];
    private bool $moviesLoaded = false;
    protected const string MOVIES_FILE_PATH = __DIR__ . '/../../data/movies.php';

    /**
     * @return void
     */
    private function loadMovies(): void
    {
        if ($this->moviesLoaded) {
            return;
        }

        if (!file_exists(self::MOVIES_FILE_PATH)) {
            throw new \RuntimeException($this->getErrorMessage('movies_not_found'));
        }

        include self::MOVIES_FILE_PATH;
        
        if (!isset($movies) || !is_array($movies)) {
            throw new \RuntimeException($this->getErrorMessage('invalid_movies_format'));
        }

        $this->movies = array_unique($movies);
        $this->moviesLoaded = true;
    }

    /**
     * @param int $count
     * @return array
     */
    public function getRandomMovies(int $count = 3): array
    {
        $this->loadMovies();

        if (empty($this->movies)) {
            return [];
        }

        if ($count >= count($this->movies)) {
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

        return $randomMovies;
    }

    /**
     * @return array
     */
    public function getMoviesWithWEvenLength(): array
    {
        $this->loadMovies();

        if (empty($this->movies)) {
            return [];
        }

        return array_filter($this->movies, function($movie) {
            if (empty($movie) || !is_string($movie)) {
                return false;
            }

            if (strlen($movie) === 0) {
                return false;
            }

            $startsWithW = strtolower($movie[0]) === 'w';
            $hasEvenLength = strlen($movie) % 2 === 0;

            return $startsWithW && $hasEvenLength;
        });
    }

    /**
     * @return array
     */
    public function getMultiWordMovies(): array
    {
        $this->loadMovies();

        if (empty($this->movies)) {
            return [];
        }

        return array_filter($this->movies, function($movie) {
            if (!is_string($movie) || trim($movie) === '') {
                return false;
            }

            $trimmedMovie = trim($movie);

            if (!str_contains($trimmedMovie, ' ')) {
                return false;
            }

            $wordCount = str_word_count($trimmedMovie);

            return $wordCount > 1;
        });
    }

}