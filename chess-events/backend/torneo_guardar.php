<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2]) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$idTorneo     = (int)($_POST['id_torneo'] ?? 0);
$nombre       = trim($_POST['nombre'] ?? '');
$idEstado     = (int)($_POST['id_estado'] ?? 0);
$direccion    = trim($_POST['direccion'] ?? '');
$urlMaps      = trim($_POST['url_maps'] ?? '');
$idRitmo      = (int)($_POST['id_ritmo'] ?? 0);
$ritmoDetalle = trim($_POST['ritmo_detalle'] ?? '');
$rondas       = (int)($_POST['rondas'] ?? 5);
$fecha        = trim($_POST['fecha_evento'] ?? '');
$hora         = trim($_POST['hora_inicio'] ?? '');
$cupo         = (int)($_POST['cupo'] ?? 50);
$esGratis     = isset($_POST['es_gratis']);
$costo        = $esGratis ? 0.00 : max(0, (float)($_POST['costo'] ?? 0));
$esFide       = isset($_POST['es_fide']) ? 'true' : 'false';
$whatsapp     = trim($_POST['whatsapp_contacto'] ?? '');
$redSocial    = trim($_POST['red_social'] ?? '');

// Validar campos obligatorios
if (empty($nombre) || $idEstado === 0 || empty($direccion) || $idRitmo === 0 || empty($ritmoDetalle) || empty($fecha) || empty($hora) || empty($whatsapp)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Completa todos los campos obligatorios (*) del torneo.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}

if (mb_strlen($nombre) > 150) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El nombre del torneo no puede exceder los 150 caracteres.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $whatsapp)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El WhatsApp de informes debe tener exactamente 10 dígitos numéricos.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}

// Impedir fechas pasadas al crear un torneo nuevo
if ($idTorneo === 0 && $fecha < date('Y-m-d')) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'La fecha del torneo no puede ser anterior al día de hoy.'];
    header('Location: ../formulario_torneo.php');
    exit;
}

if ($cupo < 2 || $cupo > 2000) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El cupo máximo debe estar entre 2 y 2000 jugadores.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}

// Procesar archivo adjunto (input name="convocatoria", máx. 4 MB)
$archivoBase64 = null;
$archivoNombre = null;
$archivoTipo   = null;
$hayArchivo    = false;

if (isset($_FILES['convocatoria']) && $_FILES['convocatoria']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['convocatoria'];
    if ($file['size'] > 4 * 1024 * 1024) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El archivo adjunto no puede superar los 4 MB.'];
        header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
        exit;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Solo se permiten archivos PDF, JPG, PNG o WEBP.'];
        header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
        exit;
    }

    $binario       = file_get_contents($file['tmp_name']);
    $archivoBase64 = base64_encode($binario);
    $archivoNombre = $file['name'];
    $archivoTipo   = $file['type'];
    $hayArchivo    = true;
}

try {
    if ($idTorneo > 0) {
        // Verificar permisos y que el nuevo cupo no sea menor a los ya inscritos
        $stmtCheck = $pdo->prepare("
            SELECT t.id_organizador, (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo) AS inscritos
            FROM torneos t WHERE t.id_torneo = ?
        ");
        $stmtCheck->execute([$idTorneo]);
        $actual = $stmtCheck->fetch();

        if (!$actual || ($_SESSION['id_rol'] !== 1 && $actual['id_organizador'] != $_SESSION['id_usuario'])) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No tienes permiso para modificar este torneo.'];
            header('Location: ../mis_torneos.php');
            exit;
        }

        if ($cupo < (int)$actual['inscritos']) {
            $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => "No puedes reducir el cupo a $cupo porque ya hay {$actual['inscritos']} jugadores inscritos."];
            header("Location: ../formulario_torneo.php?id=$idTorneo");
            exit;
        }

        if ($hayArchivo) {
            $sql = "UPDATE torneos 
                    SET id_estado=?, id_ritmo=?, nombre=?, direccion=?, url_maps=?, ritmo_detalle=?, rondas=?, fecha_evento=?, hora_inicio=?, cupo=?, costo=?, es_fide=?, whatsapp_contacto=?, red_social=?, archivo_base64=?, archivo_nombre=?, archivo_tipo=? 
                    WHERE id_torneo=?";
            $params = [$idEstado, $idRitmo, $nombre, $direccion, $urlMaps ?: null, $ritmoDetalle, $rondas, $fecha, $hora, $cupo, $costo, $esFide, $whatsapp, $redSocial ?: null, $archivoBase64, $archivoNombre, $archivoTipo, $idTorneo];
        } else {
            $sql = "UPDATE torneos 
                    SET id_estado=?, id_ritmo=?, nombre=?, direccion=?, url_maps=?, ritmo_detalle=?, rondas=?, fecha_evento=?, hora_inicio=?, cupo=?, costo=?, es_fide=?, whatsapp_contacto=?, red_social=? 
                    WHERE id_torneo=?";
            $params = [$idEstado, $idRitmo, $nombre, $direccion, $urlMaps ?: null, $ritmoDetalle, $rondas, $fecha, $hora, $cupo, $costo, $esFide, $whatsapp, $redSocial ?: null, $idTorneo];
        }

        $pdo->prepare($sql)->execute($params);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Los cambios del torneo se guardaron correctamente!'];
    } else {
        $sql = "INSERT INTO torneos (id_organizador, id_estado, id_ritmo, nombre, direccion, url_maps, ritmo_detalle, rondas, fecha_evento, hora_inicio, cupo, costo, es_fide, whatsapp_contacto, red_social, archivo_base64, archivo_nombre, archivo_tipo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $_SESSION['id_usuario'], $idEstado, $idRitmo, $nombre, $direccion, $urlMaps ?: null, $ritmoDetalle, $rondas, $fecha, $hora, $cupo, $costo, $esFide, $whatsapp, $redSocial ?: null, $archivoBase64, $archivoNombre, $archivoTipo
        ]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Torneo publicado en la cartelera nacional!'];
    }

    header('Location: ' . ($_SESSION['id_rol'] === 1 ? '../admin.php' : '../mis_torneos.php'));
    exit;

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Ocurrió un error al guardar el torneo en la base de datos.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}