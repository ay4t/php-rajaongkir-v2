<?php

namespace RajaOngkir\Http;

class Response
{
    private int $statusCode;
    private $data;
    private array $status;
    private array $originalResponse;

    public function __construct(int $statusCode, $data, array $status = [], array $originalResponse = [])
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->status = $status;
        $this->originalResponse = $originalResponse;
    }

    public static function fromArray(array $response): self
    {
        $status = $response['rajaongkir']['status'] ?? [];
        $code = $status['code'] ?? 500;
        $data = $response['rajaongkir']['results'] ?? $response['rajaongkir']['result'] ?? null;

        return new self(
            $code,
            $data,
            $status,
            $response
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function getOriginalResponse(): array
    {
        return $this->originalResponse;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode === 200;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'data' => $this->data,
            'original' => $this->originalResponse,
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
