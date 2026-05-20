<?php
// Mantenemos tu conexión intacta
$host = "localhost";
$port = "5432";
$dbname = "Detector";
$user = "postgres";
$pass = "Adwyack104";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta original
    $stmt = $pdo->prepare('SELECT "fecha_realizacion", "puntaje_total" FROM "resultados_tests" WHERE "id_usuario" = 6 ORDER BY "fecha_realizacion" ASC');
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // LÓGICA DE AGRUPACIÓN CADA 3 DÍAS
    $agrupados = [];
    foreach ($res as $index => $row) {
        $grupo = floor($index / 3);
        if (!isset($agrupados[$grupo])) {
            $agrupados[$grupo] = ['total' => 0, 'count' => 0, 'fecha' => date("d/m", strtotime($row['fecha_realizacion']))];
        }
        $agrupados[$grupo]['total'] += (int)$row['puntaje_total'];
        $agrupados[$grupo]['count']++;
    }

    $l = []; $p = [];
    foreach ($agrupados as $g) {
        $l[] = $g['fecha'];
        $p[] = round($g['total'] / $g['count']);
    }
    
    $js_labels = json_encode($l);
    $js_data = json_encode($p);

} catch (Exception $e) {
    $js_labels = "[]"; $js_data = "[]";
}
?>

<div style="width: 100%; max-width: 900px; padding: 25px; border-radius: 20px; 
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px);">
    <div style="height: 250px; width: 100%;">
        <canvas id="graficaFinal"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('graficaFinal').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $js_labels; ?>,
            datasets: [{
                data: <?php echo $js_data; ?>,
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return '#3b82f6';
                    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0, '#1e3a8a');
                    gradient.addColorStop(1, '#3b82f6');
                    return gradient;
                },
                borderRadius: 8,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }, // Líneas sutiles
                    ticks: { color: '#94a3b8', font: { size: 11 } } 
                },
                x: { 
                    grid: { display: false }, 
                    ticks: { color: '#94a3b8', font: { size: 11 } } 
                }
            }
        }
    });
});
</script>