<?php
require_once 'config/conexion.php';

$idTorneo = (int)($_GET['id'] ?? 0);

if ($idTorneo <= 0) {
    header('Location: index.php');
    exit;
}

// Consultar datos completos del torneo con JOINs
$sql = "SELECT t.*, e.nombre_estado, r.nombre_ritmo, u.nombre_completo AS organizador
        FROM torneos t
        INNER JOIN estados e ON t.id_estado = e.id_estado
        INNER JOIN ritmos r ON t.id_ritmo = r.id_ritmo
        INNER JOIN usuarios u ON t.id_organizador = u.id_usuario
        WHERE t.id_torneo = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idTorneo]);
$torneo = $stmt->fetch();

if (!$torneo) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'El torneo solicitado no existe o fue eliminado.'];
    header('Location: index.php');
    exit;
}

$tituloPagina = $torneo['nombre'] . ' - ChessEvents México';
$migaActual   = 'Detalles del Torneo';
$esPasado     = ($torneo['fecha_evento'] < date('Y-m-d'));
$costoTexto   = ($torneo['costo'] <= 0) ? 'Gratis' : '$' . number_format($torneo['costo'], 2) . ' MXN';

// Verificar si el usuario actual ya está inscrito
$yaInscrito = false;
if (isset($_SESSION['id_usuario'])) {
    $stmtIns = $pdo->prepare("SELECT id_inscripcion, categoria FROM inscripciones WHERE id_torneo = ? AND id_jugador = ?");
    $stmtIns->execute([$idTorneo, $_SESSION['id_usuario']]);
    $inscripcionActual = $stmtIns->fetch();
    if ($inscripcionActual) {
        $yaInscrito = true;
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Encabezado del Torneo -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="bg-dark text-white p-4 p-md-5 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <a href="index.php" class="btn btn-outline-light btn-sm rounded-3 fw-semibold">
                            <i class="bi bi-arrow-left me-1"></i> Volver a Cartelera
                        </a>
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                <i class="bi bi-stopwatch me-1"></i><?= htmlspecialchars($torneo['nombre_ritmo']) ?>
                            </span>
                            <?php if ($torneo['es_fide']): ?>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-award-fill me-1"></i>Válido FIDE
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h1 class="display-6 fw-bolder mb-2"><?= htmlspecialchars($torneo['nombre']) ?></h1>
                    <p class="text-light opacity-75 small mb-0">
                        <i class="bi bi-person-check-fill me-1"></i> Organizado por: <strong><?= htmlspecialchars($torneo['organizador']) ?></strong>
                    </p>
                </div>

                <!-- Tarjetas de Resumen Rápido -->
                <div class="card-body p-4 p-md-5 bg-white">
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-4 border h-100">
                                <span class="d-block small text-muted fw-bold text-uppercase mb-1"><i class="bi bi-calendar3 text-primary me-1"></i>Fecha</span>
                                <span class="fw-bolder text-dark"><?= date('d/m/Y', strtotime($torneo['fecha_evento'])) ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-4 border h-100">
                                <span class="d-block small text-muted fw-bold text-uppercase mb-1"><i class="bi bi-clock text-primary me-1"></i>Hora Inicio</span>
                                <span class="fw-bolder text-dark"><?= date('H:i', strtotime($torneo['hora_inicio'])) ?> hrs</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded-4 border h-100">
                                <span class="d-block small text-muted fw-bold text-uppercase mb-1"><i class="bi bi-hourglass-split text-primary me-1"></i>Control Tiempo</span>
                                <span class="fw-bolder text-dark"><?= htmlspecialchars($torneo['ritmo_detalle']) ?></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-4 border h-100" style="background:#eef2ff; border-color:#c7d2fe !important;">
                                <span class="d-block small text-primary fw-bold text-uppercase mb-1"><i class="bi bi-tag-fill me-1"></i>Inscripción</span>
                                <span class="fw-bolder text-primary fs-5"><?= $costoTexto ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Sede y Ubicación -->
                        <div class="col-md-6">
                            <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Ubicación de la Sede
                            </h2>
                            <div class="p-3 bg-light rounded-4 border">
                                <p class="fw-bold text-dark mb-1"><?= htmlspecialchars($torneo['nombre_estado']) ?></p>
                                <p class="small text-muted mb-3"><?= htmlspecialchars($torneo['direccion']) ?></p>
                                <?php if (!empty($torneo['url_maps'])): ?>
                                    <a href="<?= htmlspecialchars($torneo['url_maps']) ?>" target="_blank" class="btn btn-dark btn-sm w-100 rounded-3 fw-semibold">
                                        <i class="bi bi-map me-1"></i> Abrir Ubicación en Google Maps
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Especificaciones Técnicas -->
                        <div class="col-md-6">
                            <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">
                                <i class="bi bi-sliders text-primary me-1"></i> Detalles Técnicos
                            </h2>
                            <ul class="list-group list-group-flush border rounded-4 overflow-hidden">
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-light py-3">
                                    <span class="small fw-semibold text-muted">Sistema / Número de Rondas</span>
                                    <span class="badge bg-white text-dark border px-3 py-2 fw-bold"><?= (int)$torneo['rondas'] ?> Rondas</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-light py-3">
                                    <span class="small fw-semibold text-muted">Aval Oficial</span>
                                    <span class="fw-bold small <?= $torneo['es_fide'] ? 'text-warning-emphasis' : 'text-muted' ?>">
                                        <?= $torneo['es_fide'] ? 'Válido para Rating FIDE' : 'Torneo Abierto / Local' ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Módulo de Inscripción en Línea -->
                    <div class="p-4 rounded-4 border mb-4" style="background-color: #f8fafc;">
                        <div class="row align-items-center gy-3">
                            <div class="col-md-7">
                                <h3 class="h5 fw-bolder text-dark mb-1">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> Registro de Participación
                                </h3>
                                <p class="small text-muted mb-0">
                                    Aparta tu lugar en la lista oficial de jugadores del torneo.
                                </p>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <?php if ($esPasado): ?>
                                    <button class="btn btn-secondary w-100 rounded-3 fw-bold" disabled>
                                        <i class="bi bi-lock-fill me-1"></i> Inscripciones Cerradas
                                    </button>
                                <?php elseif (!isset($_SESSION['id_usuario'])): ?>
                                    <a href="login.php" class="btn btn-chess-primary w-100 py-2.5 rounded-3">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Inicia Sesión para Inscribirte
                                    </a>
                                <?php elseif ($yaInscrito): ?>
                                    <div class="alert alert-success py-2 px-3 mb-0 rounded-3 small fw-bold text-center">
                                        <i class="bi bi-check-circle-fill me-1"></i> Ya estás inscrito (Categoría: <?= htmlspecialchars($inscripcionActual['categoria']) ?>)
                                    </div>
                                <?php elseif ($_SESSION['id_rol'] === 3): ?>
                                    <form method="POST" action="backend/inscripcion_accion.php" class="d-flex gap-2">
                                        <input type="hidden" name="accion" value="inscribir">
                                        <input type="hidden" name="id_torneo" value="<?= $idTorneo ?>">
                                        <select name="categoria" class="form-select form-select-sm bg-white" required>
                                            <option value="Libre">Cat. Libre</option>
                                            <option value="Sub-2000">Sub-2000</option>
                                            <option value="Sub-1700">Sub-1700</option>
                                            <option value="Juvenil / Infantil">Juvenil / Infantil</option>
                                        </select>
                                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3 rounded-3 text-nowrap">
                                            <i class="bi bi-check2-circle me-1"></i> Confirmar Lugar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary py-2 px-3">Inscripción habilitada solo para cuentas de Jugador</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto y Convocatoria Oficial -->
                    <div class="bg-dark text-white rounded-4 p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div>
                            <h4 class="h6 fw-bold mb-1">Contacto del Organizador y Bases Oficiales</h4>
                            <p class="small text-light opacity-75 mb-3">Comunícate directamente o descarga el documento de la convocatoria.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://wa.me/52<?= preg_replace('/[^0-9]/', '', $torneo['whatsapp_contacto']) ?>?text=Hola,%20vi%20la%20convocatoria%20de%20tu%20torneo%20en%20ChessEvents%20M%C3%A9xico%20y%20me%20interesa%20participar." 
                                   target="_blank" class="btn btn-success btn-sm fw-bold px-3 py-2 rounded-3">
                                    <i class="bi bi-whatsapp me-1"></i> Contactar por WhatsApp
                                </a>
                                <?php if (!empty($torneo['red_social'])): ?>
                                    <a href="<?= htmlspecialchars($torneo['red_social']) ?>" target="_blank" class="btn btn-outline-light btn-sm fw-bold px-3 py-2 rounded-3">
                                        <i class="bi bi-globe me-1"></i> Página / Red Social
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="w-100 w-md-auto text-center">
                            <?php if (!empty($torneo['archivo_contenido'])): ?>
                                <button type="button" onclick="abrirConvocatoria()" class="btn btn-warning fw-bolder px-4 py-3 rounded-3 shadow-sm w-100">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver Convocatoria Oficial
                                </button>
                                <textarea id="dataArchivo" class="d-none"><?= htmlspecialchars($torneo['archivo_contenido']) ?></textarea>
                            <?php else: ?>
                                <span class="badge bg-secondary p-3 rounded-3 fw-normal">Sin archivo adjunto</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</main>

<script>
    function abrirConvocatoria() {
        const contenido = document.getElementById('dataArchivo')?.value;
        if (!contenido) return;

        if (contenido.startsWith('data:image')) {
            const win = window.open();
            win.document.write(`<html><body style="margin:0;background:#1e293b;display:flex;justify-content:center;align-items:center;"><img src="${contenido}" style="max-width:100%;max-height:100vh;"></body></html>`);
            win.document.close();
        } else if (contenido.startsWith('data:application/pdf')) {
            const base64Data = contenido.split(',')[1];
            const binaryData = atob(base64Data);
            const arrayBuffer = new Uint8Array(binaryData.length);
            for (let i = 0; i < binaryData.length; i++) {
                arrayBuffer[i] = binaryData.charCodeAt(i);
            }
            const blob = new Blob([arrayBuffer], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);
            window.open(url, '_blank');
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>