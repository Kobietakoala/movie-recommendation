<?php

declare(strict_types=1);

namespace App\Tests\Unit\Traits;

use App\Traits\ErrorMessagesTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ErrorMessagesTraitTest extends TestCase
{
    private object $traitObject;

    protected function setUp(): void
    {
        $this->traitObject = new class {
            use ErrorMessagesTrait;

            public function getErrorMessagePublic(string $key): string
            {
                return $this->getErrorMessage($key);
            }
        };
    }

    public function testShouldReturnCorrectErrorMessageForMoviesNotFound(): void
    {
        $key = 'movies_not_found';
        $expectedMessage = 'File data/movies.php not found';

        $result = $this->traitObject->getErrorMessagePublic($key);
        $this->assertEquals($expectedMessage, $result);
    }

    public function testShouldReturnCorrectErrorMessageForInvalidMoviesFormat(): void
    {
        $key = 'invalid_movies_format';
        $expectedMessage = 'Could not find $movies array in movies.php file';

        $result = $this->traitObject->getErrorMessagePublic($key);
        $this->assertEquals($expectedMessage, $result);
    }

    public function testShouldReturnCorrectErrorMessageForNonPositiveMovieCount(): void
    {
        $key = 'non_positive_movie_count';
        $expectedMessage = 'Number of movies to retrieve must be greater than 0';

        $result = $this->traitObject->getErrorMessagePublic($key);
        $this->assertEquals($expectedMessage, $result);
    }

    public function testShouldReturnCorrectErrorMessageForMoviesPathNotFoundError(): void
    {
        $key = 'movies_path_not_found';
        $expectedMessage = 'Path to movies.php file not found';

        $result = $this->traitObject->getErrorMessagePublic($key);
        $this->assertEquals($expectedMessage, $result);
    }

    public function testShouldReturnDefaultErrorMessageForUnknownKey(): void
    {
        $unknownKey = 'non_existent_key';
        $expectedDefaultMessage = 'Unknown error';

        $result = $this->traitObject->getErrorMessagePublic($unknownKey);
        $this->assertEquals($expectedDefaultMessage, $result);
    }

    #[DataProvider('allErrorKeysProvider')]
    public function testShouldReturnNonEmptyStringForValidKeys(string $key, string $expectedMessage): void
    {
        $result = $this->traitObject->getErrorMessagePublic($key);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
        $this->assertEquals($expectedMessage, $result);
    }

    #[DataProvider('allErrorKeysProvider')]
    public function testShouldReturnConsistentMessagesForSameKey(string $key, string $expectedMessage): void
    {
        $result1 = $this->traitObject->getErrorMessagePublic($key);
        $result2 = $this->traitObject->getErrorMessagePublic($key);

        $this->assertEquals($result1, $result2);
        $this->assertEquals($expectedMessage, $result1);
    }

    public function testShouldHandleEmptyStringKey(): void
    {
        $emptyKey = '';
        $expectedDefaultMessage = 'Unknown error';

        $result = $this->traitObject->getErrorMessagePublic($emptyKey);
        $this->assertEquals($expectedDefaultMessage, $result);
    }

    public function testGetErrorMessageIsProtected(): void
    {
        $reflection = new \ReflectionClass($this->traitObject);
        $method = $reflection->getMethod('getErrorMessage');

        $this->assertTrue($method->isProtected());
        $this->assertFalse($method->isPublic());
        $this->assertFalse($method->isPrivate());
    }

    public function testGetErrorMessageReturnsString(): void
    {
        $key = 'movies_not_found';

        $result = $this->traitObject->getErrorMessagePublic($key);
        $this->assertIsString($result);
    }

    public function testAllDefinedKeysReturnUniqueMessages(): void
    {
        $messages = [];

        $keys = array_column(array_filter(self::allErrorKeysProvider(), fn($item) => $item[0] !== 'unknown_key'), 0);

        foreach ($keys as $key) {
            $messages[$key] = $this->traitObject->getErrorMessagePublic($key);
        }

        $this->assertCount(count($keys), array_unique($messages));
        foreach ($messages as $message) {
            $this->assertNotEquals('Unknown error', $message);
        }
    }

    /**
     * Data provider for all error keys with expected messages
     */
    public static function allErrorKeysProvider(): array
    {
        return [
            ['movies_not_found', 'File data/movies.php not found'],
            ['invalid_movies_format', 'Could not find $movies array in movies.php file'],
            ['non_positive_movie_count', 'Number of movies to retrieve must be greater than 0'],
            ['movies_path_not_found', 'Path to movies.php file not found'],
            ['unknown_key', 'Unknown error'],
        ];
    }
}
