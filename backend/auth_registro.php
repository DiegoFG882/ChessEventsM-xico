<?php
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit;
}

$_SESSION['form_old'] = [
    'nombre_completo' => $_POST['nombre_completo'] ?? '',
    'correo'          => $_POST['correo'] ?? '',
    'telefono'        => $_POST['telefono'] ?? '',
    'id_estado'       => $_POST['id_estado'] ?? '',
    'id_rol'          => $_POST['id_rol'] ?? 3,
    'id_fide'         => $_POST['id_fide'] ?? '',
    'elo_actual'      => $_POST['elo_actual'] ?? 0,
];

$nombre   = trim($_POST['nombre_completo'] ?? '');
$correo   = strtolower(trim($_POST['correo'] ?? ''));
$telefono = trim($_POST['telefono'] ?? '');
$idEstado = (int)($_POST['id_estado'] ?? 0);
$idRol    = (int)($_POST['id_rol'] ?? 3);
$idFide   = trim($_POST['id_fide'] ?? '');
$elo      = (int)($_POST['elo_actual'] ?? 0);
$pass     = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if (!in_array($idRol, [2, 3])) {
    $idRol = 3;
}

if (empty($nombre) || empty($correo) || empty($telefono) || $idEstado === 0 || empty($pass)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Todos los campos obligatorios (*) deben completarse.'];
    header('Location: ../registro.php');
    exit;
}

if (mb_strlen($nombre) > 100) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El nombre completo es demasiado largo (máximo 100 caracteres).'];
    header('Location: ../registro.php');
    exit;
}

if (mb_strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Ingresa un correo electrónico válido (máximo 100 caracteres).'];
    header('Location: ../registro.php');
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $telefono)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El teléfono debe contener exactamente 10 dígitos numéricos.'];
    header('Location: ../registro.php');
    exit;
}

if (mb_strlen($idFide) > 20) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El ID FIDE no puede exceder los 20 caracteres.'];
    header('Location: ../registro.php');
    exit;
}

if ($elo < 0 || $elo > 3000) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El rating Elo debe estar entre 0 y 3000.'];
    header('Location: ../registro.php');
    exit;
}

if ($pass !== $confirm) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Las contraseñas no coinciden. Inténtalo de nuevo.'];
    header('Location: ../registro.php');
    exit;
}

if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'La contraseña debe tener al menos 8 caracteres e incluir letras y números.'];
    header('Location: ../registro.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE LOWER(correo) = ?");
    $stmt->execute([$correo]);

    if ($stmt->fetch()) {
        unset($_SESSION['form_old']);
        $_SESSION['correo_login'] = $correo;
        $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Ese correo electrónico ya está registrado. Inicia sesión con tu cuenta.'];
        header('Location: ../login.php');
        exit;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (id_rol, id_estado, nombre_completo, correo, telefono, id_fide, elo_actual, password_hash) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sql);
    $stmtInsert->execute([$idRol, $idEstado, $nombre, $correo, $telefono, $idFide ?: null, $elo, $hash]);

    unset($_SESSION['form_old']);
    $_SESSION['correo_login'] = $correo;
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Cuenta creada con éxito. Ahora puedes iniciar sesión.'];
    header('Location: ../login.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo completar el registro. Verifica tus datos e inténtalo de nuevo.'];
    header('Location: ../registro.php');
    exit;
}