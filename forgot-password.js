document.addEventListener('DOMContentLoaded', () => {
    console.log("🛠️ Script de recuperación cargado"); // Esto debe salir en F12

    const modal = document.getElementById('modal-forgot');
    const forgotLink = document.querySelector('.forgot-link');
    const closeBtn = document.getElementById('close-forgot');

    if (!forgotLink) console.error("❌ No encontré el enlace .forgot-link");
    if (!modal) console.error("❌ No encontré el modal #modal-forgot");

    if (forgotLink && modal) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            console.log("🖱️ Clic detectado en Olvidé Contraseña");
            modal.classList.add('active');
            modal.style.display = 'flex'; // Forzamos el display por si el CSS falla
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        });
    }
});