<?php
/**
 * Contoh Penggunaan Library RajaOngkir untuk Pencarian Tujuan
 * 
 * File ini menunjukkan cara menggunakan library RajaOngkir untuk:
 * 1. Mencari tujuan pengiriman domestik
 * 2. Menampilkan hasil pencarian dengan format yang rapi
 * 
 * Konfigurasi diambil dari file .env di direktori root
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RajaOngkir\RajaOngkir;
use RajaOngkir\Exceptions\ApiException;

// Muat konfigurasi dari file .env
require_once __DIR__ . '/load_env.php';

// ================================================
// KONFIGURASI AWAL
// ================================================


// Ambil konfigurasi dari environment variable
$apiKey = trim(getenv('RAJAONGKIR_API_KEY'));
$accountType = strtolower(trim(getenv('RAJAONGKIR_ACCOUNT_TYPE') ?: 'pro'));
$verifySSL = filter_var(getenv('RAJAONGKIR_VERIFY_SSL') ?: 'true', FILTER_VALIDATE_BOOLEAN);

// Validasi konfigurasi
if (empty($apiKey)) {
    die("\n❌ Error: RAJAONGKIR_API_KEY tidak ditemukan. Pastikan file .env sudah diatur dengan benar.\n");
}

if (strlen($apiKey) < 10) {
    die("\n❌ Error: API key tidak valid. Panjang API key minimal 10 karakter.\n");
}

if (!in_array($accountType, ['starter', 'basic', 'pro'])) {
    die("\n❌ Error: Tipe akun tidak valid. Gunakan 'starter', 'basic', atau 'pro'.\n");
}

// Inisialisasi dengan parameter yang sesuai
try {
    $rajaOngkir = new RajaOngkir($apiKey, $accountType, $verifySSL);
    echo "✅ Berhasil terhubung dengan API RajaOngkir (Akun: " . strtoupper($accountType) . ")\n\n";
} catch (\Exception $e) {
    die("❌ Gagal menginisialisasi RajaOngkir: " . $e->getMessage() . "\n");
}

// Fungsi untuk menampilkan daftar provinsi
showProvinces();

// Fungsi untuk menampilkan daftar kota dalam provinsi tertentu
// showCitiesByProvince(5); // Contoh: ID Provinsi Jawa Barat

// Fungsi untuk mencari kota berdasarkan nama
searchCity('jakarta');

/**
 * Menampilkan daftar provinsi
 */
function showProvinces(): void
{
    global $rajaOngkir;
    
    echo "\n🌏 Daftar Provinsi di Indonesia\n";
    echo str_repeat("=", 50) . "\n";
    
    try {
        $provinces = $rajaOngkir->getProvinces();
        
        if (empty($provinces)) {
            echo "❌ Tidak dapat mengambil data provinsi.\n";
            return;
        }
        
        // Ambil beberapa provinsi pertama sebagai contoh
        $sampleProvinces = array_slice($provinces, 0, 5);
        
        foreach ($sampleProvinces as $province) {
            echo "- {$province['province_id']}: {$province['province']}\n";
        }
        
        echo "\nℹ️ Menampilkan 5 dari " . count($provinces) . " provinsi\n";
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

/**
 * Menampilkan daftar kota dalam provinsi tertentu
 * 
 * @param int $provinceId ID Provinsi
 */
function showCitiesByProvince(int $provinceId): void
{
    global $rajaOngkir;
    
    echo "\n🏙️ Daftar Kota/Kabupaten di Provinsi ID: $provinceId\n";
    echo str_repeat("=", 50) . "\n";
    
    try {
        $cities = $rajaOngkir->getCities($provinceId);
        
        if (empty($cities)) {
            echo "❌ Tidak ditemukan kota untuk provinsi ID: $provinceId\n";
            return;
        }
        
        // Ambil beberapa kota pertama sebagai contoh
        $sampleCities = array_slice($cities, 0, 5);
        
        foreach ($sampleCities as $city) {
            echo "- {$city['city_id']}: {$city['city_name']} ({$city['type']})\n";
        }
        
        echo "\nℹ️ Menampilkan 5 dari " . count($cities) . " kota/kabupaten\n";
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

/**
 * Mencari kota berdasarkan nama
 * 
 * @param string $cityName Nama kota yang dicari
 */
function searchCity(string $cityName): void
{
    global $rajaOngkir;
    
    echo "\n🔍 Mencari kota dengan nama: " . htmlspecialchars($cityName) . "\n";
    echo str_repeat("=", 70) . "\n\n";
    
    try {
        // Gunakan metode getCities untuk mendapatkan semua kota
        $allCities = $rajaOngkir->getCities();
        
        if (empty($allCities)) {
            echo "❌ Tidak dapat mengambil data kota.\n";
            return;
        }
        
        // Lakukan pencarian manual dalam daftar kota
        $filteredCities = array_filter($allCities, function($city) use ($cityName) {
            return stripos($city['city_name'] ?? '', $cityName) !== false;
        });
        
        // Urutkan berdasarkan kemiripan dengan query pencarian
        usort($filteredCities, function($a, $b) use ($cityName) {
            $aScore = similar_text(strtolower($a['city_name']), strtolower($cityName));
            $bScore = similar_text(strtolower($b['city_name']), strtolower($cityName));
            return $bScore <=> $aScore;
        });
        
        $results = array_slice($filteredCities, 0, 5);
        
        if (empty($results)) {
            echo "❌ Tidak ditemukan hasil untuk pencarian: " . htmlspecialchars($cityName) . "\n";
            return;
        }
        
        // Tampilkan header
        echo "\n🔍 Hasil Pencarian untuk: " . htmlspecialchars($cityName) . "\n";
        echo str_repeat("-", 70) . "\n";
        
        // Tampilkan hasil pencarian
        foreach ($results as $result) {
            echo "\n🏙️ " . ($result['type'] ?? 'Kota') . " " . ($result['city_name'] ?? 'N/A') . "\n";
            echo "├─ ID: " . ($result['city_id'] ?? 'N/A') . "\n";
            echo "├─ Tipe: " . ($result['type'] ?? 'N/A') . "\n";
            echo "├─ Kode Pos: " . ($result['postal_code'] ?? 'N/A') . "\n";
            echo "└─ Provinsi: " . ($result['province'] ?? 'N/A') . "\n";
        }
        
        $totalFound = count($filteredCities);
        $showing = min(count($results), 5);
        echo "\nℹ️ Menampilkan {$showing} dari {$totalFound} hasil pencarian\n";
        
        if ($totalFound > 5) {
            echo "ℹ️ Gunakan parameter tambahan untuk mempersempit pencarian\n";
        }
        
    } catch (ApiException $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Kode Error: " . $e->getCode() . "\n";
        if ($e->getPrevious()) {
            echo "Detail: " . $e->getPrevious()->getMessage() . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Terjadi kesalahan: " . $e->getMessage() . "\n";
    }
}

// Tampilkan petunjuk penggunaan
echo "\n" . str_repeat("=", 70) . "\n";
echo "CARA MENGGUNAKAN:\n";
echo "1. Ubah parameter pencarian di baris kode 'displaySearchResults('jakarta')'\n";
echo "2. Simpan file dan jalankan: php examples/example_search-destination-rajaongkir.php\n";
echo str_repeat("=", 70) . "\n\n";
