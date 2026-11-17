<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Acceso denegado. Área exclusiva para administradores.'];
    header('Location: ../index.php');
    exit;
}

$accion    = $_POST['accion'] ?? '';
$idUsuario = (int)($_POST['id_usuario'] ?? 0);

// Evitar que el administrador se borre o se quite permisos a sí mismo por accidente
if ($idUsuario === $_SESSION['id_usuario']) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'No puedes modificar o eliminar tu propia cuenta de administrador activa.'];
    header('Location: ../admin.php');
    exit;
}

if ($accion === 'cambiar_rol') {
    $nuevoRol = (int)($_POST['id_rol'] ?? 3);
    if (in_array($nuevoRol, [1, 2, 3])) {
        $stmt = $pdo->prepare("UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?");
        $stmt->execute([$nuevoRol, $idUsuario]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Rol de usuario actualizado correctamente.'];
    }
} elseif ($accion === 'eliminar_usuario') {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$idUsuario]);
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Usuario eliminado del sistema.'];
}

header('Location: ../admin.php');
exit;