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
        // Agrupamos en bloques de 3
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
        $p[] = round($g['total'] / $g['count']); // Promedio del bloque
    }
    
    $js_labels = json_encode($l);
    $js_data = json_encode($p);

} catch (Exception $e) {
    $js_labels = "[]"; $js_data = "[]";
}
?>

<div style="height: 300px; width: 100%; background: #ffffff; border-radius: 20px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <canvas id="graficaFinal"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    new Chart(document.getElementById('graficaFinal'), {
        type: 'line',
        data: {
            labels: <?php echo $js_labels; ?>,
            datasets: [{
                data: <?php echo $js_data; ?>,
                borderColor: '#3b82f6',
                borderWidth: 3,
                tension: 0.5, // Curva muy suave, parece un diseño fluido
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                pointRadius: 6,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 40, grid: { color: '#f1f5f9' }, ticks: { color: '#64748b' } },
                x: { grid: { display: false }, ticks: { color: '#64748b' } }
            }
        }
    });
});
</script>