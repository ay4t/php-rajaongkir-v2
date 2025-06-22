<?php

namespace RajaOngkir\Exceptions;

class ApiException extends \RuntimeException
{
    private ?array $response;
    private ?int $statusCode;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?array $response = null,
        ?\Throwable $previous = null
    ) {
        $this->response = $response;
        $this->statusCode = $code;
        
        parent::__construct($message, $code, $previous);
    }

    public static function fromResponse(array $response, ?\Throwable $previous = null): self
    {
        $status = $response['rajaongkir']['status'] ?? [];
        $message = $status['description'] ?? 'An unknown error occurred';
        $code = $status['code'] ?? 500;
        
        return new self($message, $code, $response, $previous);
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
