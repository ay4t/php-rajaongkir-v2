<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RajaOngkir\RajaOngkir;
use RajaOngkir\Config\RajaOngkirConfig;
use RajaOngkir\Exceptions\ApiException;

class RajaOngkirTest extends TestCase
{
    private static ?RajaOngkir $rajaOngkir = null;
    private static string $testApiKey;
    private static string $accountType;
    private static bool $verifySSL;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        self::$testApiKey = $_ENV['RAJAONGKIR_API_KEY'] ?? 'test_api_key';
        self::$accountType = $_ENV['RAJAONGKIR_ACCOUNT_TYPE'] ?? 'starter';
        self::$verifySSL = filter_var($_ENV['RAJAONGKIR_VERIFY_SSL'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        if (self::$rajaOngkir === null) {
            self::$rajaOngkir = new RajaOngkir(
                self::$testApiKey,
                self::$accountType,
                self::$verifySSL
            );
        }
    }

    public function testGetProvinces(): void
    {
        try {
            $provinces = self::$rajaOngkir->getProvinces();
            
            $this->assertIsArray($provinces);
            
            // Skip assertions if we're using a test API key
            if (self::$testApiKey !== 'test_api_key') {
                $this->assertNotEmpty($provinces);
                $this->assertArrayHasKey('province_id', $provinces[0]);
                $this->assertArrayHasKey('province', $provinces[0]);
            }
        } catch (ApiException $e) {
            // Skip if API key is invalid or request fails for other reasons
            $this->markTestSkipped('API request failed: ' . $e->getMessage());
        }
    }

    public function testGetProvinceById(): void
    {
        try {
            // Test with DKI Jakarta province ID
            $province = self::$rajaOngkir->getProvince(6);
            
            // Skip assertions if we're using a test API key
            if (self::$testApiKey !== 'test_api_key') {
                $this->assertIsArray($province);
                $this->assertEquals('DKI Jakarta', $province['province']);
            }
        } catch (ApiException $e) {
            // Skip if API key is invalid or request fails for other reasons
            $this->markTestSkipped('API request failed: ' . $e->getMessage());
        }
    }

    public function testGetCities(): void
    {
        try {
            $cities = self::$rajaOngkir->getCities();
            
            $this->assertIsArray($cities);
            
            // Skip assertions if we're using a test API key
            if (self::$testApiKey !== 'test_api_key') {
                $this->assertNotEmpty($cities);
                $this->assertArrayHasKey('city_id', $cities[0]);
                $this->assertArrayHasKey('city_name', $cities[0]);
            }
        } catch (ApiException $e) {
            // Skip if API key is invalid or request fails for other reasons
            $this->markTestSkipped('API request failed: ' . $e->getMessage());
        }
    }

    public function testGetCityById(): void
    {
        try {
            // Test with Jakarta Pusat city ID
            $city = self::$rajaOngkir->getCity(152);
            
            // Skip assertions if we're using a test API key
            if (self::$testApiKey !== 'test_api_key') {
                $this->assertIsArray($city);
                
                // Check for both possible city name formats
                $validCityNames = ['Kota Jakarta Pusat', 'Jakarta Pusat'];
                $this->assertContains(
                    $city['city_name'] ?? null,
                    $validCityNames,
                    'City name does not match expected values: ' . ($city['city_name'] ?? 'null')
                );
            }
        } catch (ApiException $e) {
            // Skip if API key is invalid or request fails for other reasons
            $this->markTestSkipped('API request failed: ' . $e->getMessage());
        }
    }

    public function testSearchDomesticDestinations(): void
    {
        try {
            $results = self::$rajaOngkir->searchDomesticDestinations('solo', 5);
            
            $this->assertIsArray($results);
            
            // Skip assertions if we're using a test API key
            if (self::$testApiKey !== 'test_api_key') {
                $this->assertLessThanOrEqual(5, count($results));
                
                if (count($results) > 0) {
                    // Check for both possible response formats
                    $hasCityId = isset($results[0]['city_id']);
                    $hasPostalCode = isset($results[0]['postal_code']);
                    
                    // At least one of these should be true
                    $this->assertTrue(
                        $hasCityId || $hasPostalCode,
                        'Response does not contain expected city data structure. ' .
                        'Expected either city_id or postal_code in the response.'
                    );
                    
                    // Check for city name in any format
                    $hasCityName = false;
                    $cityNameKeys = ['city_name', 'city', 'cityName', 'cityName'];
                    
                    foreach ($cityNameKeys as $key) {
                        if (isset($results[0][$key])) {
                            $hasCityName = true;
                            break;
                        }
                    }
                    
                    $this->assertTrue(
                        $hasCityName,
                        'Response does not contain city name in any expected format. ' .
                        'Keys checked: ' . implode(', ', $cityNameKeys)
                    );
                }
            }
        } catch (ApiException $e) {
            // Skip if API key is invalid or request fails for other reasons
            $this->markTestSkipped('API request failed: ' . $e->getMessage());
        }
    }
}
