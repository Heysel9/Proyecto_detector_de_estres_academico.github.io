document.addEventListener('DOMContentLoaded', () => {
    // Referencias a los botones de intercambio
    const btnSignup = document.getElementById('btn-signup-swap');
    const btnSignup2 = document.getElementById('btn-signup-swap-2');
    const btnLogin = document.getElementById('btn-login-swap');
    const btnLogin2 = document.getElementById('btn-login-swap-2');
    
    const container = document.getElementById('main-wrapper');

    // Funciones para cambiar de estado
    const showSignup = () => container.classList.add('active-signup');
    const showLogin = () => container.classList.remove('active-signup');

    // Asignar eventos a los botones
    if (btnSignup) btnSignup.addEventListener('click', showSignup);
    if (btnSignup2) btnSignup2.addEventListener('click', showSignup);
    if (btnLogin) btnLogin.addEventListener('click', showLogin);
    if (btnLogin2) btnLogin2.addEventListener('click', showLogin);
});

document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita que la página se recargue

    // 1. Ocultamos el login con una transición
    const loginWrapper = document.getElementById('main-wrapper');
    loginWrapper.style.transition = 'opacity 0.5s ease';
    loginWrapper.style.opacity = '0';

    setTimeout(() => {
        loginWrapper.style.display = 'none';
        
        // 2. Mostramos el Dashboard
        const dashboard = document.getElementById('dashboard-view');
        dashboard.style.display = 'flex';
        dashboard.style.opacity = '0';
        
        // Efecto de aparición suave
        setTimeout(() => {
            dashboard.style.transition = 'opacity 0.8s ease';
            dashboard.style.opacity = '1';
        }, 50);
    }, 500);
});

