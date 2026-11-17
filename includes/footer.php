<!-- 1. Modal de Ayuda Rápida -->
<div class="modal fade" id="modalAyuda" tabindex="-1" aria-labelledby="modalAyudaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalAyudaLabel">
                    <i class="bi bi-journal-bookmark-fill text-warning"></i> Guía Rápida - ChessEvents
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-primary mb-1"><i class="bi bi-person-badge me-1"></i> ¿Eres Jugador?</h6>
                <p class="small text-muted mb-3">Crea una cuenta seleccionando el rol <strong>Jugador</strong>, explora los torneos disponibles en la cartelera y haz clic en <em>"Ver Convocatoria e Inscribirme"</em> para apartar tu lugar.</p>

                <h6 class="fw-bold text-primary mb-1"><i class="bi bi-trophy me-1"></i> ¿Eres Organizador de Club?</h6>
                <p class="small text-muted mb-3">Regístrate como <strong>Organizador</strong> para publicar convocatorias oficiales, subir el PDF del torneo y consultar la lista de jugadores inscritos.</p>

                <h6 class="fw-bold text-primary mb-1"><i class="bi bi-stopwatch me-1"></i> Ritmos de Juego</h6>
                <ul class="small text-muted mb-0">
                    <li><strong>Clásico:</strong> Partidas de 60 minutos o más por jugador.</li>
                    <li><strong>Rápido:</strong> Partidas entre 10 y 60 minutos.</li>
                    <li><strong>Blitz:</strong> Partidas relámpago menores a 10 minutos.</li>
                </ul>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-3 fw-bold btn-sm px-4" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal de Preguntas Frecuentes, Recuperación de Contraseña y Soporte -->
<div class="modal fade" id="modalFaqContacto" tabindex="-1" aria-labelledby="modalFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalFaqLabel">
                    <i class="bi bi-life-preserver text-warning"></i> Preguntas Frecuentes y Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 small">
                <p class="fw-bold text-dark mb-1"><i class="bi bi-key-fill text-primary me-1"></i> ¿Olvidaste tu contraseña?</p>
                <p class="text-muted mb-3">Por seguridad de tu cuenta, envía un correo a <strong>soporte@chessevents.mx</strong> desde tu dirección registrada indicando tu nombre completo para que el administrador restablezca tu contraseña.</p>
                
                <p class="fw-bold text-dark mb-1"><i class="bi bi-person-gear text-primary me-1"></i> ¿Cómo cambio mi rol de Jugador a Organizador?</p>
                <p class="text-muted mb-3">Si ya tienes cuenta como jugador y deseas organizar torneos, solicita el cambio de permisos al correo de soporte indicando tu correo registrado.</p>

                <p class="fw-bold text-dark mb-1"><i class="bi bi-cash-coin text-primary me-1"></i> ¿Tiene algún costo publicar convocatorias?</p>
                <p class="text-muted mb-0">No. La publicación de torneos y el registro de jugadores en ChessEvents México son completamente gratuitos.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-3 fw-bold btn-sm px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal Global de Confirmación (Sustituye confirm() nativo) -->
<div class="modal fade" id="modalConfirmarAccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4 py-2 px-3">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Confirmar Acción</h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3 small text-dark" id="textoConfirmarAccion">
                ¿Estás seguro de continuar con esta acción?
            </div>
            <div class="modal-footer border-0 pt-0 px-3 pb-3 d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-sm rounded-3 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm rounded-3 fw-bold" id="btnConfirmarEjecutar">Sí, continuar</button>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-secondary py-4 mt-5">
    <div class="container text-center">
        <p class="text-white fw-bold mb-1 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-grid-3x3-gap-fill text-warning"></i> ChessEvents México
        </p>
        <p class="small mb-2">Plataforma oficial para la difusión e inscripción de torneos de ajedrez en la República Mexicana.</p>
        <div class="mb-2">
            <a href="#modalFaqContacto" data-bs-toggle="modal" class="text-warning text-decoration-none small fw-semibold">
                <i class="bi bi-question-circle me-1"></i> Preguntas Frecuentes y Soporte
            </a>
        </div>
        <p class="mb-0" style="font-size: 0.75rem;">© <?= date('Y') ?> ChessEvents México - Proyecto Académico.</p>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle (Debe ir ANTES de nuestros scripts que usan bootstrap.Modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Reemplazo automático de onsubmit="return confirm(...)" por Modal Bootstrap
    let formularioPendiente = null;
    const modalConfirmEl = document.getElementById('modalConfirmarAccion');
    const modalConfirm = modalConfirmEl ? new bootstrap.Modal(modalConfirmEl) : null;

    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
        const attr = form.getAttribute('onsubmit');
        const match = attr.match(/confirm\('([\s\S]+?)'\)/);
        const mensaje = match ? match[1] : '¿Confirmas esta acción?';
        form.removeAttribute('onsubmit');

        form.addEventListener('submit', function(e) {
            if (!form.dataset.confirmado) {
                e.preventDefault();
                formularioPendiente = form;
                document.getElementById('textoConfirmarAccion').textContent = mensaje;
                modalConfirm.show();
            }
        });
    });

    document.getElementById('btnConfirmarEjecutar')?.addEventListener('click', () => {
        if (formularioPendiente) {
            formularioPendiente.dataset.confirmado = '1';
            formularioPendiente.submit();
        }
    });

    // 2. Indicador de carga (Spinner) al enviar cualquier formulario POST
    document.querySelectorAll('form[method="POST"]').forEach(form => {
        form.addEventListener('submit', function() {
            if (form.getAttribute('onsubmit') || (formularioPendiente === form && form.dataset.confirmado !== '1')) return;
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Procesando...';
                }, 10);
            }
        });
    });
</script>
</body>
</html>