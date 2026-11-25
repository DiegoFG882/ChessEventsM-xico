<?php
require_once 'config/conexion.php';

$tituloPagina = 'Panel de Administración - ChessEvents México';

// Verificar que haya sesión activa y que el rol sea Administrador (id_rol = 1)
if (!isset($_SESSION['id_usuario']) || (int)$_SESSION['id_rol'] !== 1) {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones POST del administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'cambiar_rol') {
        $id_objetivo = (int)($_POST['id_usuario'] ?? 0);
        $nuevo_rol   = (int)($_POST['id_rol'] ?? 3);

        if ($id_objetivo === (int)$_SESSION['id_usuario']) {
            $mensaje = 'No puedes cambiar tu propio rol de administrador.';
            $tipo_mensaje = 'warning';
        } elseif ($id_objetivo > 0 && in_array($nuevo_rol, [1, 2, 3], true)) {
            $stmt = $pdo->prepare("UPDATE usuarios SET id_rol = :rol WHERE id_usuario = :id");
            $stmt->execute([':rol' => $nuevo_rol, ':id' => $id_objetivo]);
            $mensaje = 'Rol actualizado correctamente.';
            $tipo_mensaje = 'success';
        }
    } elseif ($accion === 'eliminar_usuario') {
        $id_objetivo = (int)($_POST['id_usuario'] ?? 0);

        if ($id_objetivo === (int)$_SESSION['id_usuario']) {
            $mensaje = 'No puedes eliminar tu propia cuenta activa.';
            $tipo_mensaje = 'warning';
        } elseif ($id_objetivo > 0) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
            $stmt->execute([':id' => $id_objetivo]);
            $mensaje = 'Usuario eliminado del sistema.';
            $tipo_mensaje = 'success';
        }
    } elseif ($accion === 'eliminar_torneo') {
        $id_torneo = (int)($_POST['id_torneo'] ?? 0);

        if ($id_torneo > 0) {
            $stmt = $pdo->prepare("DELETE FROM torneos WHERE id_torneo = :id");
            $stmt->execute([':id' => $id_torneo]);
            $mensaje = 'Torneo eliminado correctamente.';
            $tipo_mensaje = 'success';
        }
    }
}

// Consultas para estadísticas generales
$totalUsuarios      = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalTorneos       = (int)$pdo->query("SELECT COUNT(*) FROM torneos")->fetchColumn();
$totalInscripciones = (int)$pdo->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();

// Listado de usuarios con su rol
$sqlUsuarios = "
    SELECT u.id_usuario, u.nombre_completo, u.correo, u.id_fide, u.elo_actual, u.id_rol, r.nombre_rol
    FROM usuarios u
    INNER JOIN roles r ON u.id_rol = r.id_rol
    ORDER BY u.id_usuario ASC
";
$usuarios = $pdo->query($sqlUsuarios)->fetchAll();

// Listado de torneos con organizador, estado, ritmo y conteo de inscritos
$sqlTorneos = "
    SELECT
        t.id_torneo,
        t.nombre,
        t.fecha_evento,
        t.direccion,
        t.cupo,
        e.nombre_estado,
        ri.nombre_ritmo,
        u.nombre_completo AS organizador,
        (SELECT COUNT(*) FROM inscripciones i WHERE i.id_torneo = t.id_torneo) AS inscritos
    FROM torneos t
    INNER JOIN estados e ON t.id_estado = e.id_estado
    INNER JOIN ritmos ri ON t.id_ritmo = ri.id_ritmo
    INNER JOIN usuarios u ON t.id_organizador = u.id_usuario
    ORDER BY t.fecha_evento DESC
";
$torneos = $pdo->query($sqlTorneos)->fetchAll();

// Jugadores inscritos por torneo (para la lista desplegable de cada torneo)
$sqlInscritos = "
    SELECT i.id_torneo, u.nombre_completo, u.elo_actual, i.categoria
    FROM inscripciones i
    INNER JOIN usuarios u ON i.id_jugador = u.id_usuario
    ORDER BY i.id_torneo ASC, u.nombre_completo ASC
