<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use RajaOngkir\Exceptions\ApiException;

class ApiExceptionTest extends TestCase
{
    private array $sampleErrorResponse = [
        'rajaongkir' => [
            'query' => [],
            'status' => [
                'code' => 400,
                'description' => 'Invalid API key'
            ]
        ]
    ];

    public function testFromResponse(): void
    {
        $exception = ApiException::fromResponse($this->sampleErrorResponse);

        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('Invalid API key', $exception->getMessage());
        $this->assertEquals($this->sampleErrorResponse, $exception->getResponse());
    }

    public function testGetResponse(): void
    {
        $exception = new ApiException(
            'Test error',
            500,
            ['error' => 'test']
        );

        $this->assertEquals(['error' => 'test'], $exception->getResponse());
    }

    public function testGetStatusCode(): void
    {
        $exception = new ApiException('Test error', 404);
        $this->assertEquals(404, $exception->getStatusCode());
    }

    public function testWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new ApiException('Test error', 500, null, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
