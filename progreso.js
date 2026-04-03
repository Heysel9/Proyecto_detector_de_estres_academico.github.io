document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('graficaProgreso').getContext('2d');

    // DATOS DE PRUEBA (Esto vendrá de Postgres después)
    const datosEestres = [5, 7, 4, 8, 3, 9, 2]; 
    const etiquetasDias = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: etiquetasDias,
            datasets: [{
                label: 'Nivel de Estrés (1-10)',
                data: datosEestres,
                borderColor: '#6c5ce7',
                backgroundColor: 'rgba(108, 92, 231, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, max: 10 }
            }
        }
    });

    // Lógica simple para el texto de estado
    const ultimoDato = datosEestres[datosEestres.length - 1];
    const estadoMsg = document.getElementById('estado-texto');
    if(ultimoDato > 7) {
        estadoMsg.innerText = "¡Cuidado! Tu nivel de estrés es alto. Considera un descanso.";
        estadoMsg.style.color = "red";
    } else {
        estadoMsg.innerText = "Vas por buen camino. ¡Sigue así!";
        estadoMsg.style.color = "green";
    }
});