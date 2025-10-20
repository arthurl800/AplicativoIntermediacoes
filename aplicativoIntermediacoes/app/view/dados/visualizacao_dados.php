<!-- app/view/data/visualizacao_dados.php -->
<main>
    <h2 style="margin-bottom: 20px;">Análise de Dados de Intermediações</h2>
    
    <!-- Painel de Sumarização e Ações -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div style="background-color: #f0f8ff; border-left: 5px solid #007bff; padding: 15px; border-radius: 4px; flex-grow: 1; margin-right: 10px;">
            <p><strong>Total de Registros Exibidos:</strong> <?= number_format(count($dados), 0, ',', '.') ?></p>
            <p style="font-size: 0.9em; margin-top: 5px;">* Exibindo registros filtrados ou limitados a 100.</p>
        </div>
        
        <a href="index.php?controller=dados&action=dashboard"
           style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; white-space: nowrap;">
            Ir para Dashboard (KPIs)
        </a>
    </div>

    <!-- Botão rápido para visualizar colunas específicas -->
    <div style="margin-bottom: 15px;">
        <?php if (!empty($availableColumns)): ?>
            <div style="margin-bottom:8px; font-size:0.95em; color:#333;">Colunas disponíveis: <strong><?= htmlspecialchars(implode(', ', $availableColumns)) ?></strong></div>

            <!-- Exemplo: link que busca apenas as colunas solicitadas pelo usuário -->
            <?php
                // Colunas de exemplo pedidas pelo usuário
                $sampleCols = ['Conta','Nome','Produto','Estrategia','Emissor','Vencimento','Taxa_Compra','Quantidade','Valor_Bruto','IOF','IR','Valor_Liquido','Data_Compra'];
                // Intersect com as colunas disponíveis para evitar SQL injection
                $validSample = array_values(array_intersect($availableColumns, $sampleCols));
                $colsParam = htmlspecialchars(implode(',', $validSample));
            ?>
            <?php if (!empty($validSample)): ?>
                <a href="index.php?controller=dados&action=visualizar&columns=<?= $colsParam ?>" style="background:#007bff;color:white;padding:8px 12px;border-radius:4px;text-decoration:none;font-weight:bold;">Ver colunas selecionadas (exemplo)</a>
            <?php else: ?>
                <div style="color:#856404;background:#fff3cd;padding:10px;border-radius:4px;">Nenhuma das colunas de exemplo está presente na tabela.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Formulário de Filtros -->
    <form method="GET" action="index.php" style="background-color: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <input type="hidden" name="controller" value="dados">
        <input type="hidden" name="action" value="visualizar">
        
        <h3 style="margin-top: 0; font-size: 1.2em;">Filtros</h3>
        
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            
            <!-- Filtro por Mercado -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filtro_mercado" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em;">Mercado:</label>
                <input type="text" id="filtro_mercado" name="mercado" 
                       value="<?= htmlspecialchars($_GET['mercado'] ?? '') ?>"
                       placeholder="Ex: Renda Fixa"
                       style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <!-- Filtro por Sub-Mercado -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filtro_submercado" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em;">Sub-Mercado:</label>
                <input type="text" id="filtro_submercado" name="sub_mercado" 
                       value="<?= htmlspecialchars($_GET['sub_mercado'] ?? '') ?>"
                       placeholder="Ex: Privado"
                       style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <!-- Filtro por Ativo -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filtro_ativo" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em;">Ativo:</label>
                <input type="text" id="filtro_ativo" name="ativo" 
                       value="<?= htmlspecialchars($_GET['ativo'] ?? '') ?>"
                       placeholder="Ex: DEBÊNTURE"
                       style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <!-- Botões de Ação -->
            <div style="display: flex; align-items: flex-end; gap: 10px;">
                <button type="submit" 
                        style="background-color: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    Aplicar Filtros
                </button>
                <a href="index.php?controller=dados&action=visualizar" 
                   style="background-color: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    Limpar
                </a>
            </div>
        </div>
    </form>

    <?php if (empty($dados)): ?>
        <div style="padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;">
            <p style="font-size: 1.1em; margin-bottom: 15px;">
                <strong>Nenhum dado de intermediação encontrado.</strong>
            </p>
            <?php if (!empty(array_filter($_GET, fn($v, $k) => in_array($k, ['mercado', 'sub_mercado', 'ativo']), ARRAY_FILTER_USE_BOTH))): ?>
                <p style="margin-bottom: 20px;">Sua pesquisa não retornou resultados. Tente limpar os filtros acima.</p>
            <?php else: ?>
                <p style="margin-bottom: 20px;">
                    Seus dados de intermediação ainda não foram importados ou a tabela está vazia.
                </p>
            <?php endif; ?>

            <a href="index.php?controller=upload&action=index" 
               style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Ir para a Página de Importação (Upload)
            </a>
        </div>
    <?php else: ?>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; min-width: 1200px; border-collapse: collapse; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background-color: #007bff; color: white; text-transform: uppercase;">
                        <?php 
                        $headers = array_keys($dados[0]);
                        foreach ($headers as $header): ?>
                            <th style="padding: 12px 10px; text-align: left; font-size: 0.8em; border: 1px solid #0056b3;">
                                <?= htmlspecialchars(str_replace('_', ' ', $header)) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($dados as $row): 
                        $rowStyle = (reset($row) % 2 === 0) ? 'background-color: #f9f9f9;' : 'background-color: #ffffff;';
                    ?>
                        <tr style="<?= $rowStyle ?>">
                            <?php 
                            foreach ($row as $key => $cell): 
                                $formatted_cell = htmlspecialchars($cell);
                                $style = 'text-align: left;';
                                
                                // Formatação Condicional
                                if (in_array($key, ['Valor_Bruto', 'IR', 'IOF', 'Valor_Liquido'])) {
                                    $formatted_cell = 'R$ ' . number_format((float)$cell, 2, ',', '.');
                                    $style = 'text-align: right; font-weight: bold; color: #008000; white-space: nowrap;';
                                } elseif (in_array($key, ['Taxa_Compra', 'Taxa_Emissao'])) {
                                    $formatted_cell = number_format((float)$cell, 4, ',', '.') . ' %'; 
                                    $style = 'text-align: center; white-space: nowrap;';
                                } elseif (in_array($key, ['Quantidade'])) {
                                    $formatted_cell = number_format((int)$cell, 0, ',', '.');
                                    $style = 'text-align: center;';
                                } elseif (in_array($key, ['Data_Compra', 'Data_Registro', 'Data_Cotizacao_Prev', 'Vencimento'])) {
                                    $style = 'text-align: center; white-space: nowrap;';
                                }
                            ?>
                                <td style="padding: 8px 10px; border: 1px solid #eee; font-size: 0.85em; <?= $style ?>">
                                    <?= $formatted_cell ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>
