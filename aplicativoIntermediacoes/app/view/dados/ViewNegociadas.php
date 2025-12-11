<?php
// app/dados/ViewNegociadas.php
?>
<div class="auditoria-container">
    <h2 class="form-section-title">
        Intermediações Negociadas
    </h2>

    <p class="mb-4">
        
    <br>Esta tabela exibe os registros de títulos <strong>após alguma negociação efetivada</strong>.</br>
        
    <br>Apenas títulos com quantidade > 0 são exibidos nesta tabela.</br>
    
    </p>

    <?php if (empty($data)): ?>
        <div class="message warning">
            <p>
                <br>Nenhum registro de intermediações negociadas encontrado.</br>
            </p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Conta</th>
                            <th>Nome/Cliente</th>
                            <th>Ativo (Código)</th>
                            <th>Produto</th>
                            <th>Vencimento</th>
                            <th style="text-align: right;">Quantidade</th>
                            <th style="text-align: right;">Vl. Bruto (R$)</th>
                            <th style="text-align: right;">Vl. Líquido (R$)</th>
                            <th>Taxa Emissão (%)</th>
                            <th>Data Importação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td data-label="Conta">
                                    <?= htmlspecialchars($row['Conta'] ?? '') ?>
                                </td>
                                <td data-label="Nome/Cliente">
                                    <?= htmlspecialchars($row['Nome'] ?? '') ?>
                                </td>
                                <td data-label="Ativo (Código)">
                                    <?= htmlspecialchars($row['Ativo'] ?? 'N/A') ?>
                                </td>
                                <td data-label="Produto">
                                    <?= htmlspecialchars($row['Produto'] ?? 'N/A') ?>
                                </td>
                                <td data-label="Vencimento" style="text-align: center;">
                                    <?php 
                                        $venc = $row['Vencimento'] ?? '';
                                        if (!empty($venc) && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $venc, $m)) {
                                            echo sprintf('%02s/%02s/%s', $m[3], $m[2], $m[1]);
                                        } else {
                                            echo $venc;
                                        }
                                    ?>
                                </td>
                                <td data-label="Quantidade" style="text-align: right; font-weight: 500;">
                                    <?= number_format($row['Quantidade'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td data-label="Vl. Bruto" style="text-align: right;">
                                    R$ <?= number_format(($row['Valor_Bruto'] ?? 0) / 100, 2, ',', '.') ?>
                                </td>
                                <td data-label="Vl. Líquido" style="text-align: right; color: var(--success-color); font-weight: 600;">
                                    R$ <?= number_format(($row['Valor_Liquido'] ?? 0) / 100, 2, ',', '.') ?> 
                                </td>
                                <td data-label="Taxa Emissão">
                                    <?= number_format(($row['Taxa_Emissao'] ?? 0) / 100, 4, ',', '.') ?> %
                                </td>
                                <td data-label="Data Importação" style="font-size: 0.9em; color: #666;">
                                    <?php 
                                        $dataImport = $row['Data_Importacao'] ?? '';
                                        if (!empty($dataImport)) {
                                            echo date('d/m/Y H:i', strtotime($dataImport));
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="message info mt-4">
            <p>
                <strong>Total de registros exibidos:</strong> <?= count($data) ?>
            </p>
        </div>
    <?php endif; ?>
</div>
