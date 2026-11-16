<?php
require_once 'config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$tituloPagina = 'Mi Perfil - ChessEvents México';
$migaActual   = 'Mi Perfil';
$idUsuario    = $_SESSION['id_usuario'];

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch();

$estados = $pdo->query("SELECT id_estado, nombre_estado FROM estados ORDER BY nombre_estado ASC")->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-4">
    <div class="row justify-content-center g-4">
        <div class="col-lg-8">
            <div class="mb-3">
                <h1 class="h3 fw-bolder text-dark mb-1">Configuración de Perfil</h1>
                <p class="text-muted small mb-0">Actualiza tu información personal, rating o credenciales de acceso.</p>
            </div>

            <!-- Formulario Datos Personales -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-person-vcard text-warning me-2"></i>Información Personal y Ajedrecística</h2>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="backend/perfil_actualizar.php">
                        <input type="hidden" name="accion" value="actualizar_datos">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre Completo *</label>
                                <input type="text" name="nombre_completo" maxlength="100" required class="form-control bg-light" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Correo Electrónico (No editable)</label>
                                <input type="email" class="form-control bg-light text-muted" value="<?= htmlspecialchars($usuario['correo']) ?>" disabled>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Teléfono / WhatsApp (10 dígitos) *</label>
                                <input type="tel" name="telefono" pattern="[0-9]{10}" maxlength="10" required class="form-control bg-light" value="<?= htmlspecialchars($usuario['telefono']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Estado *</label>
                                <select name="id_estado" required class="form-select bg-light">
                                    <?php foreach ($estados as $est): ?>
                                        <option value="<?= $est['id_estado'] ?>" <?= $usuario['id_estado'] == $est['id_estado'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($est['nombre_estado']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ID FIDE</label>
                                <input type="text" name="id_fide" maxlength="20" class="form-control bg-light" value="<?= htmlspecialchars($usuario['id_fide'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Rating / Elo Actual</label>
                                <input type="number" name="elo_actual" min="0" max="3000" class="form-control bg-light" value="<?= (int)$usuario['elo_actual'] ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-chess-primary px-4 py-2 rounded-3">
                            <i class="bi bi-check2-circle me-1"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>

            <!-- Formulario Cambio de Contraseña -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-key text-warning me-2"></i>Cambiar Contraseña</h2>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="backend/perfil_actualizar.php">
                        <input type="hidden" name="accion" value="cambiar_password">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Contraseña Actual *</label>
                                <input type="password" name="pass_actual" required class="form-control bg-light">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nueva Contraseña *</label>
                                <input type="password" name="pass_nueva" required minlength="8" class="form-control bg-light" placeholder="Mín. 8 caracteres, letras y números">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Confirmar Nueva *</label>
                                <input type="password" name="pass_confirm" required minlength="8" class="form-control bg-light">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-dark btn-sm fw-bold px-4 py-2 rounded-3">
                            Actualizar Contraseña
                        </button>
                    </form>
                </div>
            </div>

            <!-- Zona de Eliminación de Cuenta (Control y libertad del usuario) -->
            <?php if ($_SESSION['id_rol'] !== 1): ?>
            <div class="card border border-danger-subtle shadow-sm rounded-4">
                <div class="card-body p-4 bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="h6 fw-bold text-danger mb-1">Eliminar mi cuenta</h3>
                        <p class="small text-muted mb-0">Esta acción eliminará permanentemente tu perfil, torneos creados e inscripciones.</p>
                    </div>
                    <form method="POST" action="backend/perfil_actualizar.php" onsubmit="return confirm('¿Estás completamente seguro de eliminar tu cuenta? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="accion" value="eliminar_cuenta">
                        <button type="submit" class="btn btn-outline-danger btn-sm fw-bold px-3 py-2 rounded-3 text-nowrap">
                            <i class="bi bi-trash3 me-1"></i> Eliminar Cuenta
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>