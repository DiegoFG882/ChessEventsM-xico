<?php
require_once '../config/conexion.php';

// Verificar sesión y permiso (Solo Organizador = 2 o Admin = 1)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2])) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No tienes permisos para publicar o editar torneos.'];
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../mis_torneos.php');
    exit;
}

$idTorneo      = !empty($_POST['id_torneo']) ? (int)$_POST['id_torneo'] : null;
$idUsuario     = $_SESSION['id_usuario'];
$nombre        = trim($_POST['nombre'] ?? '');
$idEstado      = (int)($_POST['id_estado'] ?? 0);
$idRitmo       = (int)($_POST['id_ritmo'] ?? 0);
$direccion     = trim($_POST['direccion'] ?? '');
$urlMaps       = trim($_POST['url_maps'] ?? '');
$ritmoDet      = trim($_POST['ritmo_detalle'] ?? '');
$rondas        = (int)($_POST['rondas'] ?? 5);
$fecha         = $_POST['fecha_evento'] ?? '';
$hora          = $_POST['hora_inicio'] ?? '';
$esGratis      = isset($_POST['es_gratis']);
$costo         = $esGratis ? 0.00 : (float)($_POST['costo'] ?? 0);
$esFide        = isset($_POST['es_fide']) ? 'true' : 'false';
$whatsapp      = trim($_POST['whatsapp_contacto'] ?? '');
$redSocial     = trim($_POST['red_social'] ?? '');
$archivoNombre = !empty($_FILES['convocatoria']['name']) ? $_FILES['convocatoria']['name'] : null;

if (empty($nombre) || $idEstado === 0 || $idRitmo === 0 || empty($direccion) || empty($fecha) || empty($hora) || empty($whatsapp)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Por favor completa todos los campos obligatorios del torneo.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}
if ($fecha < date('Y-m-d')) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'La fecha del torneo no puede ser anterior al día de hoy.'];
    header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
    exit;
}
// Procesar archivo de convocatoria (PDF o Imagen) a Base64 para persistencia en BD
$archivoBase64 = null;
if (isset($_FILES['convocatoria']) && $_FILES['convocatoria']['error'] === UPLOAD_ERR_OK) {
    $tipoMime = mime_content_type($_FILES['convocatoria']['tmp_name']);
    $permitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
    
    if (!in_array($tipoMime, $permitidos)) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Formato no válido. Sube únicamente PDF, JPG o PNG.'];
        header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
        exit;
    }

    if ($_FILES['convocatoria']['size'] > 4 * 1024 * 1024) { // Máximo 4 MB
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'El archivo es muy pesado (máximo 4 MB).'];
        header('Location: ../formulario_torneo.php' . ($idTorneo ? "?id=$idTorneo" : ''));
        exit;
    }

    $contenidoBinario = file_get_contents($_FILES['convocatoria']['tmp_name']);
    $archivoBase64 = 'data:' . $tipoMime . ';base64,' . base64_encode($contenidoBinario);
}

if ($idTorneo) {
    // MODO EDICIÓN (UPDATE) - Verificar que sea dueño del torneo o Admin
    $stmtCheck = $pdo->prepare("SELECT id_organizador, archivo_contenido, archivo_nombre FROM torneos WHERE id_torneo = ?");
    $stmtCheck->execute([$idTorneo]);
    $torneoActual = $stmtCheck->fetch();

    if (!$torneoActual || ($torneoActual['id_organizador'] != $idUsuario && $_SESSION['id_rol'] !== 1)) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No puedes editar un torneo que no te pertenece.'];
        header('Location: ../mis_torneos.php');
        exit;
    }

    // Si no subió archivo nuevo, conservamos el contenido y el nombre anteriores
    $contenidoFinal = $archivoBase64 ?? $torneoActual['archivo_contenido'];
    $nombreFinal    = $archivoNombre ?? $torneoActual['archivo_nombre'];

    $sql = "UPDATE torneos SET 
                id_estado = ?, id_ritmo = ?, nombre = ?, direccion = ?, url_maps = ?, 
                ritmo_detalle = ?, rondas = ?, fecha_evento = ?, hora_inicio = ?, 
                costo = ?, es_fide = ?, whatsapp_contacto = ?, red_social = ?, 
                archivo_contenido = ?, archivo_nombre = ?
            WHERE id_torneo = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $idEstado, $idRitmo, $nombre, $direccion, $urlMaps ?: null,
        $ritmoDet, $rondas, $fecha, $hora,
        $costo, $esFide, $whatsapp, $redSocial ?: null,
        $contenidoFinal, $nombreFinal, $idTorneo
    ]);

    $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Los cambios del torneo se guardaron correctamente!'];
} else {
    // MODO CREACIÓN (CREATE)
    $sql = "INSERT INTO torneos (
                id_organizador, id_estado, id_ritmo, nombre, direccion, url_maps, 
                ritmo_detalle, rondas, fecha_evento, hora_inicio, costo, es_fide, 
                whatsapp_contacto, red_social, archivo_contenido, archivo_nombre
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $idUsuario, $idEstado, $idRitmo, $nombre, $direccion, $urlMaps ?: null,
        $ritmoDet, $rondas, $fecha, $hora, $costo, $esFide,
        $whatsapp, $redSocial ?: null, $archivoBase64, $archivoNombre
    ]);

    $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Torneo publicado en la cartelera nacional!'];
}

$destino = ($_SESSION['id_rol'] === 1) ? '../admin.php' : '../mis_torneos.php';
header("Location: $destino");
exit;