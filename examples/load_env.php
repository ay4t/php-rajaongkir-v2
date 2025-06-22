<?php
/**
 * Helper untuk memuat variabel environment dari file .env
 */
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Abaikan komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Pisahkan nama dan nilai
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Hapus tanda kutip jika ada
        if (strpos($value, '"') === 0 && substr($value, -1) === '"' ||
            strpos($value, "'") === 0 && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
        }
        
        // Set environment variable
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
    
    return true;
}

// Coba muat dari file .env di direktori root
if (!loadEnv(__DIR__ . '/../.env')) {
    die("⚠️ File .env tidak ditemukan. Pastikan file .env sudah ada di direktori root.\n");
}

// Pastikan variabel yang diperlukan tersedia
if (!getenv('RAJAONGKIR_API_KEY')) {
    die("⚠️ RAJAONGKIR_API_KEY tidak ditemukan di file .env\n");
}

if (!getenv('RAJAONGKIR_ACCOUNT_TYPE')) {
    putenv('RAJAONGKIR_ACCOUNT_TYPE=pro');
    $_ENV['RAJAONGKIR_ACCOUNT_TYPE'] = 'pro';
}

echo "✅ Berhasil memuat konfigurasi dari file .env\n";
echo "🔑 Tipe Akun: " . strtoupper(getenv('RAJAONGKIR_ACCOUNT_TYPE')) . "\n";
echo "🔒 API Key: " . substr(getenv('RAJAONGKIR_API_KEY'), 0, 5) . str_repeat('*', max(0, strlen(getenv('RAJAONGKIR_API_KEY')) - 5)) . "\n\n";
