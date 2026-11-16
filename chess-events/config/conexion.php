<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si estamos en la nube (Render/Railway), toma la URL automática; si no, usa tus datos locales
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    $db = parse_url($databaseUrl);
    $host = $db['host'];
    $port = $db['port'] ?? 5432;
    $dbname = ltrim($db['path'], '/');
    $user = $db['user'];
    $pass = $db['pass'];
} else {
    // Configuración para pruebas locales en tu PC
    $host = 'localhost';
    $port = '5432';
    $dbname = 'chessevents';
    $user = 'postgres';
    $pass = '22052007';
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión a PostgreSQL: " . $e->getMessage());
}
?>