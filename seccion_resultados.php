<?php
/**
 * SECCIÓN DE RESULTADOS DINÁMICOS - SIBE (Versión Final Corregida)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php'; 

$id_usuario = $_SESSION['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 6;

$nivel_detectado = "Nivel Medio";
$mensaje_final = "Tu perfil de bienestar ha sido actualizado con éxito.";
$accion_final  = "Sigue monitoreando tus hábitos diarios en la plataforma SIBE.";
$puntos_pss = 0;
$puntos_dass = 0;

try {
    $pdo = conectarDB();

    // Cambiamos a LEFT JOIN por si acaso la tabla recomendaciones está vacía en este momento,
    // así la página cargará de todos modos con los datos del test y no se romperá el diseño.
    $sql_res = 'SELECT r."puntaje_total", r."nivel_detectado", rec."mensaje", rec."sugerencia_accion"
                FROM "resultados_tests" r
                LEFT JOIN "recomendaciones" rec ON r."id_recomendacion" = rec."id_recomendacion"
                WHERE r."id_usuario" = :id_user AND r."tipo_test" = \'pss10\'
                ORDER BY r."fecha_realizacion" DESC 
                LIMIT 1';
                
    $stmt = $pdo->prepare($sql_res);
    $stmt->execute([':id_user' => $id_usuario]);
    $data_pss = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data_pss) {
        $puntos_pss      = (int)$data_pss['puntaje_total'];
        $nivel_detectado = $data_pss['nivel_detectado'] ?? $nivel_detectado;
        $mensaje_final   = $data_pss['mensaje'] ?? "Puntaje PSS-10 obtenido: " . $puntos_pss . " puntos.";
        $accion_final    = $data_pss['sugerencia_accion'] ?? "Continúa evaluando tus métricas en el panel.";
    }

    // Recuperar último DASS-21
    $sql_dass = 'SELECT "puntaje_total" 
                 FROM "resultados_tests" 
                 WHERE "id_usuario" = :id_user AND "tipo_test" = \'dass21\'
                 ORDER BY "fecha_realizacion" DESC 
                 LIMIT 1';
    $stmt_dass = $pdo->prepare($sql_dass);
    $stmt_dass->execute([':id_user' => $id_usuario]);
    $data_dass = $stmt_dass->fetch(PDO::FETCH_ASSOC);
    if ($data_dass) {
        $puntos_dass = (int)$data_dass['puntaje_total'];
    }

    $pct_estres = min(100, round(($puntos_pss / 40) * 100));
    $pct_dass = min(100, round(($puntos_dass / 63) * 100));

} catch (Exception $e) {
    error_log("Error en resultados SIBE: " . $e->getMessage());
}
?>

<style>
    .resultados-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        max-width: 1100px;
        margin: 0 auto;
        padding: 10px;
        font-family: 'Inter', sans-serif;
        animation: slideUp 0.6s ease-out;
    }

    @media (min-width: 768px) {
        .resultados-container {
            grid-template-columns: 1fr 1.2fr;
            align-items: start;
        }
    }

    .card-sibe {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border: 1px solid #e2e8f0;
    }

    .panel-exito {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 380px;
    }

    .panel-plan {
        background: #ffffff;
    }

    .header-plan {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .badge-alerta {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #b45309;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .box-diagnostico {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #f1f5f9;
        margin-bottom: 20px;
    }

    .box-diagnostico h4 {
        margin: 0 0 6px 0;
        font-size: 1.05rem;
        color: #1e293b;
    }

    .box-diagnostico p {
        color: #475569;
        font-size: 0.9rem;
        line-height: 1.45;
        margin: 0 0 10px 0;
    }

    .titulo-metricas {
        font-size: 0.8rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .fila-progreso {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        font-size: 0.9rem;
        color: #334155;
    }

    .label-progreso { width: 100px; font-weight: 500; }
    
    .contenedor-barra {
        background: #f1f5f9;
        height: 8px;
        border-radius: 6px;
        flex-grow: 1;
        margin: 0 12px;
        overflow: hidden;
    }

    .relleno-barra { height: 100%; border-radius: 6px; }
    .bg-critico { background: #ef4444; }
    .bg-advertencia { background: #f59e0b; }
    .bg-saludable { background: #10b981; }

    .texto-porcentaje { width: 35px; text-align: right; color: #64748b; font-weight: 600; }

    .btn-inicio {
        display: inline-block;
        background: #2563eb;
        color: white;
        text-decoration: none;
        padding: 12px 30px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: background 0.2s ease;
    }

    .btn-inicio:hover { background: #1d4ed8; }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="resultados-container">
    
    <div class="card-sibe panel-exito">
        <div style="font-size: 3.5rem; margin-bottom: 15px;">🎉</div>
        <h2 style="color: #0f172a; font-size: 1.75rem; font-weight: 800; margin-bottom: 10px;">
            ¡Análisis Completado!
        </h2>
        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5; margin-bottom: 25px; max-width: 340px;">
            Tus respuestas han sido procesadas con éxito en el sistema inteligente.
        </p>
        
        <a href="index.php?seccion=inicio" class="btn-inicio">← Volver al inicio</a>
    </div>

    <div class="card-sibe panel-plan">
        <div class="header-plan">💡 PLAN DE BIENESTAR SUGERIDO</div>
        
        <div class="badge-alerta">
            Status: <?= htmlspecialchars($nivel_detectado) ?>
        </div>

        <div class="box-diagnostico">
            <h4>📋 Diagnóstico de Estrés Académico</h4>
            <p><strong><?= htmlspecialchars($mensaje_final) ?></strong></p>
            <div style="border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 8px; font-size: 0.85rem; color: #475569;">
                <span style="color: #2563eb; font-weight: 700;">Recomendación inmediata:</span><br>
                <?= htmlspecialchars($accion_final) ?>
            </div>
        </div>

        <div class="titulo-metricas">Indicadores Obtenidos</div>
        
        <div class="fila-progreso">
            <span class="label-progreso">Estrés (PSS)</span>
            <div class="contenedor-barra">
                <div class="relleno-barra <?= ($pct_estres > 60) ? 'bg-critico' : 'bg-advertencia' ?>" style="width: <?= $pct_estres ?>%;"></div>
            </div>
            <span class="texto-porcentaje"><?= $pct_estres ?>%</span>
        </div>

        <div class="fila-progreso">
            <span class="label-progreso">Ánimo (DASS)</span>
            <div class="contenedor-barra">
                <div class="relleno-barra <?= ($pct_dass > 50) ? 'bg-advertencia' : 'bg-saludable' ?>" style="width: <?= $pct_dass ?>%;"></div>
            </div>
            <span class="texto-porcentaje"><?= $pct_dass ?>%</span>
        </div>
    </div>

</div>