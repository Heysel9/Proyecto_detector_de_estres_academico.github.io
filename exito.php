<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Incluimos tu archivo de conexión real
require_once 'conexion.php'; 

// Valores base para la vista
$porcentaje_pss = 0;
$porcentaje_dass = 0;
$nivel_estres = "No calculado";
$diagnostico_texto = "Cargando resultados...";
$recomendacion_inmediata = "Procesando...";

try {
    // 2. Obtener la conexión PDO
    $conexion = conectarDB();
    
    // ID de usuario de pruebas
    $id_usuario = 6; 
    
    // 3. CONSULTA PSS-10: Traemos el último puntaje guardado ordenando por id_resultado
    $query_pss = "SELECT puntaje_total 
                  FROM resultados_tests 
                  WHERE id_usuario = :id_usuario 
                    AND (UPPER(tipo_test) = 'PSS10' OR UPPER(tipo_test) = 'PSS-10')
                  ORDER BY id_resultado DESC LIMIT 1"; 
                  
    $stmt = $conexion->prepare($query_pss);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $resultado_pss = $stmt->fetch(); 

    if ($resultado_pss) {
        $puntaje_real_pss = $resultado_pss['puntaje_total'];
        
        // El PSS-10 evalúa sobre un máximo de 40 puntos. Convertimos el puntaje real a porcentaje.
        $porcentaje_pss = round(($puntaje_real_pss / 40) * 100);
        if ($porcentaje_pss > 100) $porcentaje_pss = 100;
        
        $porcentaje_int = (int)$porcentaje_pss;

        // OMITIMOS el id_recomendacion corrupto de la tabla y buscamos directamente por el porcentaje matemático real
        $query_rec = "SELECT categoria, mensaje, sugerencia_accion 
                      FROM recomendaciones 
                      WHERE rango_min <= :porcentaje1 AND rango_max >= :porcentaje2 
                      LIMIT 1";
                      
        $stmt_rec = $conexion->prepare($query_rec);
        $stmt_rec->execute([
            'porcentaje1' => $porcentaje_int,
            'porcentaje2' => $porcentaje_int
        ]);
        $recomendacion = $stmt_rec->fetch();
        
        if ($recomendacion) {
            $nivel_estres = "Estrés " . $recomendacion['categoria'];
            $diagnostico_texto = $recomendacion['mensaje'];
            $recomendacion_inmediata = $recomendacion['sugerencia_accion'];
        }
    }

    // 4. CONSULTA DASS-21: Traemos el último ordenando por id_resultado
    $query_dass = "SELECT puntaje_total 
                   FROM resultados_tests 
                   WHERE id_usuario = :id_usuario 
                     AND (UPPER(tipo_test) = 'DASS21' OR UPPER(tipo_test) = 'DASS-21')
                   ORDER BY id_resultado DESC LIMIT 1";
                   
    $stmt_dass = $conexion->prepare($query_dass);
    $stmt_dass->execute(['id_usuario' => $id_usuario]);
    $resultado_dass = $stmt_dass->fetch();
    
    if ($resultado_dass) {
        // Asumiendo escala base sobre 42 puntos para las 3 subescalas o 21 según tu diseño visual
        $porcentaje_dass = round(($resultado_dass['puntaje_total'] / 21) * 100);
        if ($porcentaje_dass > 100) $porcentaje_dass = 100;
    } else {
        $porcentaje_dass = 0; 
    }

} catch (Exception $e) {
    $nivel_estres = "Error Técnico de BD";
    $diagnostico_texto = "Detalle: " . $e->getMessage();
    $recomendacion_inmediata = "Revisa los datos en la tabla resultados_tests.";
    $porcentaje_pss = 0;
    $porcentaje_dass = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Análisis Completado - SIBE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0;
            background: linear-gradient(135deg, #e0f2fe 0%, #f0fdf4 100%);
            font-family: 'Inter', sans-serif;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }

        .evaluacion-wrapper {
            display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px;
            max-width: 1200px; width: 95%; margin: 0 auto; padding: 20px; box-sizing: border-box;
        }

        @media (max-width: 900px) {
            .evaluacion-wrapper { grid-template-columns: 1fr; }
        }

        .form-card {
            background: white; width: 100%; padding: 50px 40px; border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); text-align: center; box-sizing: border-box;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
        }

        .exito-card { min-height: 450px; }
        .icon { font-size: 4rem; margin-bottom: 20px; display: block; }
        h2 { font-size: 2rem; color: #1e293b; margin: 0 0 15px 0; font-weight: 800; }
        p { color: #64748b; font-size: 1.05rem; line-height: 1.5; max-width: 340px; margin: 0 0 25px 0; }

        .btn-inicio {
            background: #2563eb; color: white; border: none; padding: 16px 45px;
            border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s ease;
            text-decoration: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-inicio:hover { background: #1d4ed8; transform: translateY(-2px); }

        .plan-card { background: white; text-align: left; padding: 45px; align-items: flex-start; }
        .plan-header { font-size: 1.3rem; font-weight: 800; color: #1e293b; margin-bottom: 25px; display: flex; align-items: center; gap: 8px; }
        
        .status-banner {
            background: #fffbeb; border: 1px solid #fef3c7; color: #d97706;
            padding: 12px 20px; border-radius: 12px; font-size: 1rem; font-weight: 700; margin-bottom: 30px; width: 100%; box-sizing: border-box;
        }

        .diagnostic-box { background: #f8fafc; border-radius: 16px; padding: 25px; border: 1px solid #f1f5f9; margin-bottom: 35px; width: 100%; box-sizing: border-box; }
        .diagnostic-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        .diagnostic-box p { color: #475569; font-size: 1rem; margin: 0 0 15px 0; max-width: 100%; }
        .recom-title { color: #2563eb; font-weight: 700; font-size: 0.95rem; margin-bottom: 4px; }
        .recom-text { color: #64748b; font-size: 0.95rem; margin: 0; }

        .scores-section { width: 100%; }
        .scores-title { font-size: 0.85rem; font-weight: 800; color: #94a3b8; letter-spacing: 0.5px; margin-bottom: 20px; text-transform: uppercase; }
        .score-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; font-size: 1rem; color: #1e293b; font-weight: 600; }
        .score-label { width: 120px; }
        .bar-container { background: #f1f5f9; height: 12px; border-radius: 6px; flex-grow: 1; margin: 0 20px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 6px; transition: width 0.4s ease; }
        .score-pct { width: 40px; text-align: right; color: #64748b; font-weight: 500; }
    </style>
</head>
<body>

<div class="evaluacion-wrapper">
    
    <div class="form-card exito-card">
        <span class="icon">🎉</span>
        <h2>¡Análisis Completado!</h2>
        <p>Tus respuestas han sido procesadas con éxito en el sistema inteligente.</p>
        <a href="index.php" class="btn-inicio">← Volver al inicio seguro</a>
    </div>

    <div class="form-card plan-card">
        <div class="plan-header">💡 PLAN DE BIENESTAR SUGERIDO</div>
        
        <div class="status-banner">
            Status: <?= htmlspecialchars($nivel_estres) ?>
        </div>

        <div class="diagnostic-box">
            <div class="diagnostic-title">📋 Diagnóstico de Estrés Académico</div>
            <p><?= htmlspecialchars($diagnostico_texto) ?></p>
            
            <div class="recom-title">Recomendación inmediata:</div>
            <p class="recom-text"><?= htmlspecialchars($recomendacion_inmediata) ?></p>
        </div>

        <div class="scores-section">
            <div class="scores-title">Indicadores Obtenidos</div>
            
            <div class="score-row">
                <span class="score-label">Estrés (PSS)</span>
                <div class="bar-container">
                    <div class="bar-fill" style="width: <?= $porcentaje_pss ?>%; background: #f97316;"></div>
                </div>
                <span class="score-pct"><?= $porcentaje_pss ?>%</span>
            </div>

            <div class="score-row">
                <span class="score-label">Ánimo (DASS)</span>
                <div class="bar-container">
                    <div class="bar-fill" style="width: <?= $porcentaje_dass ?>%; background: #10b981;"></div>
                </div>
                <span class="score-pct"><?= $porcentaje_dass ?>%</span>
            </div>
        </div>
    </div>

</div>

</body>
</html>