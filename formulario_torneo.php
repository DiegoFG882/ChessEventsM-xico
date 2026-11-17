<?php
require_once 'config/conexion.php';

// Solo Organizador (2) o Administrador (1)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2])) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Debes iniciar sesión con una cuenta de Organizador para publicar torneos.'];
    header('Location: login.php');
    exit;
}

// Cargar catálogos
$estados = $pdo->query("SELECT id_estado, nombre_estado FROM estados ORDER BY nombre_estado ASC")->fetchAll();
$ritmos  = $pdo->query("SELECT id_ritmo, nombre_ritmo FROM ritmos ORDER BY id_ritmo ASC")->fetchAll();

// Verificar si estamos en modo edición
$idEditar = (int)($_GET['id'] ?? 0);
$torneo   = null;

if ($idEditar > 0) {
    $stmt = $pdo->prepare("SELECT * FROM torneos WHERE id_torneo = ?");
    $stmt->execute([$idEditar]);
    $torneo = $stmt->fetch();

    if (!$torneo || ($torneo['id_organizador'] != $_SESSION['id_usuario'] && $_SESSION['id_rol'] !== 1)) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'No tienes permiso para editar este torneo.'];
        header('Location: mis_torneos.php');
        exit;
    }
}

$tituloPagina = ($torneo ? 'Editar Torneo' : 'Publicar Convocatoria') . ' - ChessEvents México';
$migaActual   = $torneo ? 'Editar Convocatoria' : 'Publicar Convocatoria';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-dark text-white p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold text-uppercase mb-1">
                            <?= $torneo ? 'Modo Edición' : 'Nueva Convocatoria' ?>
                        </span>
                        <h1 class="h4 fw-bolder mb-0">
                            <?= $torneo ? 'Modificar Datos del Torneo' : 'Publicar Torneo de Ajedrez' ?>
                        </h1>
                    </div>
                    <a href="<?= $_SESSION['id_rol'] === 1 ? 'admin.php' : 'mis_torneos.php' ?>" class="btn btn-outline-light btn-sm rounded-3 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar
                    </a>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form method="POST" action="backend/torneo_guardar.php" enctype="multipart/form-data">
                        <?php if ($torneo): ?>
                            <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">
                        <?php endif; ?>

                        <!-- 1. Datos Generales -->
                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">1. Información General</h2>
                        <div class="mb-3">
                            <label for="nombre" class="form-label small fw-bold">Nombre Oficial del Torneo *</label>
                            <input type="text" id="nombre" name="nombre" required class="form-control bg-light" 
                                   value="<?= htmlspecialchars($torneo['nombre'] ?? '') ?>" placeholder="Ej. I Torneo Abierto de Primavera 2026">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="id_estado" class="form-label small fw-bold">Estado Sede *</label>
                                <select id="id_estado" name="id_estado" required class="form-select bg-light">
                                    <option value="">Selecciona el estado...</option>
                                    <?php foreach ($estados as $est): ?>
                                        <option value="<?= $est['id_estado'] ?>" <?= (($torneo['id_estado'] ?? 0) == $est['id_estado']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($est['nombre_estado']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="direccion" class="form-label small fw-bold">Dirección Exacta o Lugar *</label>
                                <input type="text" id="direccion" name="direccion" required class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['direccion'] ?? '') ?>" placeholder="Ej. Centro Cultural, Col. Centro">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="url_maps" class="form-label small fw-bold">Enlace de Google Maps <span class="text-muted fw-normal">(Opcional)</span></label>
                            <input type="url" id="url_maps" name="url_maps" class="form-control bg-light"
                                   value="<?= htmlspecialchars($torneo['url_maps'] ?? '') ?>" placeholder="https://maps.google.com/...">
                        </div>

                        <!-- 2. Formato y Fecha -->
                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">2. Formato, Ritmo, Cupo y Horario</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="id_ritmo" class="form-label small fw-bold">Categoría de Ritmo *</label>
                                <select id="id_ritmo" name="id_ritmo" required class="form-select bg-light">
                                    <option value="">Selecciona...</option>
                                    <?php foreach ($ritmos as $rit): ?>
                                        <option value="<?= $rit['id_ritmo'] ?>" <?= (($torneo['id_ritmo'] ?? 0) == $rit['id_ritmo']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($rit['nombre_ritmo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="ritmo_detalle" class="form-label small fw-bold">Tiempo por Jugador *</label>
                                <input type="text" id="ritmo_detalle" name="ritmo_detalle" required class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['ritmo_detalle'] ?? '') ?>" placeholder="Ej. 15+10 o 90+30">
                            </div>
                            <div class="col-md-4">
                                <label for="rondas" class="form-label small fw-bold">Número de Rondas *</label>
                                <input type="number" id="rondas" name="rondas" required min="1" max="15" class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['rondas'] ?? '5') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="fecha_evento" class="form-label small fw-bold">Fecha del Evento *</label>
                                <input type="date" id="fecha_evento" name="fecha_evento" required class="form-control bg-light"
                                       <?= !$torneo ? 'min="' . date('Y-m-d') . '"' : '' ?>
                                       value="<?= htmlspecialchars($torneo['fecha_evento'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="hora_inicio" class="form-label small fw-bold">Hora de Inicio (Ronda 1) *</label>
                                <input type="time" id="hora_inicio" name="hora_inicio" required class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['hora_inicio'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="cupo" class="form-label small fw-bold">Cupo Máximo (Jugadores) *</label>
                                <input type="number" id="cupo" name="cupo" required min="2" max="2000" class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['cupo'] ?? '150') ?>">
                            </div>
                        </div>

                        <!-- 3. Costo y Aval -->
                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">3. Inscripción y Aval Oficial</h2>
                        <div class="row g-3 align-items-center mb-4">
                            <div class="col-md-6">
                                <label for="costo" class="form-label small fw-bold">Costo de Inscripción (MXN)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" id="costo" name="costo" step="0.01" min="0" class="form-control bg-light"
                                           value="<?= htmlspecialchars($torneo['costo'] ?? '0') ?>">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="es_gratis" name="es_gratis"
                                           <?= ($torneo && $torneo['costo'] <= 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label small fw-semibold" for="es_gratis">
                                        Evento gratuito (sin costo de inscripción)
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light border rounded-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="es_fide" name="es_fide"
                                               <?= ($torneo && $torneo['es_fide']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-dark" for="es_fide">
                                            <i class="bi bi-award-fill text-warning me-1"></i> Válido para Rating FIDE
                                        </label>
                                    </div>
                                    <p class="small text-muted mb-0 mt-1">Activa esta opción únicamente si el torneo cuenta con aval oficial FIDE/FENAMAC.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Contacto y Convocatoria -->
                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">4. Contacto y Documento Base</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="whatsapp_contacto" class="form-label small fw-bold">WhatsApp de Informes (10 dígitos) *</label>
                                <input type="tel" id="whatsapp_contacto" name="whatsapp_contacto" required pattern="[0-9]{10}" maxlength="10" class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['whatsapp_contacto'] ?? '') ?>" placeholder="5512345678">
                            </div>
                            <div class="col-md-6">
                                <label for="red_social" class="form-label small fw-bold">Página Web o Red Social <span class="text-muted fw-normal">(Opcional)</span></label>
                                <input type="url" id="red_social" name="red_social" class="form-control bg-light"
                                       value="<?= htmlspecialchars($torneo['red_social'] ?? '') ?>" placeholder="https://facebook.com/tuclub">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="convocatoria" class="form-label small fw-bold">
                                Archivo de Convocatoria (PDF o Imagen JPG/PNG, máx. 4 MB)
                            </label>
                            <input type="file" id="convocatoria" name="convocatoria" accept=".pdf,image/jpeg,image/png,image/webp" class="form-control bg-light">
                            <?php if (!empty($torneo['archivo_nombre'])): ?>
                                <div class="form-text text-success fw-semibold">
                                    <i class="bi bi-paperclip me-1"></i> Archivo actual guardado: <?= htmlspecialchars($torneo['archivo_nombre']) ?> (sube otro solo si deseas reemplazarlo).
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-chess-primary w-100 py-3 rounded-3 text-uppercase fw-bold">
                            <i class="bi bi-save me-1"></i> <?= $torneo ? 'Guardar Cambios del Torneo' : 'Publicar Torneo en Cartelera' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Deshabilitar campo de costo si se marca "Evento gratuito"
    const chkGratis = document.getElementById('es_gratis');
    const inputCosto = document.getElementById('costo');

    function actualizarCosto() {
        if (chkGratis.checked) {
            inputCosto.value = '0';
            inputCosto.readOnly = true;
        } else {
            inputCosto.readOnly = false;
        }
    }
    chkGratis.addEventListener('change', actualizarCosto);
    actualizarCosto();
</script>

<?php require_once 'includes/footer.php'; ?>