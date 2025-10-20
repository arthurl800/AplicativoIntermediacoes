<main>
    <h2>Resultado da Importação</h2>

    <?php if (isset($result) && $result['success']): ?>
        <p style="color: green; font-weight: bold;"><?= $result['message'] ?></p>
        <?php if (!empty($result['errors'])): ?>
            <p style="color: orange;">Atenção: <?= count($result['errors']) ?> linhas falharam (provavelmente registros duplicados).</p>
        <?php endif; ?>
    <?php elseif (isset($result)): ?>
        <p style="color: red; font-weight: bold;"><?= $result['message'] ?></p>
    <?php endif; ?>

    <p><a href="index.php?controller=upload&action=index">Voltar ao Formulário de Upload</a></p>
</main>