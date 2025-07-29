<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MovieRecommendationController extends AbstractController
{
    #[Route('/random-movies', name: 'random_movies', methods: ['GET'])]
    public function getRandomMovies(): JsonResponse
    {
        return new JsonResponse();
    }
    #[Route('/movies-with-w-even-length', name: 'movies_with_w_even_length', methods: ['GET'])]
    public function getMoviesWithWEvenLength(): JsonResponse
    {
        return new JsonResponse();
    }
    #[Route('/multi-word-movies', name: 'multi_word_movies', methods: ['GET'])]
    public function getMultiWordMovies(): JsonResponse
    {
        return new JsonResponse();
    }
}
