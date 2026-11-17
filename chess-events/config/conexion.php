<?php
// Ocultar errores técnicos en pantalla para no exponer rutas del servidor
ini_set('display_errors', '0');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar zona horaria de México
date_default_timezone_set('America/Mexico_City');

$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl !== false && $databaseUrl !== '') {
    // 1. ENTORNO EN LA NUBE (Render.com lee la variable DATABASE_URL)
    $db = parse_url($databaseUrl);
    $host   = $db['host'];
    $port   = $db['port'] ?? 5432;
    $dbname = ltrim($db['path'], '/');
    $user   = $db['user'];
    $pass   = $db['pass'];
    $dsn    = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=prefer";
} else {
    // 2. ENTORNO LOCAL (Tu computadora con XAMPP / pgAdmin)
    $host   = 'localhost';
    $port   = '5432';
    $dbname = 'chessevents';
    $user   = 'postgres';
    $pass   = 'TU_CONTRASEÑA_LOCAL'; // <-- Pon aquí la clave de tu PostgreSQL local
    $dsn    = "pgsql:host=$host;port=$port;dbname=$dbname";
}

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Error de conexión con la base de datos. Intenta más tarde.');
}