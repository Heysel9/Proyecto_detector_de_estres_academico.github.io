// --- LÓGICA DINÁMICA DEL DASHBOARD CORREGIDA ---
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Saludo según la hora (Sin cambios)
    const actualizarBienvenida = () => {
        const hora = new Date().getHours();
        const titulo = document.querySelector('.hero-module h2');
        let saludo = "";

        if (hora >= 5 && hora < 12) saludo = "¡Buenos días, Recluta! ☀️";
        else if (hora >= 12 && hora < 19) saludo = "¡Buenas tardes! ☕";
        else saludo = "¡Buenas noches, querido estudiante! 🌙";

        if(titulo) titulo.innerText = saludo;
    };

    // 2. Proverbios Dinámicos, frases motivadoras no motivadoras a las 2AM (Sin cambios en la lista (por el momento))
    const proverbios = [
        "«Si no peleas, no puedes ganar».",
        "«La diferencia entre un novato y un maestro es que el maestro ha fallado más veces».",
        "«Entregar tu corazón significa darlo todo por tu meta».",
        "«En la ingeniería, lo que no se mide, no se puede mejorar».",
        "«No te detengas hasta que te sientas orgulloso de lo que has construido».",
        "«Incluso en el caos, un buen código es orden»."
    ];

    // FUNCIÓN CORREGIDA (numero????): Ahora maneja la visibilidad del texto
    const inyectarProverbio = (mostrar) => {
        const p = document.getElementById('daily-proverb');
        if(!p) return;

        if (mostrar) {
            const random = Math.floor(Math.random() * proverbios.length);
            p.innerText = proverbios[random];
            p.style.opacity = "1"; // Lo hacemos visible
        } else {
            p.style.opacity = "0"; // Lo ocultamos por completo
        }
    };

    // 3. Animación de "Fade In" para las Cards (Sin cambios)
    const animarDashboard = () => {
        const cards = document.querySelectorAll('.mini-card');
        const hero = document.querySelector('.hero-module');
        
        [hero, ...cards].forEach(el => {
            if(el) {
                el.style.opacity = "0";
                el.style.transform = "translateY(20px)";
                el.style.transition = "all 0.6s ease-out";
            }
        });

        setTimeout(() => {
            if(hero) {
                hero.style.opacity = "1";
                hero.style.transform = "translateY(0)";
            }
            
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = "1";
                    card.style.transform = "translateY(0)";
                }, 200 * (index + 1));
            });
        }, 300);
    };

    // --- CONEXIÓN DINÁMICA CON LA LÁMPARA (Ajustada) ---
    window.addEventListener('message', (event) => {
        // Escuchamos los mensajes específicos que configuramos en la lámpara
        if (event.data === 'encendido') {
            inyectarProverbio(true); // Llama a la función para mostrar
        } else if (event.data === 'apagado') {
            inyectarProverbio(false); // Llama a la función para ocultar
        }
    });

    // Ejecutar todo al cargar
    actualizarBienvenida();
    // ANTES: inyectarProverbio();  <- ¡Esto era lo que forzaba el texto, CUIDADO SI SE BORRA!
    animarDashboard();
});


// --- LÓGICA DEL CALENDARIO DINÁMICO ---
let fechaActual = new Date(); // Guardamos la fecha de hoy

const renderizarCalendario = () => {
    const monthDisplay = document.getElementById('month-display');
    const calendarContent = document.getElementById('calendar-content');
    if (!monthDisplay || !calendarContent) return;

    const año = fechaActual.getFullYear();
    const mes = fechaActual.getMonth();

    const nombresMeses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", 
                         "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    
    // 1. Mostramos Mes y Año en el título
    monthDisplay.innerText = `${nombresMeses[mes]} ${año}`;

    // 2. Cálculos técnicos (Ingeniería de fechas)
    const primerDiaMes = new Date(año, mes, 1).getDay(); // 0 (Dom) a 6 (Sáb)
    const ultimoDiaMes = new Date(año, mes + 1, 0).getDate(); // Total días del mes
    const ultimoDiaMesPasado = new Date(año, mes, 0).getDate(); // Días del mes anterior

    // 3. Crear la cuadrícula (HTML dinámico)
    let html = `<div class="cal-grid">`;
    
    // Nombres de los días
    const diasSemanas = ["do", "lu", "ma", "mi", "ju", "vi", "sa"];
    diasSemanas.forEach(dia => html += `<div class="day-name">${dia}</div>`);

    // Días del mes PASADO (relleno gris)
    for (let i = primerDiaMes; i > 0; i--) {
        html += `<div class="day inactive">${ultimoDiaMesPasado - i + 1}</div>`;
    }

    // Días del mes ACTUAL
    const hoy = new Date();
    for (let i = 1; i <= ultimoDiaMes; i++) {
        // Verificamos si es HOY para ponerle la clase 'active'
        const esHoy = i === hoy.getDate() && mes === hoy.getMonth() && año === hoy.getFullYear() ? "active" : "";
        html += `<div class="day ${esHoy}">${i}</div>`;
    }

    html += `</div>`; // Cerramos la cuadrícula
    calendarContent.innerHTML = html;
};

// 4. Listeners para los botones de navegación
document.getElementById('prev-month')?.addEventListener('click', () => {
    fechaActual.setMonth(fechaActual.getMonth() - 1);
    renderizarCalendario();
});

document.getElementById('next-month')?.addEventListener('click', () => {
    fechaActual.setMonth(fechaActual.getMonth() + 1);
    renderizarCalendario();
});

// 5. ¡funciona el calendario!
renderizarCalendario();