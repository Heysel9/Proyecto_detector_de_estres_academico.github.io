// SIMULACIÓN: Esto es lo que eventualmente traerás con un 'fetch' de Railway
const datosDesdeBD = [
    { fecha: '2026-04-01', nivel: 3 },
    { fecha: '2026-04-02', nivel: 7 },
    { fecha: '2026-04-03', nivel: 5 },
    { fecha: '2026-04-04', nivel: 9 }, // Alguien tuvo un parcial aquí jaja
];

// Función para dibujar la gráfica
const ctx = document.getElementById('miGrafica').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line', // Tipo de gráfica (línea, barra, etc.)
    data: {
        labels: datosDesdeBD.map(d => d.fecha), // Sacamos las fechas para el eje X
        datasets: [{
            label: 'Estrés del Estudiante',
            data: datosDesdeBD.map(d => d.nivel), // Sacamos los niveles para el eje Y
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(75, 192, 192, 0.2)'
        }]
    },
    options: {
        scales: { y: { beginAtZero: true, max: 10 } }
    }
});