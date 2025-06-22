<?php

namespace RajaOngkir;

use Ay4t\RestClient\Client as RestClient;
use Ay4t\RestClient\Config\Config as RestClientConfig;
use RajaOngkir\Config\RajaOngkirConfig;
use RajaOngkir\Exceptions\ApiException;
use RajaOngkir\Http\Response;

class RajaOngkir
{
    private RestClient $client;
    private RajaOngkirConfig $config;

    public function __construct(string $apiKey, string $accountType = 'starter', bool $verifySSL = true)
    {
        $this->config = new RajaOngkirConfig($apiKey, $accountType, $verifySSL);
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        $config = new RestClientConfig();
        $config->setBaseUri($this->config->getBaseUri());
        $config->setApiKey($this->config->getApiKey());
        
        $this->client = new RestClient($config);
        $this->client->setRequestOptions([
            'verify' => $this->config->shouldVerifySSL(),
            'headers' => [
                'key' => $this->config->getApiKey(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ]);
    }

    /**
     * Get all provinces
     */
    public function getProvinces(): array
    {
        $response = $this->client->cmd('GET', 'province');
        return $this->createResponse($response);
    }

    /**
     * Get a province by ID
     */
    public function getProvince(int $id): ?array
    {
        $response = $this->client->cmd('GET', 'province', ['id' => $id]);
        return $this->createResponse($response);
    }

    /**
     * Get all cities
     */
    public function getCities(int $provinceId = null): array
    {
        $params = [];
        if ($provinceId !== null) {
            $params['province'] = $provinceId;
        }
        
        $response = $this->client->cmd('GET', 'city', $params);
        return $this->createResponse($response);
    }

    /**
     * Get a city by ID
     */
    public function getCity(int $id): ?array
    {
        $response = $this->client->cmd('GET', 'city', ['id' => $id]);
        return $this->createResponse($response);
    }

    /**
     * Search domestic destinations
     * 
     * @throws ApiException If the API request fails
     */
    public function searchDomesticDestinations(string $search, int $limit = 10, int $offset = 0): array
    {
        try {
            $params = [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset
            ];
            
            // Use the correct endpoint based on account type
            if ($this->config->getAccountType() === 'starter') {
                // For starter accounts, use the domestic-destination endpoint
                $response = $this->client->cmd('GET', 'destination/domestic-destination', [
                    'search' => $search,
                    'limit' => $limit,
                    'offset' => $offset
                ]);
                
                $result = $this->createResponse($response);
                
                // Standardize the response format
                if (isset($result['rajaongkir']['results'])) {
                    return array_map(function($city) {
                        return [
                            'city_id' => $city['city_id'] ?? null,
                            'province_id' => $city['province_id'] ?? null,
                            'province' => $city['province'] ?? null,
                            'type' => $city['type'] ?? null,
                            'city_name' => $city['city_name'] ?? $city['city'] ?? null,
                            'postal_code' => $city['postal_code'] ?? null
                        ];
                    }, $result['rajaongkir']['results']);
                }
                
                return [];
            } else {
                // For pro/basic accounts, use the domestic-destination endpoint with additional features
                $response = $this->client->cmd('GET', 'destination/domestic-destination', [
                    'search' => $search,
                    'limit' => $limit,
                    'offset' => $offset
                ]);
                
                $result = $this->createResponse($response);
                
                // Standardize the response format
                if (isset($result['rajaongkir']['results'])) {
                    return array_map(function($city) {
                        return [
                            'city_id' => $city['city_id'] ?? null,
                            'province_id' => $city['province_id'] ?? null,
                            'province' => $city['province'] ?? null,
                            'type' => $city['type'] ?? null,
                            'city_name' => $city['city_name'] ?? $city['city'] ?? null,
                            'postal_code' => $city['postal_code'] ?? null
                        ];
                    }, $result['rajaongkir']['results']);
                }
                
                return [];
            }
        } catch (\Exception $e) {
            throw new ApiException(
                'Failed to search domestic destinations: ' . $e->getMessage(),
                $e->getCode(),
                null,
                $e
            );
        }
    }

    /**
     * Search international destinations
     */
    /**
     * Search international destinations
     * 
     * @param string $search Search term for country name
     * @param int $limit Number of results to return (default: 10)
     * @param int $offset Starting offset (default: 0)
     * @return array List of international destinations
     * @throws ApiException If the API request fails
     */
    public function searchInternationalDestinations(string $search = '', int $limit = 10, int $offset = 0): array
    {
        try {
            $params = [];
            
            // Only add search parameter if not empty
            if (!empty($search)) {
                $params['search'] = $search;
            }
            
            // Add pagination parameters if needed
            if ($limit > 0) {
                $params['limit'] = $limit;
            }
            
            if ($offset > 0) {
                $params['offset'] = $offset;
            }
            
            // Call the international destination endpoint
            $response = $this->client->cmd('GET', 'destination/international-destination', $params);
            $result = $this->createResponse($response);
            
            // Return the data as is, as it's already in a standardized format
            return $result['data'] ?? [];
            
        } catch (\Exception $e) {
            throw new ApiException(
                'Failed to search international destinations: ' . $e->getMessage(),
                $e->getCode(),
                null,
                $e
            );
        }
    }

    /**
     * Calculate domestic shipping cost
     */
    /**
     * Calculate domestic shipping cost
     * 
     * @param array $params Array berisi parameter yang diperlukan:
     *   - origin: ID kota asal (required)
     *   - destination: ID kota tujuan (required)
     *   - weight: Berat dalam gram (required)
     *   - courier: Kode kurir (jne, pos, tiki, dll) (required)
     *   - price: (opsional) Urutan harga (lowest, highest)
     * @return array Hasil perhitungan ongkos kirim
     * @throws ApiException Jika terjadi kesalahan
     */
    public function calculateDomesticCost(array $params): array
    {
        $required = ['origin', 'destination', 'weight', 'courier'];
        $this->validateParams($params, $required);
        
        // Format parameter untuk request
        $requestParams = [
            'origin' => $params['origin'],
            'destination' => $params['destination'],
            'weight' => $params['weight'],
            'courier' => $params['courier']
        ];
        
        // Tambahkan parameter opsional jika ada
        if (isset($params['price'])) {
            $requestParams['price'] = $params['price'];
        }
        
        // Set options untuk request
        $this->client->setRequestOptions([
            'headers' => [
                'key' => $this->config->getApiKey(),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        
        // Kirim request dengan parameter sebagai string query
        $queryString = http_build_query($requestParams);
        $response = $this->client->cmd('POST', 'calculate/domestic-cost?' . $queryString, []);
        $result = $this->createResponse($response);
        
        return $result['data'] ?? [];
    }

    /**
     * Calculate international shipping cost
     * 
     * @param array $params Array berisi parameter yang diperlukan:
     *   - origin: ID kota asal (required)
     *   - destination: ID negara tujuan (required)
     *   - weight: Berat dalam gram (required)
     *   - courier: Kode kurir (pos, jne, tiki, dll) (required)
     * @return array Hasil perhitungan ongkos kirim internasional
     * @throws ApiException Jika terjadi kesalahan
     */
    public function calculateInternationalCost(array $params): array
    {
        $required = ['origin', 'destination', 'weight', 'courier'];
        $this->validateParams($params, $required);
        
        // Format parameter untuk request
        $requestParams = [
            'origin' => $params['origin'],
            'destination' => $params['destination'],
            'weight' => $params['weight'],
            'courier' => $params['courier']
        ];
        
        // Set options untuk request
        $this->client->setRequestOptions([
            'headers' => [
                'key' => $this->config->getApiKey(),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);
        
        // Kirim request dengan parameter sebagai string query
        $queryString = http_build_query($requestParams);
        $response = $this->client->cmd('POST', 'calculate/international-cost?' . $queryString, []);
        $result = $this->createResponse($response);
        
        return $result['data'] ?? [];
    }

    /**
     * Melacak status pengiriman berdasarkan nomor resi
     * 
     * @param string $waybill Nomor resi yang akan dilacak
     * @param string $courier Kode kurir (jne, pos, tiki, dll)
     * @return array Data pelacakan pengiriman
     * @throws ApiException Jika terjadi kesalahan
     */
    public function trackWaybill(string $waybill, string $courier): array
    {
        // Validasi parameter
        if (empty($waybill)) {
            throw new \InvalidArgumentException('Nomor resi tidak boleh kosong');
        }
        
        if (empty($courier)) {
            throw new \InvalidArgumentException('Kode kurir tidak boleh kosong');
        }
        
        // Reset request options terlebih dahulu
        $this->client->setRequestOptions([]);
        
        // Buat URL dengan parameter query
        $queryString = http_build_query([
            'awb' => $waybill,
            'courier' => $courier
        ]);
        
        // Set options untuk request
        $this->client->setRequestOptions([
            'headers' => [
                'key' => $this->config->getApiKey()
            ],
            // Pastikan tidak ada Content-Type header untuk request GET
            'http_errors' => false
        ]);
        
        // Kirim request dengan method POST dan parameter query string
        $response = $this->client->cmd('POST', 'track/waybill?' . $queryString, []);
        $result = $this->createResponse($response);
        
        return $result['data'] ?? [];
    }

    /**
     * Create response object from API response
     */
    private function createResponse($response)
    {
        if (!is_array($response)) {
            throw new ApiException('Invalid API response format');
        }

        $code = $response['meta']['code'] ?? 500;
        $message = $response['meta']['message'] ?? 'API request failed';
        $data = $response['data'] ?? null;

        if ($code !== 200) {
            throw new ApiException($message, $code);
        }

        return [
            'meta' => [
                'code' => $code,
                'message' => $message,
                'status' => $response['meta']['status'] ?? 'success',
            ],
            'data' => $data
        ];
    }

    /**
     * Validate required parameters
     */
    private function validateParams(array $params, array $required): void
    {
        $missing = [];
        foreach ($required as $param) {
            if (!isset($params[$param]) || $params[$param] === '') {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required parameters: ' . implode(', ', $missing)
            );
        }
    }
}
