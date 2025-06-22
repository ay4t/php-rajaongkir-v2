Library PHP untuk mengintegrasikan dengan API RajaOngkir V2. Library ini menyediakan antarmuka yang mudah digunakan untuk mengakses berbagai fitur yang disediakan oleh RajaOngkir V2, termasuk perhitungan ongkos kirim domestik, internasional, dan pelacakan resi.

## 📋 Daftar Isi

- [Fitur](#-fitur)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Penggunaan](#-penggunaan)
  - [Inisialisasi](#-inisialisasi)
  - [Mengambil Data Provinsi](#-mengambil-data-provinsi)
  - [Mengambil Data Kota/Kabupaten](#-mengambil-data-kotakabupaten)
  - [Mencari Tujuan Domestik](#-mencari-tujuan-domestik)
  - [Menghitung Biaya Pengiriman](#-menghitung-biaya-pengiriman)
  - [Melacak Pengiriman](#-melacak-pengiriman)
- [Testing](#-testing)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)
- [Dukungan](#-dukungan)

## ✨ Fitur

- 🚀 Dukungan penuh untuk API RajaOngkir V2
- 🔑 Mudah diintegrasikan dengan framework PHP populer (Laravel, CodeIgniter, dll)
- 📦 Dikemas dengan Composer
- 🧪 Dilengkapi dengan unit test
- 📝 Dokumentasi yang lengkap
- 🔄 Dukungan untuk berbagai tipe akun (Starter, Basic, Pro)
- 🛡️ Penanganan error yang baik

## 🛠️ Persyaratan Sistem

- PHP 7.4 atau lebih baru
- Ekstensi PHP cURL
- Composer (untuk instalasi)
- Akun RajaOngkir (Starter, Basic, atau Pro)
- API Key dari [RajaOngkir](https://rajaongkir.com/)

## 📥 Instalasi

### Menggunakan Composer (Direkomendasikan)

```bash
composer require ay4t/php-rajaongkir-v2
```

### Instalasi Manual

1. Unduh atau clone repository ini
2. Masuk ke direktori proyek
3. Jalankan:
   ```bash
   composer install
   ```

## ⚙️ Konfigurasi

### Menggunakan Environment Variables

Buat file `.env` di root direktori proyek Anda:

```env
RAJAONGKIR_API_KEY=your_api_key_here
RAJAONGKIR_ACCOUNT_TYPE=starter # atau basic, pro
RAJAONGKIR_VERIFY_SSL=true # verifikasi SSL (true/false)
```

### Konfigurasi Manual

```php
use RajaOngkir\RajaOngkir;

// Inisialisasi dengan API Key dan tipe akun
$rajaOngkir = new RajaOngkir(
    'your_api_key_here',  // API Key dari RajaOngkir
    'starter',            // Tipe akun: starter, basic, atau pro
    true                 // Verifikasi SSL (opsional, default: true)
);
```

## 🚀 Penggunaan

### 🔹 Inisialisasi

```php
<?php

require 'vendor/autoload.php';

use RajaOngkir\RajaOngkir;

// Menggunakan environment variable
$rajaOngkir = new RajaOngkir();

### Contoh Penggunaan

#### 1. Mendapatkan Data Provinsi dan Kota

```php
try {
    // Dapatkan semua provinsi
    $provinces = $rajaOngkir->getProvinces();
    
    // Dapatkan provinsi berdasarkan ID
    $province = $rajaOngkir->getProvince(6); // DKI Jakarta
    
    // Dapatkan semua kota
    $cities = $rajaOngkir->getCities();
    
    // Dapatkan kota berdasarkan ID
    $city = $rajaOngkir->getCity(152); // Jakarta Pusat
    
} catch (\RajaOngkir\Exceptions\ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

#### 2. Pencarian Tujuan Pengiriman

```php
try {
    // Cari tujuan dalam negeri
    $domesticResults = $rajaOngkir->searchDomesticDestinations('solo', 5);
    
    // Cari tujuan internasional
    $internationalResults = $rajaOngkir->searchInternationalDestinations('singapore', 5);
    
} catch (\RajaOngkir\Exceptions\ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

#### 3. Menghitung Biaya Pengiriman

```php
try {
    // Hitung biaya pengiriman dalam negeri
    $params = [
        'origin' => '501',      // ID kota asal (Yogyakarta)
        'destination' => '114', // ID kota tujuan (Jakarta Pusat)
        'weight' => 1000,       // berat (gram)
        'courier' => 'jne',     // kode kurir
        'originType' => 'city', // tipe asal (city/subdistrict)
        'destinationType' => 'city' // tipe tujuan (city/subdistrict)
    ];
    
    $domesticCost = $rajaOngkir->calculateDomesticCost($params);
    
    // Hitung biaya pengiriman internasional
    $intlParams = [
        'origin' => '501',      // ID kota asal
        'destination' => '233', // ID negara tujuan (Malaysia)
        'weight' => 1000,       // berat (gram)
        'courier' => 'pos'      // kode kurir
    ];
    
    $intlCost = $rajaOngkir->calculateInternationalCost($intlParams);
    
    print_r($domesticCost);
    print_r($intlCost);
    
} catch (\RajaOngkir\Exceptions\ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

#### 4. Melacak Pengiriman

```php
try {
    // Lacak nomor resi
    $waybillNumber = 'TLJR359E6MGG28L7'; // Ganti dengan nomor resi yang valid
    $courier = 'jne'; // Kode kurir (jne, pos, tiki, dll)
    
    $tracking = $rajaOngkir->trackWaybill($waybillNumber, $courier);
    
    // Hasil pelacakan
    if (!empty($tracking['summary'])) {
        echo "📦 Informasi Pengiriman\n";
        echo "Nomor Resi    : " . ($tracking['summary']['waybill_number'] ?? '-') . "\n";
        echo "Kurir         : " . strtoupper($tracking['summary']['courier_code'] ?? '') . "\n";
        echo "Status        : " . ($tracking['delivery_status']['status'] ?? '-') . "\n";
        echo "Pengirim      : " . ($tracking['summary']['shipper_name'] ?? '-') . "\n";
        echo "Penerima      : " . ($tracking['summary']['receiver_name'] ?? '-') . "\n";
        echo "Asal          : " . ($tracking['summary']['origin'] ?? '-') . "\n";
        echo "Tujuan        : " . ($tracking['summary']['destination'] ?? '-') . "\n";
        
        // Tampilkan riwayat pengiriman jika ada
        if (!empty($tracking['manifest'])) {
            echo "\n🔄 Riwayat Pengiriman\n";
            foreach ($tracking['manifest'] as $history) {
                echo "- [" . $history['manifest_date'] . ' ' . $history['manifest_time'] . "] " . 
                     $history['manifest_description'] . "\n";
            }
        }
    } else {
        echo "Tidak dapat menemukan informasi pengiriman.\n";
    }
    
} catch (\RajaOngkir\Exceptions\ApiException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## 🧪 Testing

Untuk memastikan kode berjalan dengan baik, jalankan test suite:

```bash
# Install dependencies pengembangan
composer install --dev

# Menjalankan PHPUnit tests
composer test

# Menjalankan PHPStan untuk static analysis
composer stan

# Menjalankan PHP_CodeSniffer untuk pengecekan standar kode
composer cs-check

# Memperbaiki standar kode secara otomatis (jika memungkinkan)
composer cs-fix
```

## 🤝 Kontribusi

Kontribusi sangat diterima! Berikut cara Anda bisa berkontribusi:

1. Fork repository ini
2. Buat branch untuk fitur Anda (`git checkout -b fitur/namafitur`)
3. Commit perubahan Anda (`git commit -am 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin fitur/namafitur`)
5. Buat Pull Request

### Panduan Kontribusi

- Ikuti standar kode PSR-12
- Tulis test untuk kode baru
- Pastikan semua test lulus
- Perbarui dokumentasi jika diperlukan
- Gunakan pesan commit yang deskriptif

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## ❓ Dukungan

Jika Anda menemukan bug atau memiliki pertanyaan, silakan buat [issue baru](https://github.com/ay4t/php-rajaongkir-v2/issues) di GitHub.

---
