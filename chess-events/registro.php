<?php
require_once 'config/conexion.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Crear Cuenta - ChessEvents México';
$migaActual   = 'Registro de Usuario';

$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);

$estados = $pdo->query("SELECT id_estado, nombre_estado FROM estados ORDER BY nombre_estado ASC")->fetchAll();

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
                        <span class="badge bg-warning text-dark fw-bold text-uppercase mb-1">Nuevo Perfil</span>
                        <h1 class="h4 fw-bolder mb-0">Registro en ChessEvents México</h1>
                    </div>
                    <a href="index.php" class="btn btn-outline-light btn-sm rounded-3 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form method="POST" action="backend/auth_registro.php" id="formRegistro">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2">1. Selecciona tu perfil en la plataforma *</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check border rounded-4 p-3 h-100 bg-light">
                                        <input class="form-check-input ms-1" type="radio" name="id_rol" id="rolJugador" value="3" <?= (($old['id_rol'] ?? 3) == 3) ? 'checked' : '' ?>>
                                        <label class="form-check-label ms-2 d-block cursor-pointer" for="rolJugador">
                                            <span class="fw-bold d-block text-dark"><i class="bi bi-person-badge text-primary me-1"></i> Soy Jugador</span>
                                            <span class="small text-muted">Explorar torneos disponibles e inscribirme a competencias.</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check border rounded-4 p-3 h-100 bg-light">
                                        <input class="form-check-input ms-1" type="radio" name="id_rol" id="rolOrganizador" value="2" <?= (($old['id_rol'] ?? 3) == 2) ? 'checked' : '' ?>>
                                        <label class="form-check-label ms-2 d-block cursor-pointer" for="rolOrganizador">
                                            <span class="fw-bold d-block text-dark"><i class="bi bi-trophy text-primary me-1"></i> Soy Organizador</span>
                                            <span class="small text-muted">Publicar convocatorias oficiales y gestionar jugadores inscritos.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">2. Datos Personales</h2>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="nombre_completo" class="form-label small fw-bold">Nombre Completo (máx. 100 caracteres) *</label>
                                <input type="text" id="nombre_completo" name="nombre_completo" required class="form-control bg-light"
                                       value="<?= htmlspecialchars($old['nombre_completo'] ?? '') ?>" placeholder="Ej. Carlos Torre Repetto">
                            </div>
                            <div class="col-md-6">
                                <label for="correo" class="form-label small fw-bold">Correo Electrónico *</label>
                                <input type="email" id="correo" name="correo" required class="form-control bg-light"
                                       value="<?= htmlspecialchars($old['correo'] ?? '') ?>" placeholder="ejemplo@correo.com">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label small fw-bold">Teléfono / WhatsApp (10 dígitos) *</label>
                                <input type="tel" id="telefono" name="telefono" required pattern="[0-9]{10}" maxlength="10" class="form-control bg-light"
                                       value="<?= htmlspecialchars($old['telefono'] ?? '') ?>" placeholder="5512345678">
                            </div>
                            <div class="col-md-6">
                                <label for="id_estado" class="form-label small fw-bold">Estado de la República *</label>
                                <select id="id_estado" name="id_estado" required class="form-select bg-light">
                                    <option value="">Selecciona tu estado...</option>
                                    <?php foreach ($estados as $est): ?>
                                        <option value="<?= $est['id_estado'] ?>" <?= (($old['id_estado'] ?? '') == $est['id_estado']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($est['nombre_estado']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">3. Información Ajedrecística (Opcional)</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="id_fide" class="form-label small fw-bold">ID FIDE</label>
                                <input type="text" id="id_fide" name="id_fide" maxlength="20" class="form-control bg-light"
                                       value="<?= htmlspecialchars($old['id_fide'] ?? '') ?>" placeholder="Ej. 5110981">
                                <div class="form-text">Déjalo vacío si aún no cuentas con registro internacional.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="elo_actual" class="form-label small fw-bold">Rating / Elo Actual</label>
                                <input type="number" id="elo_actual" name="elo_actual" min="0" max="3000" class="form-control bg-light"
                                       value="<?= htmlspecialchars($old['elo_actual'] ?? '0') ?>">
                                <div class="form-text">Indica 0 si participas en categoría aficionado sin rating.</div>
                            </div>
                        </div>

                        <h2 class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">4. Seguridad de la Cuenta</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-bold">Contraseña (mín. 8 caracteres, letras y números) *</label>
                                <input type="password" id="password" name="password" required minlength="8" class="form-control bg-light" placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label small fw-bold">Confirmar Contraseña *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="8" class="form-control bg-light" placeholder="••••••••">
                                <div id="feedbackPass" class="small fw-bold mt-1 d-none"></div>
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit" class="btn btn-chess-primary w-100 py-3 rounded-3 text-uppercase fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Completar Registro
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="small text-muted mb-0">
                            ¿Ya tienes una cuenta registrada? <a href="login.php" class="fw-bold text-decoration-none">Inicia sesión aquí</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const pass = document.getElementById('password');
    const confirmPass = document.getElementById('confirm_password');
    const feedback = document.getElementById('feedbackPass');
    const btnSubmit = document.getElementById('btnSubmit');

    function validarCoincidencia() {
        if (!confirmPass.value) {
            feedback.classList.add('d-none');
            return;
        }
        feedback.classList.remove('d-none');
        if (pass.value === confirmPass.value) {
            feedback.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Las contraseñas coinciden';
            feedback.className = 'small fw-bold mt-1 text-success';
            btnSubmit.disabled = false;
        } else {
            feedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Las contraseñas no coinciden';
            feedback.className = 'small fw-bold mt-1 text-danger';
            btnSubmit.disabled = true;
        }
    }

    pass.addEventListener('input', validarCoincidencia);
    confirmPass.addEventListener('input', validarCoincidencia);
</script>

<?php require_once 'includes/footer.php'; ?>