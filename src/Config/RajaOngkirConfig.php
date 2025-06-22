<?php

namespace RajaOngkir\Config;

class RajaOngkirConfig
{
    public const BASE_URI = 'https://rajaongkir.komerce.id/api/v1';
    public const BASE_URI_STARTER = 'https://rajaongkir.komerce.id/api/v1';
    public const BASE_URI_PRO = 'https://rajaongkir.komerce.id/api/v1';

    private string $apiKey;
    private string $accountType;
    private string $baseUri;
    private bool $verifySSL;

    /**
     * @param string $apiKey API key untuk autentikasi
     * @param string $accountType Tipe akun (starter, basic, pro)
     * @param bool $verifySSL Verifikasi SSL (default: true)
     */
    public function __construct(
        string $apiKey,
        string $accountType = 'starter',
        bool $verifySSL = true
    ) {
        $this->apiKey = $apiKey;
        $this->setAccountType($accountType);
        $this->verifySSL = $verifySSL;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getAccountType(): string
    {
        return $this->accountType;
    }

    public function setAccountType(string $accountType): self
    {
        $validTypes = ['starter', 'basic', 'pro'];
        if (!in_array(strtolower($accountType), $validTypes, true)) {
            throw new \InvalidArgumentException(
                'Invalid account type. Must be one of: ' . implode(', ', $validTypes)
            );
        }

        $this->accountType = strtolower($accountType);
        $this->updateBaseUri();
        
        return $this;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function shouldVerifySSL(): bool
    {
        return $this->verifySSL;
    }

    public function setVerifySSL(bool $verifySSL): self
    {
        $this->verifySSL = $verifySSL;
        return $this;
    }

    private function updateBaseUri(): void
    {
        switch ($this->accountType) {
            case 'pro':
                $this->baseUri = self::BASE_URI_PRO;
                break;
            case 'starter':
                $this->baseUri = self::BASE_URI_STARTER;
                break;
            default:
                $this->baseUri = self::BASE_URI;
        }
    }

    public function toArray(): array
    {
        return [
            'base_uri' => $this->getBaseUri(),
            'headers' => [
                'key' => $this->getApiKey(),
            ],
            'verify' => $this->shouldVerifySSL(),
        ];
    }
}
