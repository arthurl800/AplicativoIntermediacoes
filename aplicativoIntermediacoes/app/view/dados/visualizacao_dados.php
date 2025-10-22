<!-- app/view/data/visualizacao_dados.php -->
<main>
    <h2 style="margin-bottom: 20px;">Painel do Usuário</h2>

    <?php
    // Oculta preview quando explicitamente solicitado via GET (mais confiável que âncora)
    $showPreview = true;
    if (!empty($_GET['only_negotiations']) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#negociacoes') !== false)) {
        $showPreview = false;
    }
    if ($showPreview):
    ?>
           
    <?php endif; ?>

    <?php if (!empty($aggregates)): ?>
        <section id="negociacoes" style="margin-top:20px;">
            <h3>Investimentos Negociáveis por Cliente</h3>
            <div style="overflow-x:auto; background:#fff; padding:10px; border-radius:6px; border:1px solid #e6eef8;">
                <?php
                // Colunas e aliases solicitados
                $userCols = [
                    'Conta' => 'Conta',
                    'Nome' => 'Cliente',
                    'Produto' => 'Tipo',
                    'Estrategia' => 'Indexador',
                    'Emissor' => 'Emissor',
                    'Vencimento' => 'Vencimento',
                    'Taxa_Plataforma' => 'Taxa_Plataforma',
                    'Quantidade' => 'Quantidade',
                    'Valor_Bruto' => 'Valor_Bruto',
                    'IOF' => 'IOF',
                    'IR' => 'IR',
                    'Valor_Liquido' => 'Valor_Liquido',
                    'Data_Compra' => 'Data_Compra'
                ];
                ?>
                <table style="width:100%; border-collapse:collapse; min-width:1000px;">
                    <thead>
                        <tr style="background:#007bff;color:#fff; text-transform:uppercase; font-size:0.85em;">
                            <?php foreach ($userCols as $col => $alias): ?>
                                <th style="padding:8px;border:1px solid #0056b3; text-align:left;"><?= htmlspecialchars($alias) ?></th>
                            <?php endforeach; ?>
                            <th style="padding:8px;border:1px solid #0056b3;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aggregates as $agg): ?>
                            <tr>
                                <?php foreach ($userCols as $col => $alias): ?>
                                    <?php
                                    $cell = $agg[$col] ?? '';
                                    // Formata campos conhecidos
                                    if ($col === 'Quantidade') {
                                        $cell = number_format((int)$cell, 0, ',', '.');
                                    } elseif ($col === 'Valor_Bruto' || $col === 'Valor_Liquido' || $col === 'IOF' || $col === 'IR') {
                                        $cell = 'R$ ' . number_format((float)$cell, 2, ',', '.');
                                    } elseif ($col === 'Taxa_Plataforma') {
                                        $num = (float)$cell;
                                        if ($num > 0 && $num < 1) {
                                            $cell = number_format($num * 100, 4, ',', '.') . ' %';
                                        } else {
                                            $cell = number_format($num, 4, ',', '.') . ' %';
                                        }
                                    } elseif ($col === 'Vencimento' || $col === 'Data_Compra') {
                                        if ($cell) {
                                            $dt = DateTime::createFromFormat('Y-m-d', $cell) ?: DateTime::createFromFormat('d/m/Y', $cell);
                                            $cell = $dt ? $dt->format('d/m/Y') : $cell;
                                        }
                                    } else {
                                        $cell = htmlspecialchars($cell ?? '');
                                    }
                                    ?>
                                    <td style="padding:8px;border:1px solid #eee;"><?= $cell ?></td>
                                <?php endforeach; ?>
                                <td style="padding:8px;border:1px solid #eee; text-align:center;">
                                    <?php
                                    // Monta parâmetros curtos para o formulário de negociação
                                    $params = http_build_query([
                                        'conta' => $agg['Conta'] ?? '',
                                        'nome' => $agg['Nome'] ?? '',
                                        'produto' => $agg['Produto'] ?? '',
                                        'estrategia' => $agg['Estrategia'] ?? '',
                                        'emissor' => $agg['Emissor'] ?? '',
                                        'vencimento' => $agg['Vencimento'] ?? '',
                                        'taxa_emissao' => $agg['Taxa_Emissao'] ?? '',
                                        'taxa_plataforma' => $agg['Taxa_Plataforma'] ?? '',
                                        'quantidade' => $agg['Quantidade'] ?? '',
                                        'valor_bruto' => $agg['Valor_Bruto'] ?? '',
                                        'iof' => $agg['IOF'] ?? '',
                                        'ir' => $agg['IR'] ?? '',
                                        'valor_liquido' => $agg['Valor_Liquido'] ?? '',
                                        'data_compra' => $agg['Data_Compra'] ?? ''
                                    ]);
                                    ?>
                                    <a href="index.php?controller=dados&action=negotiate_form&<?= $params ?>" style="display:inline-block;padding:6px 10px;background:#17a2b8;color:white;border-radius:4px;text-decoration:none;font-weight:bold;">Negociar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
    <?php if (empty($aggregates)): ?>
        <section style="margin-top:20px; padding:12px; background:#fff3cd; border-radius:6px; border:1px solid #ffeeba;">
            <strong>Nenhum investimento negociável encontrado.</strong>
            <p style="margin:6px 0 0 0;">Importe dados ou verifique se os campos esperados (Conta, Cliente, Tipo, Indexador, Emissor, Quantidade, Valor_Bruto) existem na tabela.</p>
        </section>
    <?php endif; ?>

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
                                $formatted_cell = htmlspecialchars($cell ?? '');
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