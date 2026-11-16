<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$accion    = $_POST['accion'] ?? '';

try {
    if ($accion === 'actualizar_datos') {
        $nombre   = trim($_POST['nombre_completo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $idEstado = (int)($_POST['id_estado'] ?? 0);
        $idFide   = trim($_POST['id_fide'] ?? '');
        $elo      = (int)($_POST['elo_actual'] ?? 0);

        if (empty($nombre) || mb_strlen($nombre) > 100 || !preg_match('/^[0-9]{10}$/', $telefono) || $idEstado === 0) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Verifica que tu nombre (máx. 100 caracteres), teléfono (10 dígitos) y estado sean válidos.'];
            header('Location: ../perfil.php');
            exit;
        }

        $sql = "UPDATE usuarios SET nombre_completo = ?, telefono = ?, id_estado = ?, id_fide = ?, elo_actual = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $telefono, $idEstado, $idFide ?: null, max(0, min(3000, $elo)), $idUsuario]);

        $_SESSION['nombre'] = $nombre;
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Tus datos de perfil se actualizaron correctamente.'];
        header('Location: ../perfil.php');
        exit;

    } elseif ($accion === 'cambiar_password') {
        $actual  = $_POST['pass_actual'] ?? '';
        $nueva   = $_POST['pass_nueva'] ?? '';
        $confirm = $_POST['pass_confirm'] ?? '';

        if ($nueva !== $confirm) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'La nueva contraseña y su confirmación no coinciden.'];
            header('Location: ../perfil.php');
            exit;
        }

        if (strlen($nueva) < 8 || !preg_match('/[A-Za-z]/', $nueva) || !preg_match('/[0-9]/', $nueva)) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'La nueva contraseña debe tener al menos 8 caracteres e incluir letras y números.'];
            header('Location: ../perfil.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($actual, $user['password_hash'])) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Tu contraseña actual es incorrecta.'];
            header('Location: ../perfil.php');
            exit;
        }

        $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?")->execute([$nuevoHash, $idUsuario]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Contraseña actualizada con éxito.'];
        header('Location: ../perfil.php');
        exit;

    } elseif ($accion === 'eliminar_cuenta') {
        if ($_SESSION['id_rol'] === 1) {
            $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'La cuenta de Administrador principal no puede eliminarse desde aquí.'];
            header('Location: ../perfil.php');
            exit;
        }

        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$idUsuario]);
        session_unset();
        session_destroy();

        session_start();
        $_SESSION['alerta'] = ['tipo' => 'info', 'mensaje' => 'Tu cuenta y todos tus datos han sido eliminados permanentemente.'];
        header('Location: ../index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Ocurrió un error al procesar tu solicitud.'];
    header('Location: ../perfil.php');
    exit;
}