<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\MovieRecommendationController;
use App\Service\RecommendationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class MovieRecommendationControllerTest extends TestCase
{
    private RecommendationService $recommendationService;
    private MovieRecommendationController $controller;

    protected function setUp(): void
    {
        $this->recommendationService = $this->createMock(RecommendationService::class);
        $this->controller = new MovieRecommendationController($this->recommendationService);
    }

    public function testGetRandomMovies(): void
    {
        $expectedMovies = ['Movie 1', 'Movie 2', 'Movie 3'];
        $this->recommendationService
            ->expects($this->once())
            ->method('getRandomMovies')
            ->willReturn($expectedMovies);

        $response = $this->controller->getRandomMovies();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($expectedMovies, $responseData['data']);
    }

    public function testGetRandomMoviesHandlesException(): void
    {
        $this->recommendationService
            ->expects($this->once())
            ->method('getRandomMovies')
            ->willThrowException(new \RuntimeException('Test error'));

        $response = $this->controller->getRandomMovies();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Test error', $responseData['message']);
    }

    public function testGetMoviesWithWEvenLength(): void
    {
        $expectedMovies = ['Whiplash', 'Wyspa tajemnic'];
        $this->recommendationService
            ->expects($this->once())
            ->method('getMoviesWithWEvenLength')
            ->willReturn($expectedMovies);

        $response = $this->controller->getMoviesWithWEvenLength();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($expectedMovies, $responseData['data']);
    }

    public function testGetMultiWordMovies(): void
    {
        $expectedMovies = ['Pulp Fiction', 'Fight Club'];
        $this->recommendationService
            ->expects($this->once())
            ->method('getMultiWordMovies')
            ->willReturn($expectedMovies);

        $response = $this->controller->getMultiWordMovies();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($expectedMovies, $responseData['data']);
    }
}