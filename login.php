<?php
require_once 'config/conexion.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Iniciar Sesión - ChessEvents México';
$migaActual   = 'Iniciar Sesión';

$correoPrevio = $_SESSION['correo_login'] ?? '';
unset($_SESSION['correo_login']);

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/alertas.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-dark text-white p-4 text-center">
                    <div class="mb-2">
                        <i class="bi bi-shield-lock-fill text-warning fs-1"></i>
                    </div>
                    <h1 class="h4 fw-bolder mb-1">Bienvenido de nuevo</h1>
                    <p class="small text-light opacity-75 mb-0">Ingresa a tu cuenta para gestionar torneos o inscripciones</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form method="POST" action="backend/auth_login.php">
                        <div class="mb-3">
                            <label for="correo" class="form-label small fw-bold text-muted text-uppercase">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" id="correo" name="correo" required class="form-control bg-light"
                                       value="<?= htmlspecialchars($correoPrevio) ?>" placeholder="usuario@correo.com">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label small fw-bold text-muted text-uppercase">Contraseña</label>
                                <a href="#modalFaqContacto" data-bs-toggle="modal" class="small text-primary text-decoration-none fw-semibold">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" id="password" name="password" required class="form-control bg-light" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-chess-primary w-100 py-2.5 rounded-3 mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar a la Plataforma
                        </button>
                    </form>

                    <div class="text-center border-top pt-3 mt-2">
                        <p class="small text-muted mb-1">¿Aún no tienes una cuenta?</p>
                        <a href="registro.php" class="btn btn-outline-secondary btn-sm fw-bold rounded-3 px-4">
                            Crear Cuenta Nueva
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>