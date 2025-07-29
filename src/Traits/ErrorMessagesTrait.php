<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\RecommendationService;

trait ErrorMessagesTrait
{
    protected function getErrorMessage(string $key): string
    {
        return match ($key) {
            'movies_not_found' => 'Plik ' . RecommendationService::MOVIES_FILE_PATH . ' nie został znaleziony',
            'invalid_movies_format' => 'Nie znaleziono tablicy $movies w pliku movies.php',
            'internal' => 'Wystąpił nieoczekiwany błąd.',
            'missing_param' => 'Brak wymaganego parametru: %param%',
            default => 'Nieznany błąd'
        };
    }
}