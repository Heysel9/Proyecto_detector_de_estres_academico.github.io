<?php
$json_path = 'instrumentos.json';
$datos = json_decode(file_get_contents($json_path), true);
$pss10 = null; $dass21 = null;

foreach ($datos['tests'] as $t) {
    if ($t['id'] == 'pss10') $pss10 = $t;
    if ($t['id'] == 'dass21') $dass21 = $t;
}

// CONTROL DE VISTAS: Cambia a true tras procesar el POST de guardar_test.php
$mostrar_resultados = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['simular_exito'])) {
    $porcentaje_sueno = 30;
    $porcentaje_actividad = 45;
    $porcentaje_alimentacion = 92;
    $porcentaje_lectura = 30;
    $porcentaje_celular = 65;

    $mostrar_resultados = true; 
}
?>

<style>

 /* 1. Al principio de tu <style>, añade esto */
:root {
    color-scheme: light !important;
}

/* 2. Modifica la tarjeta para que sea inmune al modo oscuro */
.form-card {
    background-color: #ffffff !important;
    color-scheme: light !important; /* Fuerza a los hijos a ser 'light' */
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

/* 3. La pregunta (Negro total, como en tu foto ideal) */
.pregunta-texto {
    font-size: 2rem;
    font-weight: 800;
    color: #000000 !important; 
    -webkit-text-fill-color: #000000 !important; /* Obligatorio para Chrome en modo oscuro */
    margin-bottom: 40px;
}

/* 4. El indicador naranja (Más fuerte para que resalte) */
.test-indicator {
    /* Un color naranja amarillento más fuerte para que resalte en blanco */
    color: #f59e0b !important; 
    
    text-transform: uppercase;
    font-weight: 800; /* Más grueso para que se vea mejor */
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: block;
    
    /* TRUCO PARA MODO OSCURO: Esto evita que el navegador lo opaque */
    -webkit-text-fill-color: #f59e0b !important;
    filter: none !important;
}

/* 5. Las opciones (Para que no se vean blancas sobre blanco) */
.custom-option {
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-weight: 600;
}
    .evaluacion-wrapper {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
        max-width: 900px;
        margin: 0 auto;
        min-height: 80vh;
        align-items: center;
        font-family: 'Inter', sans-serif;
        padding: 20px;
        transition: all 0.4s ease;
    }

    .evaluacion-wrapper.con-resultados {
        grid-template-columns: 1fr 1fr;
        max-width: 1300px;
    }

    .form-card {
        background: white;
        width: 100%;
        padding: 60px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        text-align: center;
        box-sizing: border-box;
    }

    .form-card.exito-card {
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 400px;
    }

    .pregunta-texto {
        font-size: 2rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 40px;
    }

    .option-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 40px;
    }

    .custom-option {
        background: #f1f5f9;
        padding: 18px 25px;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
        color: #475569;
        font-weight: 500;
    }

    .custom-option:hover { background: #e2e8f0; }

    input[type="radio"]:checked + .custom-option {
        background: #ffffff;
        border-color: #3b82f6;
        color: #1e293b;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    input[type="radio"] { display: none; }

    .btn-next, .btn-inicio {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 15px 45px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: 0.3s;
        text-decoration: none;
    }

    .btn-next:hover { background: #2563eb; transform: translateY(-2px); }
    
    .btn-inicio {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        margin-top: 25px;
    }
    .btn-inicio:hover { background: #e2e8f0; color: #1e293b; }

    .test-indicator {
        color: #fbbf24;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 10px;
        display: block;
    }

    .plan-card {
        background: #f6fbf9; 
        border: 1px solid #e2f2ec;
        text-align: left;
        padding: 40px;
    }

    .plan-header {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-banner {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        color: #92400e;
        padding: 15px;
        border-radius: 8px;
        font-size: 0.95rem;
        margin-bottom: 25px;
        line-height: 1.4;
    }

    .habit-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }
    .tab {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: white;
        border: 1px solid #e2e8f0;
        color: #64748b;
        cursor: pointer;
    }
    .tab.active {
        background: #1e3a8a;
        color: white;
        border-color: #1e3a8a;
    }

    .recommendation-box {
        background: white;
        border-radius: 12px;
        padding: 25px;
        border: 1px solid #e2e8f0;
        margin-bottom: 30px;
    }
    .recommendation-box h4 {
        margin: 0 0 10px 0;
        font-size: 1.2rem;
        color: #1e293b;
    }
    .recommendation-box p {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 15px;
    }
    .recommendation-box ul {
        margin: 0;
        padding-left: 20px;
        color: #334155;
        font-size: 0.9rem;
    }
    .recommendation-box li { margin-bottom: 10px; line-height: 1.4; }

    .scores-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
    }
    .score-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 0.95rem;
        color: #334155;
        font-weight: 500;
    }
    .score-label { width: 100px; }
    .bar-container {
        background: #e2e8f0;
        height: 10px;
        border-radius: 5px;
        flex-grow: 1;
        margin: 0 15px;
        overflow: hidden;
    }
    .bar-fill { height: 100%; border-radius: 5px; transition: width 0.5s ease; }
    .bg-red { background: #ef4444; }
    .bg-orange { background: #f97316; }
    .bg-green { background: #10b981; }
    .score-pct { width: 40px; text-align: right; color: #64748b; }
</style>

<div class="evaluacion-wrapper <?= $mostrar_resultados ? 'con-resultados' : '' ?>">
    
    <?php if (!$mostrar_resultados): ?>
        <form id="form-sibe" action="guardar_test.php" method="POST" class="form-card">
            
            <?php foreach ($pss10['preguntas'] as $i => $pregunta): ?>
                <div class="step-container" id="pss-<?= $i ?>" style="<?= ($i === 0) ? '' : 'display:none' ?>">
                    <span class="test-indicator">PSS-10: Pregunta <?= ($i + 1) ?> / 10</span>
                    <h2 class="pregunta-texto"><?= $pregunta ?></h2>
                    
                    <div class="option-container">
                        <?php foreach ($pss10['escala'] as $opcion): ?>
                            <label>
                                <input type="radio" name="pss_q<?= $i ?>" value="<?= $opcion['valor'] ?>" required>
                                <div class="custom-option">
                                    <span style="margin-right: 15px;">○</span> <?= $opcion['etiqueta'] ?>
                                </div>
                            </label>
                            
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn-next" onclick="nextStep('pss', <?= $i ?>)">Next Question →</button>
                </div>
            <?php endforeach; ?>

            <?php foreach ($dass21['preguntas'] as $i => $pregunta): ?>
                <div class="step-container" id="dass-<?= $i ?>" style="display:none">
                    <span class="test-indicator" style="color: #10b981;">DASS-21: Pregunta <?= ($i + 1) ?> / 21</span>
                    <h2 class="pregunta-texto"><?= $pregunta ?></h2>
                    
                    <div class="option-container">
                        <?php foreach ($dass21['escala'] as $opcion): ?>
                            <label>
                                <input type="radio" name="dass_q<?= $i ?>" value="<?= $opcion['valor'] ?>" required>
                                <div class="custom-option">
                                    <span style="margin-right: 15px;">○</span> <?= $opcion['etiqueta'] ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($i < 20): ?>
                        <button type="button" class="btn-next" onclick="nextStep('dass', <?= $i ?>)">Next Question →</button>
                    <?php else: ?>
                        <button type="submit" class="btn-next" style="background: #10b981;">Finalizar Todo ✨</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </form>
    <?php else: ?>
        <div class="form-card exito-card">
            <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
            <h2 style="font-size: 1.8rem; color: #1e293b; margin-bottom: 10px;">¡Análisis Completado!</h2>
            <p style="color: #64748b; font-size: 1rem; max-width: 300px;">Tus respuestas han sido procesadas con éxito en el sistema.</p>
            
            <a href="seccion_pruebas.php" class="btn-inicio">← Volver al inicio</a>
        </div>
    <?php endif; ?>

    <?php if ($mostrar_resultados): ?>
        <div class="form-card plan-card">
            <div class="plan-header">💡 PLAN DE BIENESTAR SUGERIDO</div>
            
            <div class="alert-banner">
                <strong>⚡ NIVEL MEDIO:</strong> Buen punto de partida. Hay hábitos importantes que mejorar.
            </div>

            <div class="habit-tabs">
                <span class="tab active">🌙 Sueño</span>
                <span class="tab">🏃 Actividad</span>
                <span class="tab">🥗 Alimentación</span>
                <span class="tab">📚 Lectura</span>
                <span class="tab">📱 Celular</span>
            </div>

            <div class="recommendation-box">
                <h4>😴 Déficit de sueño</h4>
                <p>Dormir menos de 5 horas afecta seriamente tu memoria y concentración.</p>
                <ul>
                    <li><strong>Prioriza el sueño:</strong> tu rendimiento académico cae hasta un 40% con privación crónica.</li>
                    <li>Establece una "hora de apagar" para el celular y crear un ritual de relajación.</li>
                    <li>Considera apps de meditación guiada (Calm, Headspace) para facilitar el sueño.</li>
                </ul>
            </div>

            <div class="scores-section">
                <div class="scores-title">PUNTUACIÓN POR HÁBITO</div>
                
                <div class="score-row">
                    <span class="score-label">Sueño</span>
                    <div class="bar-container">
                        <div class="bar-fill bg-red" style="width: <?= $porcentaje_sueno ?>%;"></div>
                    </div>
                    <span class="score-pct"><?= $porcentaje_sueno ?>%</span>
                </div>

                <div class="score-row">
                    <span class="score-label">Actividad</span>
                    <div class="bar-container">
                        <div class="bar-fill bg-orange" style="width: <?= $porcentaje_actividad ?>%;"></div>
                    </div>
                    <span class="score-pct"><?= $porcentaje_actividad ?>%</span>
                </div>

                <div class="score-row">
                    <span class="score-label">Alimentación</span>
                    <div class="bar-container">
                        <div class="bar-fill bg-green" style="width: <?= $porcentaje_alimentacion ?>%;"></div>
                    </div>
                    <span class="score-pct"><?= $porcentaje_alimentacion ?>%</span>
                </div>

                <div class="score-row">
                    <span class="score-label">Lectura</span>
                    <div class="bar-container">
                        <div class="bar-fill bg-red" style="width: <?= $porcentaje_lectura ?>%;"></div>
                    </div>
                    <span class="score-pct"><?= $porcentaje_lectura ?>%</span>
                </div>

                <div class="score-row">
                    <span class="score-label">Celular</span>
                    <div class="bar-container">
                        <div class="bar-fill bg-orange" style="width: <?= $porcentaje_celular ?>%;"></div>
                    </div>
                    <span class="score-pct"><?= $porcentaje_celular ?>%</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
function nextStep(test, actual) {
    const radios = document.getElementsByName(test + '_q' + actual);
    let checked = false;
    for (let r of radios) if (r.checked) checked = true;

    if (!checked) {
        alert("Por favor selecciona una opción");
        return;
    }

    document.getElementById(test + '-' + actual).style.display = 'none';

    if (test === 'pss' && actual === 9) {
        document.getElementById('dass-0').style.display = 'block';
    } else {
        document.getElementById(test + '-' + (actual + 1)).style.display = 'block';
    }
}
</script>