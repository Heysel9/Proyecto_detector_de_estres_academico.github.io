// --- LÓGICA DINÁMICA DEL DASHBOARD ---
document.addEventListener('DOMContentLoaded', () => {

    // =============================================
    // 1. Saludo según la hora
    // =============================================
    const actualizarBienvenida = () => {
        const hora = new Date().getHours();
        const titulo = document.querySelector('.hero-module h2');
        let saludo = "";

        if (hora >= 5 && hora < 12)       saludo = "¡Buenos días, Recluta! ☀️";
        else if (hora >= 12 && hora < 19) saludo = "¡Buenas tardes! ☕";
        else                              saludo = "¡Buenas noches, querido estudiante! 🌙";

        if (titulo) titulo.innerText = saludo;
    };

    // =============================================
    // 2. Proverbios Dinámicos
    // =============================================
    const proverbios = [
        "«Si no peleas, no puedes ganar».",
        "«La diferencia entre un novato y un maestro es que el maestro ha fallado más veces».",
        "«Entregar tu corazón significa darlo todo por tu meta».",
        "«En la ingeniería, lo que no se mide, no se puede mejorar».",
        "«No te detengas hasta que te sientas orgulloso de lo que has construido».",
        "«Incluso en el caos, un buen código es orden»."
    ];

    const inyectarProverbio = (mostrar) => {
        const p = document.getElementById('daily-proverb');
        if (!p) return;
        if (mostrar) {
            p.innerText = proverbios[Math.floor(Math.random() * proverbios.length)];
            p.style.opacity = "1";
        } else {
            p.style.opacity = "0";
        }
    };

    // =============================================
    // 3. Animación de Fade In para las Cards
    // =============================================
    const animarDashboard = () => {
        const cards = document.querySelectorAll('.mini-card');
        const hero  = document.querySelector('.hero-module');

        [hero, ...cards].forEach(el => {
            if (el) {
                el.style.opacity   = "0";
                el.style.transform = "translateY(20px)";
                el.style.transition = "all 0.6s ease-out";
            }
        });

        setTimeout(() => {
            if (hero) {
                hero.style.opacity   = "1";
                hero.style.transform = "translateY(0)";
            }
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity   = "1";
                    card.style.transform = "translateY(0)";
                }, 200 * (index + 1));
            });
        }, 300);
    };

    // =============================================
    // 4. Lámpara — escucha mensajes del iframe
    // =============================================
    window.addEventListener('message', (event) => {
        if (event.data === 'encendido')  inyectarProverbio(true);
        if (event.data === 'apagado')    inyectarProverbio(false);
    });

    // =============================================
    // 5. Efecto "Active" en el menú
    // =============================================
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            menuItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // =============================================
    // 6. Modo Descanso (Eye Care)
    // =============================================
    const btnEyeCare = document.getElementById('btn-eye-care');
    if (btnEyeCare) {
        btnEyeCare.addEventListener('click', () => {
            document.documentElement.classList.toggle('eye-care-mode');
        });
    }

    // Ejecutar al cargar
    actualizarBienvenida();
    animarDashboard();
});