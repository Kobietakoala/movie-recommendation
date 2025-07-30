<?php

declare(strict_types=1);

namespace App\Traits;

trait ErrorMessagesTrait
{
    /**
    * Retrieves a localized error message based on the provided key.
    *
    * @param string $key The error message key (e.g., 'movies_not_found', 'invalid_movies_format')
    * @return string The corresponding error message in Polish
    * */
    protected function getErrorMessage(string $key): string
    {
        return match ($key) {
            'movies_not_found' => 'File data/movies.php not found',
            'invalid_movies_format' => 'Could not find $movies array in movies.php file',
            'non_positive_movie_count' => 'Number of movies to retrieve must be greater than 0',
            'internal' => 'An unexpected error occurred.',
            default => 'Unknown error'
        };
    }
}