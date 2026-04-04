/**
 * SISTEMA DE NAVEGACIÓN
 * Solo maneja los saltos entre módulos.
 * El login/sesión lo maneja PHP — NO tocar eso aquí.
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log("🚀 Módulo de navegación activado");

    // =============================================
    // Navegación entre páginas
    // =============================================
    const conectarPagina = (idBoton, archivoDestino) => {
        const boton = document.getElementById(idBoton);
        if (boton) {
            boton.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = archivoDestino;
            });
        }
    };

    conectarPagina('btn-progreso',  'mi-progreso.html');
    conectarPagina('btn-dashboard', 'index.php');

    // Si hay otros botones agrégalos aquí:
    // conectarPagina('btn-bienestar', 'bienestar.html');

    // =============================================
    // Cerrar Sesión — redirige al logout de PHP
    // =============================================
    const btnCerrar = document.getElementById('btn-cerrar');
    if (btnCerrar) {
        btnCerrar.addEventListener('click', () => {
            window.location.href = 'logout.php';
        });
    }
});