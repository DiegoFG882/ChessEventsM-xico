<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$accion    = $_POST['accion'] ?? '';
$idUsuario = (int)$_SESSION['id_usuario'];
$idRol     = (int)$_SESSION['id_rol'];

try {
    if ($accion === 'inscribir') {
        if ($idRol !== 3) {
            $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Solo las cuentas con rol de Jugador pueden inscribirse a torneos.'];
            header('Location: ../index.php');
            exit;
        }

        $idTorneo  = (int)($_POST['id_torneo'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? 'Libre');

        // Verificar que el torneo exista, su fecha, cupo y total de inscritos
        $stmtT = $pdo->prepare("
            SELECT t.fecha_evento, t.cupo,
                   (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo) AS inscritos
            FROM torneos t 
            WHERE t.id_torneo = ?
        ");
        $stmtT->execute([$idTorneo]);
        $torneo = $stmtT->fetch();

        if (!$torneo) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El torneo seleccionado no existe.'];
            header('Location: ../index.php');
            exit;
        }

        if ($torneo['fecha_evento'] < date('Y-m-d')) {
            $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Las inscripciones para este torneo ya están cerradas.'];
            header("Location: ../detalles.php?id=$idTorneo");
            exit;
        }

        // Evitar doble inscripción
        $stmtCheck = $pdo->prepare("SELECT id_inscripcion FROM inscripciones WHERE id_torneo = ? AND id_jugador = ?");
        $stmtCheck->execute([$idTorneo, $idUsuario]);

        if ($stmtCheck->fetch()) {
            $_SESSION['alerta'] = ['tipo' => 'info', 'mensaje' => 'Ya te encuentras inscrito en este torneo.'];
            header("Location: ../detalles.php?id=$idTorneo");
            exit;
        }

        // Bloquear si ya se alcanzó el cupo máximo
        if ((int)$torneo['inscritos'] >= (int)$torneo['cupo']) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Cupo lleno. Este torneo ha alcanzado su capacidad máxima de jugadores.'];
            header("Location: ../detalles.php?id=$idTorneo");
            exit;
        }

        $stmtIns = $pdo->prepare("INSERT INTO inscripciones (id_torneo, id_jugador, categoria) VALUES (?, ?, ?)");
        $stmtIns->execute([$idTorneo, $idUsuario, $categoria]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Inscripción confirmada! Tu lugar ha sido registrado en la lista oficial.'];
        header("Location: ../detalles.php?id=$idTorneo");
        exit;

    } elseif ($accion === 'cancelar') {
        $idInscripcion = (int)($_POST['id_inscripcion'] ?? 0);

        // Si es Administrador (id_rol = 1), puede cancelar cualquier inscripción desde admin.php
        if ($idRol === 1) {
            $stmtDel = $pdo->prepare("DELETE FROM inscripciones WHERE id_inscripcion = ?");
            $stmtDel->execute([$idInscripcion]);

            $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Inscripción del jugador cancelada por el administrador.'];
            header('Location: ../admin.php');
            exit;
        }

        // Si es Jugador, solo puede cancelar sus propias inscripciones
        $stmtDel = $pdo->prepare("DELETE FROM inscripciones WHERE id_inscripcion = ? AND id_jugador = ?");
        $stmtDel->execute([$idInscripcion, $idUsuario]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Has cancelado tu inscripción al torneo.'];
        header('Location: ../mis_inscripciones.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo procesar la operación de inscripción.'];
    header('Location: ../index.php');
    exit;
}