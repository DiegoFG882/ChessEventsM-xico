<?php
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// Control básico de intentos fallidos (Prevención de fuerza bruta)
$_SESSION['intentos_login'] = $_SESSION['intentos_login'] ?? 0;
$_SESSION['bloqueo_hasta']  = $_SESSION['bloqueo_hasta'] ?? 0;

if (time() < $_SESSION['bloqueo_hasta']) {
    $segundos = $_SESSION['bloqueo_hasta'] - time();
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => "Demasiados intentos fallidos. Espera $segundos segundos antes de volver a intentar."];
    header('Location: ../login.php');
    exit;
}

$correo = strtolower(trim($_POST['correo'] ?? ''));
$pass   = $_POST['password'] ?? '';

if (empty($correo) || empty($pass)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Ingresa tu correo electrónico y contraseña.'];
    header('Location: ../login.php');
    exit;
}

try {
    $sql = "SELECT u.id_usuario, u.nombre_completo, u.correo, u.password_hash, u.id_rol, r.nombre_rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.id_rol = r.id_rol 
            WHERE LOWER(u.correo) = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($pass, $usuario['password_hash'])) {
        // Restablecer contador de intentos
        $_SESSION['intentos_login'] = 0;
        $_SESSION['bloqueo_hasta']  = 0;

        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre']     = $usuario['nombre_completo'];
        $_SESSION['correo']     = $usuario['correo'];
        $_SESSION['id_rol']     = (int)$usuario['id_rol'];
        $_SESSION['nombre_rol'] = $usuario['nombre_rol'];

        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Bienvenido de nuevo, ' . $usuario['nombre_completo'] . '.'];

        if ($_SESSION['id_rol'] === 1) {
            header('Location: ../admin.php');
        } elseif ($_SESSION['id_rol'] === 2) {
            header('Location: ../mis_torneos.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } else {
        $_SESSION['intentos_login']++;
        if ($_SESSION['intentos_login'] >= 5) {
            $_SESSION['bloqueo_hasta'] = time() + 60; // Bloqueo de 1 minuto
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Has superado el límite de 5 intentos. Intenta nuevamente en 60 segundos.'];
        } else {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Correo o contraseña incorrectos.'];
        }
        header('Location: ../login.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Error temporal al verificar tus credenciales.'];
    header('Location: ../login.php');
    exit;
}