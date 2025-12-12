<?php
// app/dados/ViewNegociadas.php
// View otimizada para melhor visualização e análise de dados.
?>
<div class="auditoria-container">
    <h2 class="form-section-title">
        Negociações Efetivadas
    </h2>

    <div class="view-toolbar" style="margin-bottom:12px; display:flex; gap:8px; align-items:center;">
        <button id="btnFullscreen" class="btn btn-secondary" type="button">⤢ Tela Cheia</button>
        <small class="text-muted">Use o botão para expandir a tabela. Pressione ESC para sair.</small>
    </div>
    
    <p class="mb-4">
        Esta tabela exibe os registros de títulos <strong>após</strong> alguma negociação efetivada, excluindo títulos com quantidade zero.
    </p>

    <?php if (empty($data)): ?>
        <div class="message warning">
            <p>
                Nenhum registro de negociações efetivadas foi encontrado.
            </p>
        </div>
    <?php else: ?>
        <div class="table-wrapper" id="neg-table-wrapper">
            <div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th title="Data e Hora do Registro" style="color: green;">Data Registro</th>
                            <th style="text-align: center; color: green;">Ativo</th>
                            <th style="color: green;">Estratégia</th>
                            
                            <th style="text-align: right; color: green;">Quant. Neg.</th>
                            <th style="text-align: right; color: green;">Preço Unit. Saída</th>
                            <th style="text-align: right; color: green;">Valor Líq. Saída</th>
                            <th style="text-align: right; color: green;">Ganho Saída (R$)</th>
                            <th style="text-align: right; color: green;">Rentab. Saída (%)</th>

                            <th title="Conta e Nome do Vendedor" style="color: green;">Vendedor (Conta)</th>
                            <th title="Conta e Nome do Comprador" style="color: blue;">Comprador (Conta)</th>
                            
                            <th style="text-align: right; color: blue;">Taxa Entr. (%)</th>
                            <th style="text-align: right; color: blue;">Valor Bruto Entr. (R$)</th>
                            <th style="text-align: right; color: green;">ROA (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td data-label="Data Registro">
                                    <?php echo !empty($row['Data_Registro']) ? date('d/m/Y H:i', strtotime($row['Data_Registro'])) : '' ?>
                                </td>
                                <td data-label="Ativo"><?= htmlspecialchars($row['Produto'] ?? '') ?></td>
                                <td data-label="Estratégia"><?= htmlspecialchars($row['Estrategia'] ?? '') ?></td>

                                <?php 
                                    // Cálculo e formatação de variáveis
                                    $precoUnitSaida = $row['Preco_Unitario_Saida'] ?? 0;
                                    $valorLiqSaida = $row['Valor_Liquido_Saida'] ?? 0;
                                    $ganhoSaida = $row['Ganho_Saida'] ?? 0;
                                    
                                    // Rentabilidade: Normalizando para percentual (ex: 0.05 -> 5%)
                                    $rent = $row['Rentabilidade_Saida'] ?? $row['rentabilidade_saida'] ?? 0;
                                    if (abs($rent) <= 1) $rent = $rent * 100;
                                    
                                    // Taxa de Entrada: Normalizando para percentual
                                    $taxEntrada = $row['Taxa_Entrada'] ?? 0;
                                    if (abs($taxEntrada) <= 1) $taxEntrada = $taxEntrada * 100;
                                    
                                    $roa = $row['Roa_Assessor'] ?? $row['roa_assessor'] ?? 0;
                                ?>
                                
                                <td data-label="Quantidade" style="text-align: right; font-weight: 500;">
                                    <?= number_format($row['Quantidade_negociada'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td data-label="Preço Unit. Saída" style="text-align: right;">R$ <?= number_format($precoUnitSaida, 2, ',', '.') ?></td>
                                <td data-label="Valor Líquido Saída" style="text-align: right; font-weight: 600;">R$ <?= number_format($valorLiqSaida, 2, ',', '.') ?></td>
                                
                                <td data-label="Ganho Saída" 
                                    style="text-align: right; font-weight: 600; color: <?= $ganhoSaida >= 0 ? '#198754' : '#dc3545' ?>;">
                                    R$ <?= number_format($ganhoSaida, 2, ',', '.') ?>
                                </td>
                                <td data-label="Rentabilidade" 
                                    style="text-align: right; font-weight: 600; color: <?= $rent >= 0 ? '#198754' : '#dc3545' ?>;">
                                    <?= number_format($rent, 2, ',', '.') ?> %
                                </td>

                                <td data-label="Vendedor (Conta)">
                                    <?= htmlspecialchars($row['Conta_Vendedor'] ?? '') ?> 
                                    <small>(<?= htmlspecialchars($row['Nome_Vendedor'] ?? '') ?>)</small>
                                </td>
                                <td data-label="Comprador (Conta)">
                                    <?= htmlspecialchars($row['Conta_Comprador'] ?? '') ?>
                                    <small>(<?= htmlspecialchars($row['Nome_Comprador'] ?? '') ?>)</small>
                                </td>

                                <td data-label="Taxa Entrada" style="text-align: right;"><?= number_format($taxEntrada, 2, ',', '.') ?> %</td>
                                <td data-label="Valor Bruto Entrada" style="text-align: right;">R$ <?= number_format($row['Valor_Bruto_Entrada'] ?? 0, 2, ',', '.') ?></td>
                                <td data-label="ROA" style="text-align: right;"><?= number_format($roa, 2, ',', '.') ?> %</td>
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

<style>
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
        -webkit-overflow-scrolling: touch;
        background: #fff; /* Fundo branco no wrapper para destacar */
        border-radius: 6px;
        padding: 6px 0;
        border: 1px solid #ddd; /* Borda leve */
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px; /* Largura mínima ajustada para menos colunas */
    }

    .data-table th,
    .data-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 0.9em; /* Levemente menor para caber mais dados */
    }

    .data-table th {
        background-color: #f8f9fa; /* Leve sombreamento no header */
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8em;
    }

    .expanded-table {
        position: fixed !important;
        top: 8px !important;
        left: 8px !important;
        right: 8px !important;
        bottom: 8px !important;
        background: #ffffff !important;
        z-index: 1200 !important;
        padding: 14px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
        overflow: auto !important;
        border-radius: 6px !important;
    }

    .btn { cursor: pointer; padding: 6px 10px; border-radius: 4px; border: 1px solid #ccc; background:#f5f5f5; }
    .btn-secondary { background:#eceff1; }
</style>

<script>
    (function(){
        const btn = document.getElementById('btnFullscreen');
        const wrapper = document.getElementById('neg-table-wrapper');
        if (!btn || !wrapper) return;

        // Verifica se a API de Tela Cheia é suportada
        function isFullscreenSupported() {
            return !!(document.fullscreenEnabled || document.webkitFullscreenEnabled || document.mozFullScreenEnabled || document.msFullscreenEnabled);
        }

        // Entra no modo tela cheia
        function enterFullscreen(el) {
            if (el.requestFullscreen) return el.requestFullscreen();
            if (el.webkitRequestFullscreen) return el.webkitRequestFullscreen();
            if (el.mozRequestFullScreen) return el.mozRequestFullScreen();
            if (el.msRequestFullscreen) return el.msRequestFullscreen();
            return Promise.reject(new Error('Fullscreen API não suportada'));
        }

        // Sai do modo tela cheia
        function exitFullscreen() {
            if (document.exitFullscreen) return document.exitFullscreen();
            if (document.webkitExitFullscreen) return document.webkitExitFullscreen();
            if (document.mozCancelFullScreen) return document.mozCancelFullScreen();
            if (document.msExitFullscreen) return document.msExitFullscreen();
            return Promise.reject(new Error('Fullscreen API não suportada'));
        }

        let expandedFallback = false;
        btn.addEventListener('click', function(){
            // Se o suporte estiver disponível, usa a API nativa
            if (isFullscreenSupported()) {
                if (!document.fullscreenElement) {
                    enterFullscreen(wrapper).catch(()=>{
                        // Se falhar (permissão negada, etc.), usa o fallback CSS
                        wrapper.classList.add('expanded-table');
                        expandedFallback = true;
                    });
                } else {
                    exitFullscreen().catch(()=>{});
                }
            } else {
                // Se o suporte não estiver disponível, usa apenas o fallback CSS
                if (wrapper.classList.contains('expanded-table')) {
                    wrapper.classList.remove('expanded-table');
                    expandedFallback = false;
                } else {
                    wrapper.classList.add('expanded-table');
                    expandedFallback = true;
                }
            }
        });

        // Remove a classe CSS quando o usuário sai do Fullscreen nativamente (ESC)
        document.addEventListener('fullscreenchange', function(){
            if (!document.fullscreenElement && expandedFallback) {
                wrapper.classList.remove('expanded-table');
                expandedFallback = false;
            }
        });

        // Adiciona funcionalidade de ESC para sair do fallback CSS
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' || e.key === 'Esc') {
                if (wrapper.classList.contains('expanded-table')) {
                    wrapper.classList.remove('expanded-table');
                }
            }
        });
    })();
</script>