<?php
require_once 'config/conexion.php';

// Verificar sesión y permiso (Organizador = 2 o Admin = 1)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2])) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Acceso exclusivo para cuentas de Organizador.'];
    header('Location: login.php');
    exit;
}

$tituloPagina = 'Mis Torneos Publicados - ChessEvents México';
$migaActual   = 'Mis Torneos Publicados';
$idUsuario    = $_SESSION['id_usuario'];

// Consultar torneos creados por este organizador
$sql = "SELECT t.*, e.nombre_estado, r.nombre_ritmo
        FROM torneos t
        INNER JOIN estados e ON t.id_estado = e.id_estado
        INNER JOIN ritmos r ON t.id_ritmo = r.id_ritmo
        WHERE t.id_organizador = ?
        ORDER BY t.fecha_evento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$misTorneos = $stmt->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bolder text-dark mb-1">Panel de Organizador</h1>
            <p class="text-muted small mb-0">Administra tus convocatorias publicadas y revisa la lista oficial de jugadores inscritos.</p>
        </div>
        <a href="formulario_torneo.php" class="btn btn-chess-primary px-4 py-2.5 rounded-3 shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Publicar Nuevo Torneo
        </a>
    </div>

    <?php if (empty($misTorneos)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="text-muted mb-3">
                <i class="bi bi-calendar-plus display-4"></i>
            </div>
            <h4 class="fw-bold text-dark">Aún no has publicado convocatorias</h4>
            <p class="text-muted small mb-3">Crea tu primer torneo para que los jugadores de todo el país puedan verlo e inscribirse.</p>
            <div>
                <a href="formulario_torneo.php" class="btn btn-chess-primary px-4 py-2 rounded-3">Crear Mi Primer Torneo</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($misTorneos as $t): 
                // Consultar jugadores inscritos en este torneo específico
                $stmtJug = $pdo->prepare("
                    SELECT i.categoria, i.fecha_inscripcion, u.nombre_completo, u.correo, u.telefono, u.id_fide, u.elo_actual
                    FROM inscripciones i
                    INNER JOIN usuarios u ON i.id_jugador = u.id_usuario
                    WHERE i.id_torneo = ?
                    ORDER BY i.fecha_inscripcion ASC
                ");
                $stmtJug->execute([$t['id_torneo']]);
                $inscritos = $stmtJug->fetchAll();
                $totalInscritos = count($inscritos);
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 bg-white">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-7">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">
                                        <?= htmlspecialchars($t['nombre_ritmo']) ?> (<?= htmlspecialchars($t['ritmo_detalle']) ?>)
                                    </span>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold">
                                        <i class="bi bi-people-fill me-1"></i><?= $totalInscritos ?> inscritos
                                    </span>
                                </div>
                                <h2 class="h5 fw-bolder text-dark mb-1"><?= htmlspecialchars($t['nombre']) ?></h2>
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($t['nombre_estado']) ?> &bull; 
                                    <i class="bi bi-calendar3 text-primary ms-2 me-1"></i><?= date('d/m/Y', strtotime($t['fecha_evento'])) ?> (<?= date('H:i', strtotime($t['hora_inicio'])) ?> hrs)
                                </p>
                            </div>

                            <!-- Acciones CRUD del Torneo -->
                            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                                <button class="btn btn-outline-dark btn-sm rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#listaInscritos<?= $t['id_torneo'] ?>">
                                    <i class="bi bi-list-ul me-1"></i> Ver Inscritos (<?= $totalInscritos ?>)
                                </button>
                                <a href="detalles.php?id=<?= $t['id_torneo'] ?>" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold" title="Ver ficha pública">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="formulario_torneo.php?id=<?= $t['id_torneo'] ?>" class="btn btn-warning btn-sm rounded-3 fw-bold text-dark">
                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                </a>
                                <form method="POST" action="backend/torneo_eliminar.php" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este torneo de forma permanente? Se borrarán también sus inscripciones.');">
                                    <input type="hidden" name="id_torneo" value="<?= $t['id_torneo'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm rounded-3 fw-bold">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Tabla Desplegable de Jugadores Inscritos -->
                        <div class="collapse mt-4 pt-3 border-top" id="listaInscritos<?= $t['id_torneo'] ?>">
                            <h3 class="h6 fw-bold text-uppercase text-muted mb-3">
                                <i class="bi bi-person-lines-fill text-primary me-1"></i> Lista Oficial de Participantes
                            </h3>
                            <?php if (empty($inscritos)): ?>
                                <p class="small text-muted fst-italic mb-0">Todavía no hay jugadores inscritos en este evento.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Jugador</th>
                                                <th>Categoría</th>
                                                <th>ID FIDE / Elo</th>
                                                <th>Teléfono</th>
                                                <th>Fecha Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inscritos as $idx => $jug): ?>
                                                <tr>
                                                    <td class="fw-bold"><?= $idx + 1 ?></td>
                                                    <td>
                                                        <span class="fw-bold d-block text-dark"><?= htmlspecialchars($jug['nombre_completo']) ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($jug['correo']) ?></span>
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($jug['categoria']) ?></span></td>
                                                    <td>
                                                        <?= $jug['id_fide'] ? 'FIDE: ' . htmlspecialchars($jug['id_fide']) : 'Sin ID' ?> 
                                                        <span class="text-muted">(Elo: <?= (int)$jug['elo_actual'] ?>)</span>
                                                    </td>
                                                    <td><?= htmlspecialchars($jug['telefono']) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($jug['fecha_inscripcion'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>