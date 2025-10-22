<main>

<div style="border: 1px solid #007bff; padding: 15px; margin-top: 20px; background-color: #ffffa5;">
    <h2>Importar Dados</h2>
    
    <form action="index.php?controller=upload&action=processUpload" method="POST" enctype="multipart/form-data">
        <p>Selecione o arquivo para importar:</p>
        
        <input type="file" name="arquivo_csv" accept=".csv" required>
        
        <br><br>
        <button type="submit">Enviar e Salvar no Banco</button>
    </form>
</div>

   <?php
    // Exibe preview da última importação se disponível
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    $previewData = $_SESSION['last_import_preview'] ?? null;
    $preview = null;
    $previewHeader = null;
    if (!empty($previewData)) {
        if (is_array($previewData) && array_key_exists('rows', $previewData)) {
            $preview = $previewData['rows'];
            $previewHeader = $previewData['header'] ?? null;
        } else {
            $preview = $previewData; // legacy
        }
    }

    if (!empty($preview)):
    ?>
        <section style="margin-top:20px; padding:12px; border:1px dashed #007bff; background:#f8fbff;">
            <h3 style="margin-top:0;">Preview da Última Importação (head 25)</h3>
            <div style="overflow:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#e9f5ff;">
                            <?php if (!empty($previewHeader)): ?>
                                <?php foreach ($previewHeader as $h): ?>
                                    <th style="padding:6px;border:1px solid #ddd;"><?= htmlspecialchars($h ?? '') ?></th>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php
                                // Cabeçalhos numéricos 1..n
                                $maxCols = max(array_map('count', $preview));
                                for ($i=0;$i<$maxCols;$i++): ?>
                                    <th style="padding:6px;border:1px solid #ddd;">Col <?= $i+1 ?></th>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview as $row): ?>
                            <tr>
                                <?php $cols = !empty($previewHeader) ? count($previewHeader) : max(array_map('count', [$row])); ?>
                                <?php for ($i=0;$i<$cols;$i++): ?>
                                    <?php
                                    $cellVal = $row[$i] ?? '';
                                    // tenta formatar datas no formato YYYY-MM-DD para DD/MM/YYYY
                                    if (is_string($cellVal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $cellVal)) {
                                        $cellVal = date('d/m/Y', strtotime($cellVal));
                                    }
                                    ?>
                                    <td style="padding:6px;border:1px solid #eee;"><?= htmlspecialchars($cellVal ?? '') ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:10px;">
                <a href="index.php?controller=dados&action=visualizar&clear_preview=1" style="text-decoration:none;color:#333;background:#fff;border:1px solid #007bff;padding:6px 10px;border-radius:4px;">Fechar Preview</a>
            </div>
        </section>
    <?php
    // Limpa preview se o usuário passou ?clear_preview=1
    if (!empty($_GET['clear_preview'])) {
        unset($_SESSION['last_import_preview']);
    }
    endif;
    ?>

</main>