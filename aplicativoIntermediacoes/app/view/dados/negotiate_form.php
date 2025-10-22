<main>
    <h2>Formulário de Negociação</h2>

    <form method="POST" action="index.php?controller=dados&action=process_negotiation" style="background:#fff;padding:12px;border-radius:6px;border:1px solid #e9ecef;">
    <input type="hidden" name="conta" value="<?= htmlspecialchars($data['conta'] ?? '') ?>">
    <input type="hidden" name="cliente" value="<?= htmlspecialchars($data['cliente'] ?? '') ?>">
    <input type="hidden" name="tipo" value="<?= htmlspecialchars($data['tipo'] ?? '') ?>">
    <input type="hidden" name="quantidade" value="<?= htmlspecialchars($data['quantidade'] ?? '') ?>">
    <input type="hidden" name="valor_bruto_importado" value="<?= htmlspecialchars($data['valor_bruto'] ?? '') ?>">

        <p><strong>Conta:</strong> <?= htmlspecialchars($data['conta'] ?? '') ?> — <strong>Cliente:</strong> <?= htmlspecialchars($data['cliente'] ?? '') ?></p>
        <p><strong>Tipo:</strong> <?= htmlspecialchars($data['tipo'] ?? '') ?> — <strong>Quantidade disponível:</strong> <?= number_format($data['quantidade'] ?? 0, 0, ',', '.') ?></p>

        <label style="display:block; margin-top:8px;">Quantidade a negociar (<= disponível):<br>
            <input type="number" name="quantidade_negociada" min="1" max="<?= htmlspecialchars($data['quantidade'] ?? 0) ?>" value="<?= htmlspecialchars($data['quantidade'] ?? 0) ?>" style="width:200px;padding:8px;border:1px solid #ccc;border-radius:4px;">
        </label>

        <hr>
        <h4>Valores de Saída (vendedor)</h4>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <label style="flex:1;">Taxa de Saída (%):<br>
                <input type="text" name="taxa_saida" value="" placeholder="Ex: 1.25" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </label>
            <label style="flex:1;">Valor Bruto de Saída (R$):<br>
                <input type="text" name="valor_bruto_saida" value="" placeholder="Calcule ou preencha" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </label>
        </div>

        <hr>
        <h4>Valores de Entrada (comprador)</h4>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <label style="flex:1;">Taxa de Entrada (%):<br>
                <input type="text" name="taxa_entrada" value="" placeholder="Ex: 1.00" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </label>
            <label style="flex:1;">Valor Bruto de Entrada (R$):<br>
                <input type="text" name="valor_bruto_entrada" value="" placeholder="Calcule ou preencha" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </label>
        </div>

        <div style="margin-top:12px; display:flex; gap:10px;">
            <button type="submit" style="background:#28a745;color:white;padding:8px 12px;border:none;border-radius:4px;font-weight:bold;">Processar Negociação</button>
            <a href="index.php?controller=dados&action=visualizar" style="background:#6c757d;color:white;padding:8px 12px;border-radius:4px;text-decoration:none;">Cancelar</a>
        </div>
    </form>
</main>
