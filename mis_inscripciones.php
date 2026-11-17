<?php
require_once 'config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 3) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Esta sección es exclusiva para cuentas de Jugador.'];
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Mis Inscripciones - ChessEvents México';
$migaActual   = 'Mis Inscripciones';
$idJugador    = $_SESSION['id_usuario'];

$sql = "SELECT i.id_inscripcion, i.categoria, i.estado_inscripcion, i.fecha_inscripcion,
               t.id_torneo, t.nombre, t.fecha_evento, t.hora_inicio, t.costo,
               e.nombre_estado, r.nombre_ritmo
        FROM inscripciones i
        INNER JOIN torneos t ON i.id_torneo = t.id_torneo
        INNER JOIN estados e ON t.id_estado = e.id_estado
        INNER JOIN ritmos r ON t.id_ritmo = r.id_ritmo
        WHERE i.id_jugador = ?
        ORDER BY t.fecha_evento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idJugador]);
$inscripciones = $stmt->fetchAll();

$hoy = date('Y-m-d');

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bolder text-dark mb-1">Mis Inscripciones a Torneos</h1>
            <p class="text-muted small mb-0">Consulta los eventos en los que participas o cancela tu lugar antes de la fecha del torneo.</p>
        </div>
        <a href="index.php" class="btn btn-chess-primary px-4 py-2.5 rounded-3 shadow-sm">
            <i class="bi bi-search me-1"></i> Explorar Más Torneos
        </a>
    </div>

    <?php if (empty($inscripciones)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="text-muted mb-3">
                <i class="bi bi-card-checklist display-4"></i>
            </div>
            <h4 class="fw-bold text-dark">Aún no tienes inscripciones activas</h4>
            <p class="text-muted small mb-3">Explora la cartelera nacional y regístrate en los próximos torneos disponibles.</p>
            <div>
                <a href="index.php" class="btn btn-chess-primary px-4 py-2 rounded-3">Ver Cartelera de Torneos</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($inscripciones as $ins): 
                $esPasado = ($ins['fecha_evento'] < $hoy);
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-body p-4">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-8">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($ins['estado_inscripcion']) ?>
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">
                                        Categoría: <?= htmlspecialchars($ins['categoria']) ?>
                                    </span>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                        <?= htmlspecialchars($ins['nombre_ritmo']) ?>
                                    </span>
                                </div>

                                <h2 class="h5 fw-bolder text-dark mb-1">
                                    <a href="detalles.php?id=<?= $ins['id_torneo'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($ins['nombre']) ?>
                                    </a>
                                </h2>

                                <p class="small text-muted mb-0">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($ins['nombre_estado']) ?> &bull; 
                                    <i class="bi bi-calendar3 text-primary ms-2 me-1"></i><?= date('d/m/Y', strtotime($ins['fecha_evento'])) ?> (<?= date('H:i', strtotime($ins['hora_inicio'])) ?> hrs)
                                </p>
                            </div>

                            <div class="col-lg-4 d-flex justify-content-lg-end gap-2">
                                <a href="detalles.php?id=<?= $ins['id_torneo'] ?>" class="btn btn-outline-primary btn-sm rounded-3 fw-bold px-3">
                                    <i class="bi bi-file-earmark-text me-1"></i> Ver Convocatoria
                                </a>

                                <?php if (!$esPasado): ?>
                                    <form method="POST" action="backend/inscripcion_accion.php" onsubmit="return confirm('¿Seguro que deseas cancelar tu inscripción a este torneo?');">
                                        <input type="hidden" name="accion" value="cancelar">
                                        <input type="hidden" name="id_inscripcion" value="<?= $ins['id_inscripcion'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 fw-bold px-3">
                                            <i class="bi bi-x-circle me-1"></i> Cancelar Lugar
                                        </button>
                                    </form>
                                <?php endif; ?>
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