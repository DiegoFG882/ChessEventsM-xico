<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$idTorneo  = (int)($_POST['id_torneo'] ?? 0);
$idUsuario = $_SESSION['id_usuario'];
$idRol     = $_SESSION['id_rol'];

$stmt = $pdo->prepare("SELECT id_organizador FROM torneos WHERE id_torneo = ?");
$stmt->execute([$idTorneo]);
$torneo = $stmt->fetch();

if (!$torneo) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El torneo no existe.'];
    header('Location: ../index.php');
    exit;
}

if ($torneo['id_organizador'] == $idUsuario || $idRol === 1) {
    $stmtDel = $pdo->prepare("DELETE FROM torneos WHERE id_torneo = ?");
    $stmtDel->execute([$idTorneo]);
    $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Torneo eliminado de forma permanente.'];
} else {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No tienes permiso para eliminar este torneo.'];
}

$destino = ($idRol === 1) ? '../admin.php' : '../mis_torneos.php';
header("Location: $destino");
exit;