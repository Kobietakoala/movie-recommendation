<?php

declare(strict_types=1);

namespace App\Traits;

trait ErrorMessagesTrait
{
    /**
     * @param string $key
     * @return string
     */
    protected function getErrorMessage(string $key): string
    {
        return match ($key) {
            'movies_not_found' => 'Plik data/movies.php nie został znaleziony',
            'invalid_movies_format' => 'Nie znaleziono tablicy $movies w pliku movies.php',
            'non_positive_movie_count' => 'Liczba filmów do pobrania musi być większa niż 0',
            'internal' => 'Wystąpił nieoczekiwany błąd.',
            'missing_param' => 'Brak wymaganego parametru: %param%',
            default => 'Nieznany błąd'
        };
    }
}