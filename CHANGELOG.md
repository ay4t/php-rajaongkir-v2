# Changelog

Semua perubahan penting pada proyek ini akan didokumentasikan dalam file ini.

Format berdasarkan pada [Keep a Changelog](https://keepachangelog.com/id/1.0.0/),
dan proyek ini berpegang pada [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Ditambahkan
- Integrasi dengan API RajaOngkir V2
- Dukungan untuk semua endpoint utama:
  - Mendapatkan daftar provinsi dan kota
  - Pencarian tujuan domestik dan internasional
  - Perhitungan biaya pengiriman
  - Pelacakan resi pengiriman
- Dukungan untuk berbagai tipe akun (Starter, Basic, Pro)
- Sistem konfigurasi yang fleksibel
- Dokumentasi lengkap dengan contoh penggunaan
- Test suite untuk memastikan kualitas kode

### Diubah
- Perbaikan struktur direktori untuk mengikuti standar PSR-4
- Peningkatan penanganan error dan exception
- Optimasi performa untuk pencarian lokal

### Diperbaiki
- Perbaikan bug pada pencarian tujuan domestik
- Perbaikan validasi parameter
- Perbaikan kompatibilitas dengan PHP 7.4+

## [0.1.0] - 2025-06-22
### Ditambahkan
- Rilis awal library PHP RajaOngkir V2
- Dukungan untuk operasi dasar:
  - Mendapatkan daftar provinsi
  - Mendapatkan daftar kota
  - Pencarian tujuan
  - Perhitungan ongkos kirim
  - Pelacakan resi

[Unreleased]: https://github.com/ay4t/php-rajaongkir-v2/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/ay4t/php-rajaongkir-v2/releases/tag/v0.1.0
