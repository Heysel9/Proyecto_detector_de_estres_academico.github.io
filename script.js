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


// --- LÓGICA PARA EL REGISTRO (EL MOTOR) ---
const signupForm = document.querySelector('.signup-container form');

if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Evita que la página se recargue sola

        // 1. Capturamos los datos de los inputs del formulario
        const nombre = signupForm.querySelector('input[type="text"]').value;
        const email = signupForm.querySelector('input[type="email"]').value;
        const password = signupForm.querySelector('input[type="password"]').value;

        try {
            // 2. Enviamos los datos al servidor que tienes en el puerto 3000
            const response = await fetch('https://proyectodetectordeestresacademicogithubio-production.up.railway.app/registro', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre, email, password })
            });

            const data = await response.json();

            if (response.ok) {
                alert("¡Registro exitoso! Ya puedes revisar Railway.");
                signupForm.reset(); // Limpia el formulario
            } else {
                alert("Error al registrar: " + data.error);
            }
        } catch (error) {
            console.error("Error en la conexión:", error);
            alert("No se pudo conectar con el servidor. ¿Está encendido en VS Code?");
        }
    });
}