<main>
    <div class="page-header mb-4">
        <h1>🤝 Formulário de Negociação</h1>
        <p class="text-muted">Processe a venda de títulos desta intermediação</p>
    </div>

    <div class="grid grid-2">
        <!-- Coluna 1: Dados da Negociação (Apenas Leitura) -->
        <div class="card">
            <div class="card-header">
                <h2>📋 Dados da Intermediação</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Conta Vendedor</label>
                    <div class="form-control-static">
                        <strong><?= htmlspecialchars($negociacao['conta'] ?? '---') ?></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nome do Cliente</label>
                    <div class="form-control-static">
                        <?= htmlspecialchars($negociacao['cliente'] ?? '---') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Produto (Ativo)</label>
                    <div class="form-control-static">
                        <span class="badge badge-info">
                            <?= htmlspecialchars($negociacao['produto'] ?? '---') ?>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Estratégia (Tipo Operação)</label>
                    <div class="form-control-static">
                        <?= htmlspecialchars($negociacao['estrategia'] ?? '---') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Emissor (CNPJ)</label>
                    <div class="form-control-static">
                        <?= htmlspecialchars($negociacao['emissor'] ?? '---') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vencimento</label>
                    <div class="form-control-static">
                        <strong><?= htmlspecialchars($negociacao['vencimento'] ?? '---') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna 2: Informações de Quantidade e Valores -->
        <div class="card">
            <div class="card-header">
                <h2>💰 Valores e Quantidades</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Quantidade Disponível</label>
                    <div class="form-control-static">
                        <strong class="text-success"><?= htmlspecialchars($negociacao['quantidade_disponivel'] ?? '0') ?></strong>
                        <small class="text-muted"> títulos</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Taxa (%)</label>
                    <div class="form-control-static">
                        <?= htmlspecialchars($negociacao['taxa'] ?? '0,00%') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Valor Bruto Total</label>
                    <div class="form-control-static">
                        <strong><?= htmlspecialchars($negociacao['valor_bruto'] ?? 'R$ 0,00') ?></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>IR (Imposto de Renda)</label>
                    <div class="form-control-static">
                        <?= htmlspecialchars($negociacao['ir'] ?? 'R$ 0,00') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Valor Líquido Total</label>
                    <div class="form-control-static">
                        <strong class="text-primary"><?= htmlspecialchars($negociacao['valor_liquido'] ?? 'R$ 0,00') ?></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Data da Compra</label>
                    <div class="form-control-static">
                        <?= htmlspecialchars($negociacao['data_compra'] ?? '---') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de Venda -->
    <div class="card mt-4">
        <div class="card-header">
            <h2>✅ Processar Venda de Títulos</h2>
        </div>
        <form method="POST" action="index.php?controller=negociacao&action=processar" class="card-body">
            <input type="hidden" name="negociacao_id" value="<?= (int)$negociacao['id'] ?>">

            <div class="grid grid-2">
                <div class="form-group">
                    <label for="quantidade_vendida" class="form-label">
                        Quantidade a Vender
                        <small class="text-danger">*</small>
                    </label>
                    <input 
                        type="number" 
                        id="quantidade_vendida" 
                        name="quantidade_vendida" 
                        min="1" 
                        max="<?= (int)$negociacao['quantidade_disponivel'] ?>" 
                        required 
                        placeholder="Ex: 5"
                        class="form-control"
                        onchange="atualizarPreview()">
                    <small class="text-muted">
                        Máximo: <strong><?= (int)$negociacao['quantidade_disponivel'] ?></strong> títulos
                    </small>
                </div>

                <div class="form-group">
                    <label for="quantidade_remanescente" class="form-label">Quantidade Remanescente</label>
                    <input 
                        type="text" 
                        id="quantidade_remanescente" 
                        disabled 
                        placeholder="Será atualizado"
                        class="form-control" 
                        style="background-color: #f5f5f5;">
                    <small class="text-muted">Calculado automaticamente</small>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h3>⚠️ Restrições de Quantidade</h3>
                <ul>
                    <li><strong>Mínimo:</strong> 1 título</li>
                    <li><strong>Máximo:</strong> <?= (int)$negociacao['quantidade_disponivel'] ?> títulos</li>
                    <li><strong>Observação:</strong> Após a venda, a quantidade disponível será reduzida automaticamente</li>
                </ul>
            </div>

            <div class="card-footer flex flex-between mt-4" style="border-top: 1px solid var(--border-light); padding-top: 20px;">
                <a href="index.php?controller=negociacao&action=painel" class="btn btn-outline">
                    ← Cancelar
                </a>
                <button type="submit" class="btn btn-success btn-large">
                    ✓ Confirmar Venda
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    const quantidadeDisponivel = <?= (int)$negociacao['quantidade_disponivel'] ?>;

    function atualizarPreview() {
        const input = document.getElementById('quantidade_vendida');
        const remanescente = document.getElementById('quantidade_remanescente');
        
        const quantidade_vendida = parseInt(input.value) || 0;
        const quantidade_nova = quantidadeDisponivel - quantidade_vendida;

        if (quantidade_vendida > quantidadeDisponivel) {
            input.classList.add('error');
            remanescente.value = 'Quantidade inválida!';
            remanescente.classList.add('error-bg');
        } else if (quantidade_vendida > 0) {
            input.classList.remove('error');
            remanescente.value = quantidade_nova;
            remanescente.classList.remove('error-bg');
        } else {
            input.classList.remove('error');
            remanescente.value = '';
            remanescente.classList.remove('error-bg');
        }
    }

    // Valida ao carregar a página
    document.addEventListener('DOMContentLoaded', atualizarPreview);
</script>

<style>
    .form-control-static {
        padding: 8px 12px;
        background-color: #f9f9f9;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 14px;
        line-height: 1.5;
    }

    .error {
        border-color: #d32f2f !important;
        background-color: #ffebee !important;
    }

    .error-bg {
        background-color: #ffebee !important;
        border-color: #d32f2f !important;
    }

    .gap-2 {
        gap: 12px;
    }

    .btn-large {
        padding: 12px 24px;
        font-size: 16px;
    }
</style>
