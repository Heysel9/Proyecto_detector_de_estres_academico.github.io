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