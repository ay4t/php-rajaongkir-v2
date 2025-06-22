<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use RajaOngkir\Http\Response;

class ResponseTest extends TestCase
{
    private array $sampleResponse = [
        'rajaongkir' => [
            'query' => [],
            'status' => [
                'code' => 200,
                'description' => 'OK'
            ],
            'results' => [
                ['id' => 1, 'name' => 'Test']
            ]
        ]
    ];

    public function testCreateFromArray(): void
    {
        $response = Response::fromArray($this->sampleResponse);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->getData());
        $this->assertArrayHasKey('id', $response->getData()[0]);
        $this->assertArrayHasKey('description', $response->getStatus());
    }

    public function testIsSuccess(): void
    {
        $successResponse = Response::fromArray($this->sampleResponse);
        $this->assertTrue($successResponse->isSuccess());

        $errorResponse = new Response(400, null, ['code' => 400, 'description' => 'Bad Request']);
        $this->assertFalse($errorResponse->isSuccess());
    }

    public function testToArray(): void
    {
        $response = Response::fromArray($this->sampleResponse);
        $array = $response->toArray();

        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('original', $array);
        $this->assertEquals('OK', $array['status']['description']);
    }

    public function testToString(): void
    {
        $response = Response::fromArray($this->sampleResponse);
        $string = (string) $response;

        $this->assertJson($string);
        $decoded = json_decode($string, true);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertArrayHasKey('data', $decoded);
    }
}