";
$inscritosPorTorneo = [];
foreach ($pdo->query($sqlInscritos)->fetchAll() as $ins) {
    $inscritosPorTorneo[(int)$ins['id_torneo']][] = $ins;
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Panel de Administración</h2>
            <p class="text-muted mb-0">Gestión global de usuarios, roles y convocatorias de ChessEvents México</p>
        </div>
        <a href="formulario_torneo.php" class="btn btn-dark">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Torneo
        </a>
    </div>

    <?php if ($mensaje !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($tipo_mensaje) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de resumen -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Usuarios Registrados</h6>
                        <h3 class="fw-bold mb-0"><?= $totalUsuarios ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-trophy-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Torneos Publicados</h6>
                        <h3 class="fw-bold mb-0"><?= $totalTorneos ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="bi bi-journal-check fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Inscripciones Totales</h6>
                        <h3 class="fw-bold mb-0"><?= $totalInscripciones ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Gestión de Torneos -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Torneos en la Plataforma</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Torneo</th>
                        <th>Organizador</th>
                        <th>Fecha</th>
                        <th>Sede / Estado</th>
                        <th>Ritmo</th>
                        <th>Cupo</th>
                        <th>Inscritos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($torneos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No hay torneos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($torneos as $t): ?>
                            <?php
                                $inscritos = (int)$t['inscritos'];
                                $cupo      = (int)$t['cupo'];
                                $lleno     = $inscritos >= $cupo;
                            ?>
                            <tr>
                                <td>#<?= (int)$t['id_torneo'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($t['nombre']) ?></td>
                                <td><?= htmlspecialchars($t['organizador']) ?></td>
                                <td><?= date('d/m/Y', strtotime($t['fecha_evento'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($t['direccion']) ?>
                                    <span class="badge bg-secondary ms-1"><?= htmlspecialchars($t['nombre_estado']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($t['nombre_ritmo']) ?></td>
                                <td>
                                    <span class="badge <?= $lleno ? 'bg-danger' : 'bg-success' ?>">
                                        <?= $inscritos ?> / <?= $cupo ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#inscritos-<?= (int)$t['id_torneo'] ?>" aria-expanded="false" title="Ver jugadores inscritos">
                                        <i class="bi bi-people-fill me-1"></i><?= $inscritos ?>
                                    </button>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="detalles.php?id=<?= (int)$t['id_torneo'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="formulario_torneo.php?id=<?= (int)$t['id_torneo'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminarTorneo"
                                            data-id="<?= (int)$t['id_torneo'] ?>"
                                            data-titulo="<?= htmlspecialchars($t['nombre']) ?>"
                                            title="Eliminar torneo">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="inscritos-<?= (int)$t['id_torneo'] ?>">
                                <td colspan="9" class="bg-light">
                                    <strong class="small text-uppercase text-muted">Jugadores inscritos en <?= htmlspecialchars($t['nombre']) ?>:</strong>
                                    <?php $lista = $inscritosPorTorneo[(int)$t['id_torneo']] ?? []; ?>
                                    <?php if (empty($lista)): ?>
                                        <p class="mb-0 mt-1 text-muted small">Sin jugadores inscritos todavía.</p>
                                    <?php else: ?>
                                        <ul class="mb-0 mt-1 small">
                                            <?php foreach ($lista as $j): ?>
                                                <li><?= htmlspecialchars($j['nombre_completo']) ?> (<?= htmlspecialchars($j['categoria']) ?> • Elo <?= (int)$j['elo_actual'] ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla de Gestión de Usuarios -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Usuarios y Permisos</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>ID FIDE / Rating</th>
                        <th>Rol Actual</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>#<?= (int)$u['id_usuario'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($u['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td>
                                <small class="text-muted">
                                    FIDE: <?= $u['id_fide'] ? htmlspecialchars($u['id_fide']) : 'N/A' ?> |
                                    Rating: <?= (int)$u['elo_actual'] ?>
                                </small>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="accion" value="cambiar_rol">
                                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                    <select name="id_rol" class="form-select form-select-sm" style="width: auto;" <?= (int)$u['id_usuario'] === (int)$_SESSION['id_usuario'] ? 'disabled' : '' ?>>
                                        <option value="1" <?= (int)$u['id_rol'] === 1 ? 'selected' : '' ?>>Administrador</option>
                                        <option value="2" <?= (int)$u['id_rol'] === 2 ? 'selected' : '' ?>>Organizador</option>
                                        <option value="3" <?= (int)$u['id_rol'] === 3 ? 'selected' : '' ?>>Jugador</option>
                                    </select>
                                    <?php if ((int)$u['id_usuario'] !== (int)$_SESSION['id_usuario']): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Guardar rol">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td class="text-end">
                                <?php if ((int)$u['id_usuario'] !== (int)$_SESSION['id_usuario']): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminarUsuario"
                                            data-id="<?= (int)$u['id_usuario'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre_completo']) ?>"
                                            title="Eliminar usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Sesión actual</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar Torneo -->
<div class="modal fade" id="modalEliminarTorneo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Torneo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="eliminar_torneo">
                    <input type="hidden" name="id_torneo" id="inputIdTorneo">
                    <p class="mb-0">¿Estás seguro de eliminar el torneo <strong id="textoTituloTorneo"></strong>? Se borrarán también todas sus inscripciones asociadas.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar Usuario -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="eliminar_usuario">
                    <input type="hidden" name="id_usuario" id="inputIdUsuario">
                    <p class="mb-0">¿Estás seguro de eliminar al usuario <strong id="textoNombreUsuario"></strong>? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalTorneo = document.getElementById('modalEliminarTorneo');
    if (modalTorneo) {
        modalTorneo.addEventListener('show.bs.modal', (event) => {
            const btn = event.relatedTarget;
            document.getElementById('inputIdTorneo').value = btn.getAttribute('data-id');
            document.getElementById('textoTituloTorneo').textContent = btn.getAttribute('data-titulo');
        });
    }

    const modalUsuario = document.getElementById('modalEliminarUsuario');
    if (modalUsuario) {
        modalUsuario.addEventListener('show.bs.modal', (event) => {
            const btn = event.relatedTarget;
            document.getElementById('inputIdUsuario').value = btn.getAttribute('data-id');
            document.getElementById('textoNombreUsuario').textContent = btn.getAttribute('data-nombre');
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
