<?php if (isset($_SESSION['alerta'])): 
    $tipo = $_SESSION['alerta']['tipo'] ?? 'info';
    $texto = $_SESSION['alerta']['mensaje'] ?? '';
    
    $iconos = [
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info'    => 'bi-info-circle-fill'
    ];
    $icono = $iconos[$tipo] ?? 'bi-info-circle-fill';
?>
<div class="container mt-3">
    <div class="alert alert-<?= htmlspecialchars($tipo) ?> alert-dismissible fade show shadow-sm rounded-4 d-flex align-items-center gap-2 fw-semibold" role="alert">
        <i class="bi <?= $icono ?> fs-5"></i>
        <div><?= htmlspecialchars($texto) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
</div>
<?php 
    unset($_SESSION['alerta']); 
endif; 
?>