// =============================================
//  TOGGLE LOGIN / REGISTRO
// =============================================
document.addEventListener('DOMContentLoaded', () => {

    const container      = document.getElementById('main-wrapper');
    const btnLoginSwap   = document.getElementById('btn-login-swap');
    const btnSignupSwap  = document.getElementById('btn-signup-swap');
    const btnLoginSwap2  = document.getElementById('btn-login-swap-2');
    const btnSignupSwap2 = document.getElementById('btn-signup-swap-2');

    // Usa la clase CSS active-signup para activar las animaciones del overlay
    function mostrarLogin() {
        container?.classList.remove('active-signup');
    }

    function mostrarRegistro() {
        container?.classList.add('active-signup');
    }

    btnLoginSwap?.addEventListener('click',   mostrarLogin);
    btnSignupSwap?.addEventListener('click',  mostrarRegistro);
    btnLoginSwap2?.addEventListener('click',  mostrarLogin);
    btnSignupSwap2?.addEventListener('click', mostrarRegistro);

    // =============================================
    //  ANIMACIÓN DE ENTRADA (pantalla de login)
    // =============================================
    if (container) {
        container.style.display = 'flex';
        setTimeout(() => { container.style.opacity = '1'; }, 50);
    }

    // =============================================
    //  MODAL: OLVIDÉ MI CONTRASEÑA
    // =============================================
    const forgotLink  = document.querySelector('.forgot-link');
    const modalForgot = document.getElementById('modal-forgot');
    const closeForgot = document.getElementById('close-forgot');

    forgotLink?.addEventListener('click', (e) => {
        e.preventDefault();
        if (modalForgot) modalForgot.style.display = 'flex';
    });

    closeForgot?.addEventListener('click', () => {
        if (modalForgot) modalForgot.style.display = 'none';
    });

    modalForgot?.addEventListener('click', (e) => {
        if (e.target === modalForgot) modalForgot.style.display = 'none';
    });

    // =============================================
    //  MODO DESCANSO (Eye Care)
    // =============================================
    const btnEyeCare = document.getElementById('btn-eye-care');

    btnEyeCare?.addEventListener('click', () => {
        document.documentElement.classList.toggle('eye-care-mode');
        const isActive = document.documentElement.classList.contains('eye-care-mode');
        const span = btnEyeCare.querySelector('.text');
        if (span) span.textContent = isActive ? 'Modo Normal' : 'Modo Descanso';
    });

    // =============================================
    //  CALENDARIO
    // =============================================
    let currentDate = new Date();

    function renderCalendar(date) {
        const monthDisplay    = document.getElementById('month-display');
        const calendarContent = document.getElementById('calendar-content');
        if (!monthDisplay || !calendarContent) return;

        const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        const year  = date.getFullYear();
        const month = date.getMonth();
        const today = new Date();

        monthDisplay.textContent = `${months[month]} ${year}`;

        const firstDay    = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let html = '<div class="calendar-grid">';
        ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'].forEach(d => {
            html += `<div class="cal-header">${d}</div>`;
        });

        for (let i = 0; i < firstDay; i++) html += '<div></div>';

        for (let d = 1; d <= daysInMonth; d++) {
            const isToday = d === today.getDate() &&
                            month === today.getMonth() &&
                            year  === today.getFullYear();
            html += `<div class="cal-day${isToday ? ' today' : ''}">${d}</div>`;
        }
        html += '</div>';
        calendarContent.innerHTML = html;
    }

    document.getElementById('prev-month')?.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    document.getElementById('next-month')?.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    renderCalendar(currentDate);

    // =============================================
    //  LÁMPARA — PROVERBIO DIARIO
    // =============================================
    const proverbios = [
        "El conocimiento es la luz que ilumina el camino.",
        "Cada día es una nueva oportunidad para aprender.",
        "La perseverancia vence lo que la inteligencia no alcanza.",
        "Estudia como si nunca supieras suficiente.",
        "El esfuerzo de hoy es el éxito de mañana.",
        "La mente que se abre a una nueva idea nunca vuelve a su tamaño original.",
        "Aprender sin pensar es tiempo perdido.",
    ];

    window.addEventListener('message', (event) => {
        if (event.data === 'lampara-click') {
            const proverb = document.getElementById('daily-proverb');
            if (proverb) {
                proverb.textContent = proverbios[Math.floor(Math.random() * proverbios.length)];
                proverb.style.opacity = '1';
            }
        }
    });

}); // Fin DOMContentLoaded