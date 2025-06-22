<?php
/**
 * Contoh Penggunaan Library RajaOngkir untuk Pencarian Tujuan Internasional
 * 
 * File ini menunjukkan cara menggunakan library RajaOngkir untuk:
 * 1. Mencari tujuan pengiriman internasional
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

// Fungsi untuk menampilkan daftar negara
showCountries();

// Fungsi untuk mencari negara berdasarkan nama
searchCountry('singapore');

/**
 * Menampilkan daftar negara
 */
function showCountries(): void
{
    global $rajaOngkir;
    
    echo "🌍 Daftar Negara Tujuan Pengiriman\n";
    echo str_repeat("=", 50) . "\n";
    
    try {
        $countries = $rajaOngkir->searchInternationalDestinations('', 200, 0);
        
        if (empty($countries)) {
            echo "❌ Tidak dapat mengambil data negara.\n";
            return;
        }
        
        // Ambil beberapa negara pertama sebagai contoh
        $sampleCountries = array_slice($countries, 0, 5);
        
        foreach ($sampleCountries as $country) {
            echo "- {$country['country_id']}: {$country['country_name']} ({$country['country_code']})\n";
        }
        
        echo "\nℹ️ Menampilkan 5 dari " . count($countries) . " negara\n";
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

/**
 * Mencari negara berdasarkan nama
 * 
 * @param string $countryName Nama negara yang dicari
 * @return array Array berisi hasil pencarian
 */
function searchCountry(string $countryName): array
{
    global $rajaOngkir;
    
    echo "\n🔍 Mencari negara dengan nama: " . htmlspecialchars($countryName) . "\n";
    echo str_repeat("=", 70) . "\n\n";
    
    try {
        // Lakukan pencarian negara
        $results = $rajaOngkir->searchInternationalDestinations($countryName, 5, 0);
        
        if (empty($results)) {
            echo "❌ Tidak ditemukan hasil untuk pencarian: " . htmlspecialchars($countryName) . "\n";
            return [];
        }
        
        echo "✅ Ditemukan " . count($results) . " hasil untuk pencarian: " . htmlspecialchars($countryName) . "\n\n";
        
        // Tampilkan header tabel
        echo str_pad("NO", 5) . " | " . 
             str_pad("ID NEGARA", 15) . " | " .
             "NAMA NEGARA\n";
        echo str_repeat("-", 50) . "\n";
        
        // Tampilkan data negara
        $no = 1;
        foreach ($results as $country) {
            echo str_pad($no . ".", 5) . " | " . 
                 str_pad($country['country_id'] ?? 'N/A', 15) . " | " . 
                 ($country['country_name'] ?? 'N/A') . "\n";
            $no++;
        }
        
        $totalFound = count($results);
        $showing = min($totalFound, 5);
        echo "\nℹ️ Menampilkan {$showing} dari {$totalFound} hasil pencarian\n";
        
        if ($totalFound > 5) {
            echo "ℹ️ Gunakan parameter tambahan untuk mempersempit pencarian\n";
        }
        
        return $results;
        
    } catch (ApiException $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Kode Error: " . $e->getCode() . "\n";
        if ($e->getPrevious()) {
            echo "Detail: " . $e->getPrevious()->getMessage() . "\n";
        }
        return [];
    } catch (\Exception $e) {
        echo "❌ Terjadi kesalahan: " . $e->getMessage() . "\n";
        return [];
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "CARA MENGGUNAKAN:\n";
echo "1. Ubah parameter pencarian di baris kode 'searchCountry('singapore')'\n";
echo "2. Simpan file dan jalankan: php examples/example_search-international-destination.php\n";
echo str_repeat("=", 70) . "\n\n";
