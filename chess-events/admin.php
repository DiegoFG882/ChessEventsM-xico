<?php
require_once 'config/conexion.php';

// Acceso exclusivo para Administrador (id_rol = 1) - Protección contra acceso directo por URL
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Acceso denegado. Área exclusiva para administradores.'];
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Panel Administrador - ChessEvents México';
$migaActual   = 'Panel Administrador';

// Consultar todos los torneos con su conteo de inscritos
$torneos = $pdo->query("
    SELECT t.id_torneo, t.nombre, t.fecha_evento, e.nombre_estado, u.nombre_completo AS organizador,
           (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo) AS inscritos
    FROM torneos t
    INNER JOIN estados e ON t.id_estado = e.id_estado
    INNER JOIN usuarios u ON t.id_organizador = u.id_usuario
    ORDER BY t.fecha_creacion DESC
")->fetchAll();

// Consultar todos los usuarios y cuántos torneos ha creado cada uno
$usuarios = $pdo->query("
    SELECT u.id_usuario, u.nombre_completo, u.correo, u.telefono, u.id_rol, r.nombre_rol, e.nombre_estado,
           (SELECT COUNT(*) FROM torneos t WHERE t.id_organizador = u.id_usuario) AS torneos_creados
    FROM usuarios u
    INNER JOIN roles r ON u.id_rol = r.id_rol
    INNER JOIN estados e ON u.id_estado = e.id_estado
    ORDER BY u.id_usuario ASC
")->fetchAll();

$rolesCatalogo = $pdo->query("SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol ASC")->fetchAll();
$totalInscripcionesGlobal = $pdo->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <span class="badge bg-dark text-warning fw-bold text-uppercase mb-1">Control Global</span>
            <h1 class="h3 fw-bolder text-dark mb-0">Panel Master de Administración</h1>
        </div>
    </div>

    <!-- Estadísticas Rápidas (3 tarjetas) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
                <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-3">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <span class="small text-muted fw-bold text-uppercase d-block">Torneos Publicados</span>
                    <span class="h3 fw-bolder text-dark mb-0"><?= count($torneos) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
                <div class="bg-success-subtle text-success p-3 rounded-4 fs-3">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <span class="small text-muted fw-bold text-uppercase d-block">Usuarios Registrados</span>
                    <span class="h3 fw-bolder text-dark mb-0"><?= count($usuarios) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
                <div class="bg-warning-subtle text-warning-emphasis p-3 rounded-4 fs-3">
                    <i class="bi bi-card-checklist"></i>
                </div>
                <div>
                    <span class="small text-muted fw-bold text-uppercase d-block">Inscripciones Activas</span>
                    <span class="h3 fw-bolder text-dark mb-0"><?= $totalInscripcionesGlobal ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: GESTIÓN DE USUARIOS Y ROLES -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-dark text-white py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-person-gear text-warning me-2"></i>Gestión de Usuarios y Permisos</h2>
            <input type="text" id="filtroUsuarios" class="form-control form-control-sm bg-light" style="max-width: 240px;" placeholder="Filtrar por nombre o correo...">
        </div>
        <div class="card-body p-0 bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="tablaUsuarios">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Usuario</th>
                            <th>Estado / Tel.</th>
                            <th>Asignar Rol</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): 
                            $nombreEsc = htmlspecialchars($u['nombre_completo'], ENT_QUOTES);
                            $correoEsc = htmlspecialchars($u['correo'], ENT_QUOTES);
                            $extraAviso = $u['torneos_creados'] > 0 ? " y sus {$u['torneos_creados']} torneo(s) publicados" : "";
                            $msgConfirmUser = "¿Eliminar permanentemente la cuenta de {$nombreEsc} ({$correoEsc}){$extraAviso}?";
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted">#<?= $u['id_usuario'] ?></td>
                            <td>
                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($u['nombre_completo']) ?></span>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['correo']) ?></span>
                            </td>
                            <td>
                                <span class="d-block"><?= htmlspecialchars($u['nombre_estado']) ?></span>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['telefono']) ?></span>
                            </td>
                            <td>
                                <?php if ($u['id_usuario'] == $_SESSION['id_usuario']): ?>
                                    <span class="badge bg-dark text-warning text-uppercase">Admin Actual</span>
                                <?php else: ?>
                                    <form method="POST" action="backend/admin_usuarios.php" class="d-flex gap-1">
                                        <input type="hidden" name="accion" value="cambiar_rol">
                                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                        <select name="id_rol" class="form-select form-select-sm bg-light" style="width: 130px;">
                                            <?php foreach ($rolesCatalogo as $rol): ?>
                                                <option value="<?= $rol['id_rol'] ?>" <?= $u['id_rol'] == $rol['id_rol'] ? 'selected' : '' ?>>
                                                    <?= ucfirst($rol['nombre_rol']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary btn-sm fw-bold" title="Guardar rol">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                                    <form method="POST" action="backend/admin_usuarios.php" class="d-inline" onsubmit="return confirm('<?= $msgConfirmUser ?>');">
                                        <input type="hidden" name="accion" value="eliminar_usuario">
                                        <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="Eliminar cuenta">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: MODERACIÓN GLOBAL DE TORNEOS E INSCRITOS -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-dark text-white py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i>Torneos Publicados en la Plataforma</h2>
            <input type="text" id="filtroTorneos" class="form-control form-control-sm bg-light" style="max-width: 240px;" placeholder="Filtrar torneo u organizador...">
        </div>
        <div class="card-body p-0 bg-white">
            <?php if (empty($torneos)): ?>
                <p class="p-4 text-muted small mb-0">No hay torneos publicados en este momento.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" id="tablaTorneos">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Torneo</th>
                                <th>Organizador</th>
                                <th>Sede / Fecha</th>
                                <th>Inscritos</th>
                                <th class="text-end pe-4">Moderación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($torneos as $t): 
                                $torneoEsc = htmlspecialchars($t['nombre'], ENT_QUOTES);
                                $cantIns   = (int)$t['inscritos'];
                                $avisoIns  = $cantIns > 0 ? " ADVERTENCIA: Este evento tiene {$cantIns} jugador(es) inscrito(s) que perderán su registro." : "";
                                $msgConfirmTorneo = "¿Eliminar permanentemente el torneo \"{$torneoEsc}\"?{$avisoIns}";

                                // Obtener lista de inscritos para este torneo
                                $stmtJug = $pdo->prepare("
                                    SELECT i.categoria, u.nombre_completo, u.correo, u.elo_actual
                                    FROM inscripciones i
                                    INNER JOIN usuarios u ON i.id_jugador = u.id_usuario
                                    WHERE i.id_torneo = ?
                                    ORDER BY i.fecha_inscripcion ASC
                                ");
                                $stmtJug->execute([$t['id_torneo']]);
                                $listaJugadores = $stmtJug->fetchAll();
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($t['nombre']) ?></td>
                                <td><?= htmlspecialchars($t['organizador']) ?></td>
                                <td><?= htmlspecialchars($t['nombre_estado']) ?> &bull; <?= date('d/m/Y', strtotime($t['fecha_evento'])) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 fw-bold" 
                                            data-bs-toggle="collapse" data-bs-target="#adminInscritos<?= $t['id_torneo'] ?>">
                                        <i class="bi bi-people-fill me-1"></i><?= $cantIns ?>
                                    </button>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a href="detalles.php?id=<?= $t['id_torneo'] ?>" class="btn btn-outline-secondary btn-sm rounded-3" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="formulario_torneo.php?id=<?= $t['id_torneo'] ?>" class="btn btn-warning btn-sm rounded-3 text-dark fw-bold" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="backend/torneo_eliminar.php" class="d-inline" onsubmit="return confirm('<?= $msgConfirmTorneo ?>');">
                                        <input type="hidden" name="id_torneo" value="<?= $t['id_torneo'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm rounded-3" title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <!-- Fila desplegable con lista de inscritos -->
                            <tr class="collapse bg-light" id="adminInscritos<?= $t['id_torneo'] ?>">
                                <td colspan="5" class="px-4 py-3">
                                    <strong class="d-block text-uppercase text-muted mb-2" style="font-size: 0.72rem;">
                                        Jugadores inscritos en <?= htmlspecialchars($t['nombre']) ?>:
                                    </strong>
                                    <?php if (empty($listaJugadores)): ?>
                                        <span class="text-muted fst-italic">Ningún jugador inscrito todavía.</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($listaJugadores as $jug): ?>
                                                <span class="badge bg-white text-dark border py-2 px-3 fw-normal">
                                                    <strong><?= htmlspecialchars($jug['nombre_completo']) ?></strong> (<?= htmlspecialchars($jug['categoria']) ?> &bull; Elo <?= (int)$jug['elo_actual'] ?>)
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    // Filtro instantáneo para tablas del administrador
    function activarFiltroTabla(inputId, tablaId) {
        document.getElementById(inputId)?.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            document.querySelectorAll(`#${tablaId} tbody tr:not(.collapse)`).forEach(fila => {
                fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    }
    activarFiltroTabla('filtroUsuarios', 'tablaUsuarios');
    activarFiltroTabla('filtroTorneos', 'tablaTorneos');
</script>

<?php require_once 'includes/footer.php'; ?>