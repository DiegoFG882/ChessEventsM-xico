<?php
require_once 'config/conexion.php';

$tituloPagina = 'ChessEvents México - Cartelera Nacional de Torneos';

// Cargar catálogos para los filtros
$estados = $pdo->query("SELECT id_estado, nombre_estado FROM estados ORDER BY nombre_estado ASC")->fetchAll();
$ritmos  = $pdo->query("SELECT id_ritmo, nombre_ritmo FROM ritmos ORDER BY id_ritmo ASC")->fetchAll();

// Recibir filtros por GET
$busqueda = trim($_GET['q'] ?? '');
$filtroEstado = (int)($_GET['estado'] ?? 0);
$filtroRitmo  = (int)($_GET['ritmo'] ?? 0);

// Construir consulta dinámica con JOINs a tablas normalizadas
$sql = "SELECT t.*, e.nombre_estado, r.nombre_ritmo, u.nombre_completo AS organizador,
               (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo) AS total_inscritos
        FROM torneos t
        INNER JOIN estados e ON t.id_estado = e.id_estado
        INNER JOIN ritmos r ON t.id_ritmo = r.id_ritmo
        INNER JOIN usuarios u ON t.id_organizador = u.id_usuario
        WHERE 1=1";

$params = [];

if ($busqueda !== '') {
    $sql .= " AND (LOWER(t.nombre) LIKE LOWER(?) OR LOWER(t.direccion) LIKE LOWER(?))";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($filtroEstado > 0) {
    $sql .= " AND t.id_estado = ?";
    $params[] = $filtroEstado;
}

if ($filtroRitmo > 0) {
    $sql .= " AND t.id_ritmo = ?";
    $params[] = $filtroRitmo;
}

// Ordenar: primero los torneos próximos en orden ascendente, y luego los pasados en orden descendente
$sql .= " ORDER BY (t.fecha_evento >= CURRENT_DATE) DESC, 
                   CASE WHEN t.fecha_evento >= CURRENT_DATE THEN t.fecha_evento END ASC,
                   CASE WHEN t.fecha_evento < CURRENT_DATE THEN t.fecha_evento END DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$torneos = $stmt->fetchAll();

$hoy = date('Y-m-d');
$torneosProximos    = array_filter($torneos, fn($t) => $t['fecha_evento'] >= $hoy);
$torneosFinalizados = array_filter($torneos, fn($t) => $t['fecha_evento'] < $hoy);

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">

    <!-- Banner Principal Minimalista -->
    <div class="bg-dark text-white rounded-4 p-4 p-md-5 mb-4 shadow-sm position-relative overflow-hidden">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark fw-bold text-uppercase mb-2 px-3 py-1">
                    <i class="bi bi-geo-alt-fill me-1"></i> Cartelera Oficial en México
                </span>
                <h1 class="display-6 fw-bolder mb-2">Encuentra y participa en los próximos torneos de ajedrez</h1>
                <p class="text-light opacity-75 mb-0">Explora convocatorias oficiales, verifica validez FIDE, descarga las bases en PDF e inscríbete en línea.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <?php if (!isset($_SESSION['id_usuario'])): ?>
                    <a href="registro.php" class="btn btn-warning fw-bold px-4 py-2.5 rounded-3 shadow-sm">
                        <i class="bi bi-person-plus-fill me-1"></i> Registrarme Ahora
                    </a>
                <?php elseif ($_SESSION['id_rol'] === 2 || $_SESSION['id_rol'] === 1): ?>
                    <a href="formulario_torneo.php" class="btn btn-warning fw-bold px-4 py-2.5 rounded-3 shadow-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> Publicar Convocatoria
                    </a>
                <?php else: ?>
                    <a href="mis_inscripciones.php" class="btn btn-outline-light fw-bold px-4 py-2.5 rounded-3">
                        <i class="bi bi-card-checklist me-1"></i> Ver Mis Inscripciones
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="q" class="form-label small fw-bold text-muted text-uppercase mb-1">Buscar por nombre o sede</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="q" name="q" value="<?= htmlspecialchars($busqueda) ?>" 
                               class="form-control bg-light border-start-0" placeholder="Ej. Abierto Nacional, UNAM...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="estado" class="form-label small fw-bold text-muted text-uppercase mb-1">Estado</label>
                    <select id="estado" name="estado" class="form-select bg-light">
                        <option value="0">Todos los estados</option>
                        <?php foreach ($estados as $est): ?>
                            <option value="<?= $est['id_estado'] ?>" <?= $filtroEstado === (int)$est['id_estado'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($est['nombre_estado']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="ritmo" class="form-label small fw-bold text-muted text-uppercase mb-1">Ritmo de juego</label>
                    <select id="ritmo" name="ritmo" class="form-select bg-light">
                        <option value="0">Todos los ritmos</option>
                        <?php foreach ($ritmos as $rit): ?>
                            <option value="<?= $rit['id_ritmo'] ?>" <?= $filtroRitmo === (int)$rit['id_ritmo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rit['nombre_ritmo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-chess-primary w-100 py-2 rounded-3">
                        Filtrar
                    </button>
                    <?php if ($busqueda !== '' || $filtroEstado > 0 || $filtroRitmo > 0): ?>
                        <a href="index.php" class="btn btn-outline-secondary py-2 rounded-3" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- SECCIÓN 1: PRÓXIMOS TORNEOS Y CONVOCATORIAS -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bolder text-dark mb-0">
            <i class="bi bi-calendar-event me-2 text-primary"></i>Próximos Torneos y Convocatorias
        </h2>
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill">
            <?= count($torneosProximos) ?> <?= count($torneosProximos) === 1 ? 'evento próximo' : 'eventos próximos' ?>
        </span>
    </div>

    <?php if (empty($torneosProximos)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="text-muted mb-3">
                <i class="bi bi-inbox display-4"></i>
            </div>
            <h4 class="fw-bold text-dark">No se encontraron torneos próximos</h4>
            <p class="text-muted small mb-3">No hay convocatorias vigentes que coincidan con los filtros seleccionados en este momento.</p>
            <?php if ($busqueda !== '' || $filtroEstado > 0 || $filtroRitmo > 0): ?>
                <div>
                    <a href="index.php" class="btn btn-outline-primary btn-sm fw-bold rounded-3 px-4">Ver todos los torneos</a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row g-3 mb-5">
            <?php foreach ($torneosProximos as $t): 
                $costoTexto = ($t['costo'] <= 0) ? 'Gratis' : '$' . number_format($t['costo'], 2) . ' MXN';
            ?>
            <div class="col-12">
                <div class="card card-torneo bg-white shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-8">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#e0e7ff; color:#3730a3;">
                                        <i class="bi bi-stopwatch me-1"></i><?= htmlspecialchars($t['nombre_ritmo']) ?> (<?= htmlspecialchars($t['ritmo_detalle']) ?>)
                                    </span>

                                    <?php if ($t['es_fide']): ?>
                                        <span class="badge badge-fide rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-award-fill me-1"></i>Válido FIDE
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="h5 fw-bolder text-dark mb-2">
                                    <a href="detalles.php?id=<?= $t['id_torneo'] ?>" class="text-decoration-none text-dark stretched-link">
                                        <?= htmlspecialchars($t['nombre']) ?>
                                    </a>
                                </h3>

                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                    <span><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($t['nombre_estado']) ?></span>
                                    <span><i class="bi bi-calendar3 text-primary me-1"></i><?= date('d/m/Y', strtotime($t['fecha_evento'])) ?></span>
                                    <span><i class="bi bi-clock text-primary me-1"></i><?= date('H:i', strtotime($t['hora_inicio'])) ?> hrs</span>
                                    <span><i class="bi bi-people-fill text-success me-1"></i><?= $t['total_inscritos'] ?> <?= $t['total_inscritos'] == 1 ? 'inscrito' : 'inscritos' ?></span>
                                </div>
                            </div>

                            <div class="col-lg-4 text-lg-end border-top border-lg-0 pt-3 pt-lg-0 d-flex d-lg-block justify-content-between align-items-center">
                                <div class="mb-lg-2">
                                    <span class="d-block small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Inscripción</span>
                                    <span class="h4 fw-bolder text-primary mb-0"><?= $costoTexto ?></span>
                                </div>

                                <div class="position-relative" style="z-index: 2;">
                                    <a href="detalles.php?id=<?= $t['id_torneo'] ?>" class="btn btn-chess-primary btn-sm px-4 py-2 rounded-3">
                                        Ver Convocatoria e Inscribirme
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN 2: EVENTOS FINALIZADOS -->
    <?php if (!empty($torneosFinalizados)): ?>
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4 pt-3 border-top">
            <h2 class="h5 fw-bolder text-secondary mb-0">
                <i class="bi bi-clock-history me-2"></i>Eventos Finalizados
            </h2>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                <?= count($torneosFinalizados) ?> <?= count($torneosFinalizados) === 1 ? 'historial' : 'historiales' ?>
            </span>
        </div>

        <div class="row g-3">
            <?php foreach ($torneosFinalizados as $t): 
                $costoTexto = ($t['costo'] <= 0) ? 'Gratis' : '$' . number_format($t['costo'], 2) . ' MXN';
            ?>
            <div class="col-12">
                <div class="card card-torneo bg-white shadow-sm opacity-75">
                    <div class="card-body p-4">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-8">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-bold">
                                        <i class="bi bi-stopwatch me-1"></i><?= htmlspecialchars($t['nombre_ritmo']) ?> (<?= htmlspecialchars($t['ritmo_detalle']) ?>)
                                    </span>

                                    <?php if ($t['es_fide']): ?>
                                        <span class="badge badge-fide rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-award-fill me-1"></i>Válido FIDE
                                        </span>
                                    <?php endif; ?>

                                    <span class="badge bg-secondary rounded-pill px-3 py-1">Evento Finalizado</span>
                                </div>

                                <h3 class="h5 fw-bolder text-dark mb-2">
                                    <a href="detalles.php?id=<?= $t['id_torneo'] ?>" class="text-decoration-none text-dark stretched-link">
                                        <?= htmlspecialchars($t['nombre']) ?>
                                    </a>
                                </h3>

                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                    <span><i class="bi bi-geo-alt-fill text-secondary me-1"></i><?= htmlspecialchars($t['nombre_estado']) ?></span>
                                    <span><i class="bi bi-calendar3 text-secondary me-1"></i><?= date('d/m/Y', strtotime($t['fecha_evento'])) ?></span>
                                    <span><i class="bi bi-people-fill text-secondary me-1"></i><?= $t['total_inscritos'] ?> <?= $t['total_inscritos'] == 1 ? 'participante' : 'participantes' ?></span>
                                </div>
                            </div>

                            <div class="col-lg-4 text-lg-end border-top border-lg-0 pt-3 pt-lg-0 d-flex d-lg-block justify-content-between align-items-center">
                                <div class="mb-lg-2">
                                    <span class="d-block small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Inscripción</span>
                                    <span class="h4 fw-bolder text-muted mb-0"><?= $costoTexto ?></span>
                                </div>

                                <div class="position-relative" style="z-index: 2;">
                                    <a href="detalles.php?id=<?= $t['id_torneo'] ?>" class="btn btn-outline-secondary btn-sm px-4 py-2 rounded-3">
                                        Ver Información del Evento
                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>