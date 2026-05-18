<?php
// 1. FORZAR CONEXIÓN
$host = "localhost";
$port = "5432";
$dbname = "Detector"; // <--- VERIFICA QUE SEA EXACTAMENTE ESTE NOMBRE
$user = "postgres";
$pass = "Adwyack104"; // <--- CAMBIA ESTO POR TU CONTRASEÑA REAL

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // DEBUG: Vamos a ver si la tabla existe de verdad para PHP
    $check = $pdo->query("SELECT count(*) FROM information_schema.tables WHERE table_name = 'resultados_tests'")->fetchColumn();
    echo "<script>console.log('¿Tabla resultados_tests existe?: " . ($check > 0 ? "SÍ" : "NO") . "');</script>";

    // CONSULTA DIRECTA (Usando el ID 6 que confirmamos en tus capturas)
    $stmt = $pdo->prepare('SELECT "fecha_realizacion", "puntaje_total" FROM "resultados_tests" WHERE "id_usuario" = 6 ORDER BY "fecha_realizacion" ASC');
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $l = []; $p = [];
    foreach ($res as $row) {
        $l[] = date("d/m", strtotime($row['fecha_realizacion']));
        $p[] = (int)$row['puntaje_total'];
    }
    
    $js_labels = json_encode($l);
    $js_data = json_encode($p);

    echo "<script>console.log('Datos recuperados: " . count($res) . "');</script>";

} catch (Exception $e) {
    echo "<div style='color:red;'>Error Crítico: " . $e->getMessage() . "</div>";
    $js_labels = "[]"; $js_data = "[]";
}
?>

<div style="height: 350px; width: 100%; background: #1a1a1a; border-radius: 10px; padding: 10px;">
    <canvas id="graficaFinal"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const labels = <?php echo $js_labels; ?>;
    const data = <?php echo $js_data; ?>;

    if (labels.length === 0) {
        console.error("No hay datos para graficar. Revisa la consola arriba.");
        return;
    }

    new Chart(document.getElementById('graficaFinal'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nivel de Estrés',
                data: data,
                borderColor: '#a78bfa',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(167, 139, 250, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: '#334155' } },
                x: { grid: { color: '#334155' } }
            }
        }
    });
});
</script>