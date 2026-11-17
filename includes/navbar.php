<?php
$paginaActual = basename($_SERVER['PHP_SELF']);
$logueado     = isset($_SESSION['id_usuario']);
$idRol        = $_SESSION['id_rol'] ?? 0;
$nombreUser   = $_SESSION['nombre'] ?? '';
$nombreRol    = $_SESSION['nombre_rol'] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-chess sticky-top shadow-sm py-2">
    <div class="container">
        <!-- Marca / Logo con SVG vectorial -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bolder text-dark" href="index.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#3730a3" viewBox="0 0 24 24">
                <path d="M19 22H5v-2h14v2M17.16 8.26A8.94 8.94 0 0 0 13 6.22V4h2V2h-2V0h-2v2H9v2h2v2.22c-1.56.37-2.97 1.1-4.16 2.04L5 10.5l1.5 1.5l1.84-1.54A6.97 6.97 0 0 1 12 9c1.38 0 2.66.4 3.66 1.46L17.5 12l1.5-1.5l-1.84-2.24M7 18h10l-1-4H8l-1 4Z"/>
            </svg>
            <span class="fs-5 tracking-tight">ChessEvents <span class="badge fs-6 fw-bold" style="background:#e0e7ff; color:#3730a3;">MX</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <!-- Enlaces centrales según el Rol -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-1">
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3 rounded-3 <?= $paginaActual === 'index.php' ? 'active bg-light text-primary fw-bold' : '' ?>" href="index.php">
                        <i class="bi bi-trophy me-1"></i> Cartelera de Torneos
                    </a>
                </li>

                <?php if ($logueado && $idRol === 3): // ROL JUGADOR ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 rounded-3 <?= $paginaActual === 'mis_inscripciones.php' ? 'active bg-light text-primary fw-bold' : '' ?>" href="mis_inscripciones.php">
                            <i class="bi bi-card-checklist me-1"></i> Mis Inscripciones
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($logueado && $idRol === 2): // ROL ORGANIZADOR ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 rounded-3 <?= $paginaActual === 'mis_torneos.php' ? 'active bg-light text-primary fw-bold' : '' ?>" href="mis_torneos.php">
                            <i class="bi bi-kanban me-1"></i> Mis Torneos Publicados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 rounded-3 <?= $paginaActual === 'formulario_torneo.php' ? 'active bg-light text-primary fw-bold' : '' ?>" href="formulario_torneo.php">
                            <i class="bi bi-plus-circle me-1"></i> Publicar Convocatoria
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($logueado && $idRol === 1): // ROL ADMINISTRADOR ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 rounded-3 <?= $paginaActual === 'admin.php' ? 'active bg-light text-primary fw-bold' : '' ?>" href="admin.php">
                            <i class="bi bi-shield-lock me-1"></i> Panel Administrador
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3 rounded-3 <?= $paginaActual === 'formulario_torneo.php' ? 'active bg-light text-primary fw-bold' : '' ?>" href="formulario_torneo.php">
                            <i class="bi bi-plus-circle me-1"></i> Nuevo Torneo
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Acciones de Sesión (Derecha) -->
            <div class="d-flex align-items-center gap-2">
                <!-- Botón de Ayuda Contextual -->
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalAyuda" title="Guía rápida de uso">
                    <i class="bi bi-question-circle me-1"></i> Ayuda
                </button>

                <?php if ($logueado): ?>
                    <div class="dropdown">
    <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2 rounded-3 fw-semibold btn-sm py-2 px-3" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle text-primary"></i>
        <span><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $nombreUser), 0, 2))) ?></span>
        <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($nombreRol) ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
        <li class="px-3 py-2 border-bottom">
            <p class="mb-0 fw-bold small"><?= htmlspecialchars($nombreUser) ?></p>
            <p class="mb-0 text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($_SESSION['correo'] ?? '') ?></p>
        </li>
        <li>
            <a class="dropdown-item fw-semibold py-2 mt-1" href="perfil.php">
                <i class="bi bi-person-gear me-2 text-primary"></i> Mi Perfil
            </a>
        </li>
        <li>
            <a class="dropdown-item text-danger fw-semibold py-2 mt-1" href="backend/auth_logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
            </a>
        </li>
    </ul>
</div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary btn-sm fw-bold rounded-3 px-3 py-2">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-chess-primary btn-sm rounded-3 px-3 py-2">Crear Cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Migas de Pan / Breadcrumbs -->
<?php if (isset($migaActual) && $paginaActual !== 'index.php'): ?>
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none fw-semibold"><i class="bi bi-house-door me-1"></i>Inicio</a></li>
                <li class="breadcrumb-item active fw-bold text-dark" aria-current="page"><?= htmlspecialchars($migaActual) ?></li>
            </ol>
        </nav>
    </div>
</div>
<?php endif; ?>