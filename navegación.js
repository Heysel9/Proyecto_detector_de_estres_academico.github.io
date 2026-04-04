/**
 * SISTEMA DE NAVEGACIÓN DE LAS GRAFICAS
 * Controla los saltos entre módulos sin romper el Dashboard principal (IMPORTANTE PORQUE SE ROMPIO 50).
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log("🚀 Módulo de navegación activado");

    // Función para conectar un botón
    const conectarPagina = (idBoton, archivoDestino) => {
        const boton = document.getElementById(idBoton);
        if (boton) {
            boton.addEventListener('click', (e) => {
                e.preventDefault();
                console.log(`Navegando a: ${archivoDestino}`);
                window.location.href = archivoDestino;
            });
        }
    };

    // CONEXIONES (Asegúrate de que los IDs coincidan en el index.html en caso de modificar)
    conectarPagina('btn-progreso', 'mi-progreso.html');
    conectarPagina('btn-dashboard', 'index.html');
    
    // Si har otros botones, agrégalos aquí abajo:
    // conectarPagina('btn-bienestar', 'bienestar.html');
});

// --- 1. Se ejecuta apenas carga la página ---
document.addEventListener('DOMContentLoaded', () => {
    const loginWrapper = document.getElementById('main-wrapper');
    const dashboard = document.getElementById('dashboard-view');

    // Revisamos si el usuario ya inició sesión antes
    if (localStorage.getItem('sesionActiva') === 'true') {
        loginWrapper.style.display = 'none';
        dashboard.style.display = 'flex';
        dashboard.style.opacity = '1';
    }
});

// --- 2. EL LOGIN (Maneja el formulario) ---
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault(); 

    const loginWrapper = document.getElementById('main-wrapper');
    const dashboard = document.getElementById('dashboard-view');

    // GUARDAMOS LA SESIÓN: El "papelito" (facil de recordad para mi) para que el navegador recuerde
    localStorage.setItem('sesionActiva', 'true');

    // Animación de salida del Login
    loginWrapper.style.transition = 'opacity 0.5s ease';
    loginWrapper.style.opacity = '0';

    setTimeout(() => {
        loginWrapper.style.display = 'none';
        
        // Mostramos el Dashboard
        dashboard.style.display = 'flex';
        dashboard.style.opacity = '0';
        
        setTimeout(() => {
            dashboard.style.transition = 'opacity 0.8s ease';
            dashboard.style.opacity = '1';
        }, 50);
    }, 500);
});

// --- 3. CERRAR SESIÓN (Para poder volver al login de verdad) ---
const btnCerrar = document.getElementById('btn-cerrar');
if (btnCerrar) {
    btnCerrar.addEventListener('click', () => {
        // Borramos el "papelito" de la memoria
        localStorage.removeItem('sesionActiva');
        // Recargamos la página para volver al estado inicial (Login)
        window.location.reload();
    });
}

//Igual que en el dasboard.js si lo quitan se traba, no pregunten por qué, ni yo se
document.addEventListener('DOMContentLoaded', () => {
    const loginWrapper = document.getElementById('main-wrapper');
    const dashboard = document.getElementById('dashboard-view');
    const sesion = localStorage.getItem('sesionActiva');

    if (sesion === 'true') {
        // CASO A: Ya estás logueado -> Dashboard directo
        dashboard.style.display = 'flex';
        dashboard.style.opacity = '1';
        loginWrapper.style.display = 'none'; // Por seguridad
    } else {
        // CASO B: No hay sesión -> Mostramos el Login
        loginWrapper.style.display = 'flex';
        setTimeout(() => {
            loginWrapper.style.opacity = '1';
            loginWrapper.style.transition = 'opacity 0.5s ease';
        }, 10);
    }
});