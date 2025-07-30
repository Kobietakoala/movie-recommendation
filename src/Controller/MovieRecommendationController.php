<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RecommendationService;
use App\Utility\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MovieRecommendationController extends AbstractController
{
    public function __construct(
        private readonly RecommendationService $recommendationService
    ){}

    #[Route('/random-movies', name: 'random_movies', methods: ['GET'])]
    public function getRandomMovies(): JsonResponse
    {
        try {
            $movies = $this->recommendationService->getRandomMovies();
            return ApiResponse::success($movies);
        }catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
    #[Route('/movies-with-w-even-length', name: 'movies_with_w_even_length', methods: ['GET'])]
    public function getMoviesWithWEvenLength(): JsonResponse
    {
        try {
            $movies = $this->recommendationService->getMoviesWithWEvenLength();
            return ApiResponse::success($movies);
        }catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
    #[Route('/multi-word-movies', name: 'multi_word_movies', methods: ['GET'])]
    public function getMultiWordMovies(): JsonResponse
    {
        try {
            $movies = $this->recommendationService->getMultiWordMovies();
            return ApiResponse::success($movies);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
