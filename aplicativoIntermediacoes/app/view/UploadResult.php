<?php
// app/view/upload/UploadResult.php
?>

<main>
    <h2>Resultado da Importação</h2>

    <?php if (isset($result) && $result['success']): ?>
        <p class="message success"><?= $result['message'] ?></p>
        <?php if (!empty($result['errors'])): ?>
            <p class="message warning">Atenção: <?= count($result['errors']) ?> linhas falharam (provavelmente registros duplicados).</p>
        <?php endif; ?>
    <?php elseif (isset($result)): ?>
        <p class="message error"><?= $result['message'] ?></p>
    <?php endif; ?>

    <p class="mt-4"><a href="index.php?controller=upload&action=index" class="btn btn-secondary">Voltar ao Formulário de Upload</a></p>
</main>