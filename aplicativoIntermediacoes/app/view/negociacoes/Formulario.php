<?php
// app/view/negociacoes/Formulario.php
?>
<main>
    <div class="page-header mb-4">
        <h1>Formulário de Negociação</h1>
        <p class="text-muted">Processe a venda de títulos desta intermediação</p>
    </div>

    <div class="grid grid-2">
        <!-- Coluna 1: Dados da Negociação (Apenas Leitura) -->
        <div class="card">
            <div class="card-header">
                <h2>Dados da Intermediação</h2>
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
                <h2>Valores e Quantidades</h2>
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
            <h2>Processar Venda de Títulos</h2>
        </div>
        <form method="POST" action="index.php?controller=negociacao&action=processar" class="card-body">
            <input type="hidden" name="negociacao_id" value="<?= (int)$negociacao['id'] ?>">
            <input type="hidden" name="ID_Registro_Source" value="<?= htmlspecialchars($negociacao['ID_Registro'] ?? '') ?>">

            <!-- VENDEDOR -->
            <div class="card mb-3" style="background-color: #f5f5f5;">
                <div class="card-header" style="background-color: #e8f5e9; font-weight: bold;">
                    1. Vendedor
                </div>
                <div class="card-body">
                    <div class="grid grid-2">
                        <div>
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
                        </div>
                        <div>
                            <div class="form-group">
                                <label for="taxa_saida">Taxa de Saída (%)</label>
                                <input type="number" step="0.01" id="taxa_saida" name="taxa_saida" class="form-control" value="0" onchange="atualizarPreview()">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <div class="form-group">
                                <label for="valor_bruto_saida">Valor Bruto de Saída (R$)</label>
                                <input type="number" step="0.01" id="valor_bruto_saida" name="valor_bruto_saida" class="form-control" value="" onchange="atualizarPreview()">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for="valor_liquido_saida">Valor Líquido de Saída (R$)</label>
                                <input type="number" step="0.01" id="valor_liquido_saida" name="valor_liquido_saida" class="form-control" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPRADOR -->
            <div class="card mb-3" style="background-color: #f5f5f5;">
                <div class="card-header" style="background-color: #e3f2fd; font-weight: bold;">
                    2. Comprador
                </div>
                <div class="card-body">
                    <div class="grid grid-2">
                        <div>
                            <div class="form-group">
                                <label for="taxa_entrada">Taxa de Entrada (%)</label>
                                <input type="number" step="0.01" id="taxa_entrada" name="taxa_entrada" class="form-control" value="0" onchange="atualizarPreview()">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for="valor_entrada">Valor de Entrada (R$)</label>
                                <input type="number" step="0.01" id="valor_entrada" name="valor_entrada" class="form-control" value="" onchange="atualizarPreview()">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <div class="form-group">
                                <label for="conta_comprador">Conta do Comprador</label>
                                <input type="text" id="conta_comprador" name="conta_comprador" class="form-control" placeholder="Ex: 1001">
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label for="nome_comprador">Nome do Cliente (Comprador)</label>
                                <input type="text" id="nome_comprador" name="nome_comprador" class="form-control" placeholder="Ex: João Silva">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ASSESSOR -->
            <div class="card mb-3" style="background-color: #f5f5f5;">
                <div class="card-header" style="background-color: #fff3e0; font-weight: bold;">
                    3. Assessor
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="valor_plataforma">Valor da Plataforma (R$)</label>
                        <input type="number" step="0.01" id="valor_plataforma" name="valor_plataforma" class="form-control" value="0" onchange="atualizarPreview()">
                    </div>
                </div>
            </div>

            <div class="grid grid-2">

            <div class="card mt-3">
                <div class="card-header"><strong>Preview / Cálculos</strong></div>
                <div class="card-body">
                    <div class="grid grid-2">
                        <div>
                            <div class="form-group">
                                <label>Preço Unitário (Vendedor)</label>
                                <div class="form-control-static" id="preco_unitario_vendedor">R$ 0,00</div>
                            </div>

                            <div class="form-group">
                                <label>Ganho (Vendedor)</label>
                                <div class="form-control-static" id="ganho_vendedor">R$ 0,00</div>
                            </div>

                            <div class="form-group">
                                <label>Rentabilidade (Vendedor)</label>
                                <div class="form-control-static" id="rentabilidade_vendedor">0,00%</div>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label>Preço Unitário (Comprador)</label>
                                <div class="form-control-static" id="preco_unitario_comprador">R$ 0,00</div>
                            </div>

                            <div class="form-group">
                                <label>Corretagem (Assessor)</label>
                                <div class="form-control-static" id="corretagem_assessor">R$ 0,00</div>
                            </div>

                            <div class="form-group">
                                <label>ROA (Assessor)</label>
                                <div class="form-control-static" id="roa_assessor">0,00%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden raw values para envio/uso em cálculos no servidor -->
            <input type="hidden" id="valor_bruto_importado_raw" name="valor_bruto_importado_raw" value="<?= htmlspecialchars($negociacao['valor_bruto_centavos'] ?? '0') ?>">
            <input type="hidden" id="preco_unitario_saida_hidden" name="preco_unitario_saida" value="0">
            <input type="hidden" id="preco_unitario_entrada_hidden" name="preco_unitario_entrada" value="0">
            <input type="hidden" id="ganho_saida_hidden" name="ganho_saida" value="0">
            <input type="hidden" id="rentabilidade_saida_hidden" name="rentabilidade_saida" value="0">
            <input type="hidden" id="corretagem_hidden" name="corretagem_assessor" value="0">
            <input type="hidden" id="roa_hidden" name="roa_assessor" value="0">

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

    // Tenta obter valor bruto importado em formato numérico (em reais)
    function parseImportedRaw(v) {
        const asNum = Number(v);
        if (!isFinite(asNum)) return 0;
        // Se valor aparentemente em centavos (inteiro grande), converte para reais
        if (Number.isInteger(asNum) && Math.abs(asNum) > 1000) {
            return asNum / 100.0;
        }
        return asNum;
    }

    function formatBRL(value) {
        return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function atualizarPreview() {
        const input = document.getElementById('quantidade_vendida');
        const remanescente = document.getElementById('quantidade_remanescente');

        const quantidade_vendida = parseInt(input.value) || 0;
        const quantidade_nova = quantidadeDisponivel - quantidade_vendida;

        // campos de valores
        const taxa_saida = parseFloat(document.getElementById('taxa_saida').value) || 0;
        const valor_bruto_saida = parseFloat(document.getElementById('valor_bruto_saida').value) || 0;
        const taxa_entrada = parseFloat(document.getElementById('taxa_entrada').value) || 0;
        const valor_entrada = parseFloat(document.getElementById('valor_entrada').value) || 0;
        const valor_plataforma = parseFloat(document.getElementById('valor_plataforma').value) || 0;

        const valor_bruto_importado_raw = parseImportedRaw(document.getElementById('valor_bruto_importado_raw').value || 0);

        if (quantidade_vendida > quantidadeDisponivel) {
            input.classList.add('error');
            remanescente.value = 'Quantidade inválida!';
            remanescente.classList.add('error-bg');
            return;
        } else if (quantidade_vendida > 0) {
            input.classList.remove('error');
            remanescente.value = quantidade_nova;
            remanescente.classList.remove('error-bg');
        } else {
            input.classList.remove('error');
            remanescente.value = '';
            remanescente.classList.remove('error-bg');
        }

        // Determina valor bruto de saída (total) - se não informado, usa valor importado proporcional
        let bruto_saida_total = valor_bruto_saida;
        if (!bruto_saida_total || bruto_saida_total <= 0) {
            // calcula unitário importado e multiplica
            const unit_importado = (valor_bruto_importado_raw) ? (valor_bruto_importado_raw / Math.max(1, quantidadeDisponivel)) : 0;
            bruto_saida_total = unit_importado * quantidade_vendida;
        }

        // Valor líquido do vendedor considerando taxa de saída (simplificado)
        const valor_liquido_saida = bruto_saida_total * (1 - (taxa_saida / 100));

        // Preço unitário vendedor
        const preco_unitario_vendedor = (quantidade_vendida > 0) ? (valor_liquido_saida / quantidade_vendida) : 0;

        // Ganho do vendedor = valor líquido recebido - custo importado proporcional
        const custo_importado_total = (valor_bruto_importado_raw) ? (valor_bruto_importado_raw / Math.max(1, quantidadeDisponivel) * quantidade_vendida) : 0;
        const ganho_vendedor = valor_liquido_saida - custo_importado_total;
        const rentabilidade_vendedor = (custo_importado_total > 0) ? (ganho_vendedor / custo_importado_total * 100) : 0;

        // Preço unitário comprador (se informado valor_entrada)
        const preco_unitario_comprador = (quantidade_vendida > 0 && valor_entrada > 0) ? (valor_entrada / quantidade_vendida) : 0;

        // Corretagem e ROA para assessor: assumimos corretagem = valor_plataforma; roa = corretagem / valor_entrada
        const corretagem = valor_plataforma;
        const roa = (valor_entrada > 0) ? (corretagem / valor_entrada * 100) : 0;

        // Atualiza campos de exibição
        const valorLiquidoInput = document.getElementById('valor_liquido_saida');
        // Se o usuário já inseriu manualmente um valor, não sobrescreve
        if (!valorLiquidoInput.dataset.userSet) {
            valorLiquidoInput.value = valor_liquido_saida.toFixed(2);
        }
        document.getElementById('preco_unitario_vendedor').innerText = formatBRL(preco_unitario_vendedor);
        document.getElementById('ganho_vendedor').innerText = formatBRL(ganho_vendedor);
        document.getElementById('rentabilidade_vendedor').innerText = rentabilidade_vendedor.toFixed(2) + '%';

        document.getElementById('preco_unitario_comprador').innerText = formatBRL(preco_unitario_comprador);
        document.getElementById('corretagem_assessor').innerText = formatBRL(corretagem);
        document.getElementById('roa_assessor').innerText = roa.toFixed(2) + '%';

        // Guarda valores em hidden inputs para envio ao servidor
        document.getElementById('preco_unitario_saida_hidden').value = preco_unitario_vendedor.toFixed(2);
        document.getElementById('preco_unitario_entrada_hidden').value = preco_unitario_comprador.toFixed(2);
        document.getElementById('ganho_saida_hidden').value = ganho_vendedor.toFixed(2);
        document.getElementById('rentabilidade_saida_hidden').value = rentabilidade_vendedor.toFixed(2);
        document.getElementById('corretagem_hidden').value = corretagem.toFixed(2);
        document.getElementById('roa_hidden').value = roa.toFixed(2);
    }

    // Valida ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        atualizarPreview();
        // atualiza também quando valores mudarem (segurança extra)
        ['taxa_saida','taxa_entrada','valor_bruto_saida','valor_entrada','valor_plataforma','quantidade_vendida'].forEach(function(id){
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', atualizarPreview);
        });

        // Marcar se usuário editar manualmente o valor líquido
        const valorLiquidoInput = document.getElementById('valor_liquido_saida');
        if (valorLiquidoInput) {
            valorLiquidoInput.addEventListener('input', function(){
                this.dataset.userSet = '1';
                atualizarPreview();
            });
        }
    });
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
