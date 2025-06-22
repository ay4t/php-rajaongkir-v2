<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use RajaOngkir\Config\RajaOngkirConfig;

class RajaOngkirConfigTest extends TestCase
{
    private const TEST_API_KEY = 'test_api_key_123';

    public function testDefaultConfiguration(): void
    {
        $config = new RajaOngkirConfig(self::TEST_API_KEY);

        $this->assertEquals(self::TEST_API_KEY, $config->getApiKey());
        $this->assertEquals('starter', $config->getAccountType());
        $this->assertTrue($config->shouldVerifySSL());
        $this->assertEquals(RajaOngkirConfig::BASE_URI_STARTER, $config->getBaseUri());
    }

    public function testProAccountType(): void
    {
        $config = new RajaOngkirConfig(self::TEST_API_KEY, 'pro');
        $this->assertEquals('pro', $config->getAccountType());
        $this->assertEquals(RajaOngkirConfig::BASE_URI_PRO, $config->getBaseUri());
    }

    public function testBasicAccountType(): void
    {
        $config = new RajaOngkirConfig(self::TEST_API_KEY, 'basic');
        $this->assertEquals('basic', $config->getAccountType());
        $this->assertEquals(RajaOngkirConfig::BASE_URI, $config->getBaseUri());
    }

    public function testInvalidAccountType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RajaOngkirConfig(self::TEST_API_KEY, 'invalid_type');
    }

    public function testSslVerification(): void
    {
        $config = new RajaOngkirConfig(self::TEST_API_KEY, 'starter', false);
        $this->assertFalse($config->shouldVerifySSL());

        $config->setVerifySSL(true);
        $this->assertTrue($config->shouldVerifySSL());
    }

    public function testToArray(): void
    {
        $config = new RajaOngkirConfig(self::TEST_API_KEY);
        $array = $config->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('base_uri', $array);
        $this->assertArrayHasKey('headers', $array);
        $this->assertArrayHasKey('verify', $array);
        $this->assertArrayHasKey('key', $array['headers']);
        $this->assertEquals(self::TEST_API_KEY, $array['headers']['key']);
    }
}
