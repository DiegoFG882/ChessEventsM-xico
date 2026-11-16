<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Debes iniciar sesión como jugador para inscribirte.'];
    header('Location: ../login.php');
    exit;
}

$accion    = $_POST['accion'] ?? '';
$idTorneo  = (int)($_POST['id_torneo'] ?? 0);
$idJugador = $_SESSION['id_usuario'];
$categoria = trim($_POST['categoria'] ?? 'Libre');

if ($accion === 'inscribir') {
    // Evitar inscripción duplicada
    $stmtCheck = $pdo->prepare("SELECT id_inscripcion FROM inscripciones WHERE id_torneo = ? AND id_jugador = ?");
    $stmtCheck->execute([$idTorneo, $idJugador]);

    if ($stmtCheck->fetch()) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Ya te encuentras inscrito en este torneo.'];
    } else {
        $stmtIns = $pdo->prepare("INSERT INTO inscripciones (id_torneo, id_jugador, categoria) VALUES (?, ?, ?)");
        $stmtIns->execute([$idTorneo, $idJugador, $categoria]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Inscripción confirmada! Tu lugar ha sido apartado.'];
    }
    header("Location: ../detalles.php?id=$idTorneo");
    exit;

} elseif ($accion === 'cancelar') {
    $idInscripcion = (int)($_POST['id_inscripcion'] ?? 0);
    $stmtDel = $pdo->prepare("DELETE FROM inscripciones WHERE id_inscripcion = ? AND id_jugador = ?");
    $stmtDel->execute([$idInscripcion, $idJugador]);

    $_SESSION['alerta'] = ['tipo' => 'info', 'mensaje' => 'Has cancelado tu inscripción al torneo.'];
    header('Location: ../mis_inscripciones.php');
    exit;
}

header('Location: ../index.php');
exit;