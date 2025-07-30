<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utility;

use App\Utility\ApiResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ApiResponseTest extends TestCase
{
    public function testSuccessWithDefaultParameters(): void
    {
        $response = ApiResponse::success();

        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Success', $content['message']);
        $this->assertNull($content['data']);
    }

    public function testSuccessWithCustomData(): void
    {
        $data = ['movies' => ['The Matrix', 'Inception']];
        $message = 'Movies retrieved successfully';
        $statusCode = 201;

        $response = ApiResponse::success($data, $message, $statusCode);

        $this->assertEquals($statusCode, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals($message, $content['message']);
        $this->assertEquals($data, $content['data']);
    }

    public function testSuccessWithNullData(): void
    {
        $response = ApiResponse::success(null, 'Custom message');

        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Custom message', $content['message']);
        $this->assertNull($content['data']);
    }

    public function testSuccessWithArrayData(): void
    {
        $data = [
            ['id' => 1, 'title' => 'Movie 1'],
            ['id' => 2, 'title' => 'Movie 2']
        ];

        $response = ApiResponse::success($data);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($data, $content['data']);
    }

    public function testErrorWithDefaultParameters(): void
    {
        $response = ApiResponse::error();

        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Error', $content['message']);
    }

    public function testErrorWithCustomParameters(): void
    {
        $message = 'Validation failed';
        $statusCode = 422;

        $response = ApiResponse::error($message, $statusCode);

        $this->assertEquals($statusCode, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($message, $content['message']);
    }

    public function testNotFoundWithDefaultMessage(): void
    {
        $response = ApiResponse::notFound();

        $this->assertEquals(404, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Resource not found', $content['message']);
    }

    public function testNotFoundWithCustomMessage(): void
    {
        $message = 'Movie not found';

        $response = ApiResponse::notFound($message);

        $this->assertEquals(404, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($message, $content['message']);
    }

    public function testServerErrorWithDefaultMessage(): void
    {
        $response = ApiResponse::serverError();

        $this->assertEquals(500, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Internal server error', $content['message']);
    }

    public function testServerErrorWithCustomMessage(): void
    {
        $message = 'Database server is down';

        $response = ApiResponse::serverError($message);

        $this->assertEquals(500, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($message, $content['message']);
    }

    public function testResponseContentType(): void
    {
        $response = ApiResponse::success();

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testResponseStructure(): void
    {
        $response = ApiResponse::success(['test' => 'data']);
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('data', $content);
    }

    public function testErrorResponseStructure(): void
    {
        $response = ApiResponse::error('Test error');
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('message', $content);
    }

    #[DataProvider('successStatusCodeProvider')] public function testSuccessWithDifferentStatusCodes(int $statusCode): void
    {
        $response = ApiResponse::success(null, 'Test', $statusCode);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public static function successStatusCodeProvider(): array
    {
        return [
            [200],
            [201],
            [202],
            [204],
        ];
    }

    #[DataProvider('errorStatusCodeProvider')] public function testErrorWithDifferentStatusCodes(int $statusCode): void
    {
        $response = ApiResponse::error('Test error', $statusCode);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public static function errorStatusCodeProvider(): array
    {
        return [
            [400],
            [401],
            [403],
            [422],
            [500],
        ];
    }
}