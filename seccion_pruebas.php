<?php
$json_path = 'instrumentos.json';
$datos = json_decode(file_get_contents($json_path), true);
$pss10 = null; $dass21 = null;

foreach ($datos['tests'] as $t) {
    if ($t['id'] == 'pss10') $pss10 = $t;
    if ($t['id'] == 'dass21') $dass21 = $t;
}
?>

<style>
    .evaluacion-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        font-family: 'Inter', sans-serif;
    }

    .form-card {
        background: white;
        width: 100%;
        max-width: 800px;
        padding: 60px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        text-align: center;
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

    .custom-option:hover {
        background: #e2e8f0;
    }

    input[type="radio"]:checked + .custom-option {
        background: #fff;
        border-color: #3b82f6;
        color: #1e293b;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    input[type="radio"] { display: none; }

    .btn-next {
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
    }

    .btn-next:hover { background: #2563eb; transform: translateY(-2px); }

    .test-indicator {
        color: #fbbf24;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 10px;
        display: block;
    }
</style>

<div class="evaluacion-wrapper">
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