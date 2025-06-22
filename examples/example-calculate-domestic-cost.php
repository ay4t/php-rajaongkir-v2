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
    
    // Contoh parameter perhitungan ongkos kirim
    $params = [
        'origin' => '501', // ID kota asal (Yogyakarta)
        'destination' => '114', // ID kota tujuan (Bekasi)
        'weight' => 1000, // Berat dalam gram
        'courier' => 'jne', // Kode kurir (jne, pos, tiki, dll)
    ];
    
    echo "📦 Menghitung ongkos kirim...\n";
    echo "   Asal: Yogyakarta (ID: 501)\n";
    echo "   Tujuan: Bekasi (ID: 114)\n";
    echo "   Berat: 1000 gram\n";
    echo "   Kurir: JNE\n\n";
    
    // Hitung ongkos kirim
    $result = $rajaOngkir->calculateDomesticCost($params);
    
    // Tampilkan hasil
    if (!empty($result)) {
        echo "✅ Hasil Perhitungan Ongkos Kirim\n";
        echo str_repeat("=", 80) . "\n";
        
        $currentCourier = '';
        
        foreach ($result as $item) {
            if (!isset($item['code'])) continue;
            
            // Tampilkan header kurir jika berbeda
            if ($currentCourier !== $item['name']) {
                if ($currentCourier !== '') echo "\n";
                echo "📦 Kurir: " . $item['name'] . " (" . strtoupper($item['code']) . ")\n";
                echo str_repeat("-", 40) . "\n";
                $currentCourier = $item['name'];
            }
            
            $serviceName = $item['service'] ?? 'Lainnya';
            $description = $item['description'] ?? '';
            $etd = $item['etd'] ?? '';
            $cost = $item['cost'] ?? 0;
            
            echo "🔹 Layanan: $serviceName";
            echo !empty($description) ? " ($description)" : "";
            echo "\n";
            
            echo "   • Estimasi: $etd\n";
            echo "   • Biaya: Rp " . number_format($cost, 0, ',', '.') . "\n\n";
        }
    } else {
        echo "❌ Tidak ada layanan pengiriman yang tersedia untuk rute ini.\n";
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
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "CARA MENGGUNAKAN:\n";
echo "1. Ubah parameter asal, tujuan, berat, dan kurir di dalam kode\n";
echo "2. Simpan file dan jalankan: php examples/example-calculate-domestic-cost.php\n";
echo str_repeat("=", 80) . "\n\n";
