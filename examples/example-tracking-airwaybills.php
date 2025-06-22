<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/load_env.php';

use RajaOngkir\RajaOngkir;
use RajaOngkir\Exceptions\ApiException;

// Muat konfigurasi dari file .env
loadEnv(__DIR__ . '/../.env');

$apiKey = getenv('RAJAONGKIR_API_KEY');
$accountType = getenv('RAJAONGKIR_ACCOUNT_TYPE');
$verifySSL = getenv('RAJAONGKIR_VERIFY_SSL') === 'true';

if (empty($apiKey)) {
    die("❌ Error: RAJAONGKIR_API_KEY tidak ditemukan di file .env\n");
}

try {
    // Inisialisasi RajaOngkir
    $rajaOngkir = new RajaOngkir($apiKey, $accountType, $verifySSL);
    
    echo "✅ Berhasil terhubung dengan API RajaOngkir (Akun: " . strtoupper($accountType) . ")\n\n";
    
    // Contoh parameter pelacakan
    $waybill = 'NOMOR_RESI_LAINNYA';  // Nomor resi yang valid untuk pengujian
    $courier = 'jne';               // Kode kurir (jne, pos, tiki, dll)
    
    echo "📦 Melacak status pengiriman...\n";
    echo "   Nomor Resi: $waybill\n";
    echo "   Kurir: " . strtoupper($courier) . "\n\n";
    
    // Lacak status pengiriman
    $result = $rajaOngkir->trackWaybill($waybill, $courier);
    
    // Tampilkan hasil
    if (!empty($result) && isset($result['summary'])) {
        echo "✅ Hasil Pelacakan Resi\n";
        echo str_repeat("=", 80) . "\n";
        
        // Informasi pengiriman
        if (isset($result['summary'])) {
            $summary = $result['summary'];
            echo "📦 Informasi Pengiriman\n";
            echo str_repeat("-", 40) . "\n";
            echo "Nomor Resi    : " . ($summary['waybill_number'] ?? '-') . "\n";
            echo "Kurir         : " . strtoupper($courier) . "\n";
            echo "Status        : " . ($summary['status'] ?? '-') . "\n";
            echo "Pengirim      : " . ($summary['shipper_name'] ?? '-') . "\n";
            echo "Penerima      : " . ($summary['receiver_name'] ?? '-') . "\n";
            echo "Asal          : " . ($summary['origin'] ?? '-') . "\n";
            echo "Tujuan        : " . ($summary['destination'] ?? '-') . "\n";
            echo "\n";
        }
        
        // Riwayat pengiriman
        if (isset($result['history']) && is_array($result['history'])) {
            echo "📋 Riwayat Pengiriman\n";
            echo str_repeat("-", 40) . "\n";
            
            foreach ($result['history'] as $index => $history) {
                $date = $history['date'] ?? '';
                $desc = $history['desc'] ?? '';
                $location = $history['location'] ?? '';
                
                echo ($index + 1) . ". " . $date . "\n";
                echo "   " . $desc . "\n";
                if (!empty($location)) {
                    echo "   📍 " . $location . "\n";
                }
                echo "\n";
            }
        } else {
            echo "ℹ️ Tidak ada riwayat pengiriman yang tersedia.\n";
        }
    } else {
        if (isset($result['meta']) && $result['meta']['code'] == 404) {
            echo "❌ Nomor resi tidak valid atau tidak ditemukan. Pastikan nomor resi dan kode kurir benar.\n";
            echo "   Kode Error: " . $result['meta']['code'] . " - " . $result['meta']['message'] . "\n";
        } else {
            echo "❌ Gagal melacak status pengiriman. Pastikan nomor resi dan kode kurir benar.\n";
            if (isset($result['meta'])) {
                echo "   Kode Error: " . $result['meta']['code'] . " - " . $result['meta']['message'] . "\n";
            }
        }
    }
    
} catch (ApiException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Kode Error: " . $e->getCode() . "\n";
    if ($e->getResponse()) {
        echo "Detail: " . print_r($e->getResponse(), true) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Terjadi kesalahan: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "CARA MENGGUNAKAN:\n";
echo "1. Ubah nomor resi dan kode kurir di dalam kode\n";
echo "2. Simpan file dan jalankan: php examples/example-tracking-airwaybills.php\n";
echo "\nDaftar kode kurir yang didukung: jne, pos, tiki, wahana, jnt, sicepat, sap, jet, dse, first, ninja, lion, idl, rex, ide, sentral, anteraja\n";
echo str_repeat("=", 80) . "\n\n";
