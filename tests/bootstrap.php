<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} else {
    // Set default values for CI environment
    $_ENV['RAJAONGKIR_API_KEY'] = $_ENV['RAJAONGKIR_API_KEY'] ?? 'test_api_key';
    $_ENV['RAJAONGKIR_ACCOUNT_TYPE'] = $_ENV['RAJAONGKIR_ACCOUNT_TYPE'] ?? 'starter';
    $_ENV['RAJAONGKIR_VERIFY_SSL'] = $_ENV['RAJAONGKIR_VERIFY_SSL'] ?? 'true';
}

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Asia/Jakarta');
