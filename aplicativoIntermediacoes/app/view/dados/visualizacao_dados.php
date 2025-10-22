<!-- app/view/data/visualizacao_dados.php -->
<main>
    <h2 style="margin-bottom: 20px;">Painel de Negociações</h2>

    <?php 
    // Garante que $aggregates seja um array para evitar erros de loop se não houver dados
    $aggregates = $aggregates ?? []; 
    ?>
         
    <?php if (!empty($aggregates)): ?>
        <section id="negociacoes" style="margin-top:20px;">
            <h3>Investimentos Negociáveis por Cliente</h3>
            <div style="overflow-x:auto; background:#fff; padding:10px; border-radius:6px; border:1px solid #e6eef8;">
                <?php
                // Mapeamento de colunas do banco para exibição na tabela
                $userCols = [
                    'Conta' => 'Conta',
                    'Nome' => 'Cliente',
                    'Produto' => 'Tipo de Produto',
                    'Estrategia' => 'Indexador',
                    'Emissor' => 'Emissor',        
                    'Vencimento' => 'Vencimento',  
                    'Taxa_Emissao' => 'Taxa Emissão',
                    'Quantidade' => 'Quantidade',
                    'Valor_Bruto' => 'Valor Bruto',
                    'IOF' => 'IOF',
                    'IR' => 'IR',
                    'Valor_Liquido' => 'Valor Líquido',
                    'Data_Compra' => 'Data Compra'
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
                            <?php 
                            // 🔧 NOVO: normaliza todas as chaves para minúsculas
                            $aggLower = array_change_key_case($agg, CASE_LOWER);
                            ?>
                            <tr>
                                <?php foreach ($userCols as $col => $alias): ?>
                                    <?php
                                    // Acessa o valor pela chave em minúsculas
                                    $key = strtolower($col);
                                    $cell = $aggLower[$key] ?? '';

                                    // === Formatações ===
                                    if ($key === 'quantidade') {
                                        $cell = number_format((int)$cell, 0, ',', '.');
                                    } elseif (in_array($key, ['valor_bruto', 'valor_liquido', 'iof', 'ir'])) {
                                        // Divide por 100 para adequar ao padrão brasileiro
                                        $cell = 'R$ ' . number_format((float)$cell / 100, 2, ',', '.');
                                    } elseif ($key === 'taxa_emissao') {
                                        $num = (float)$cell;
                                        if ($num > 0 && $num < 1) {
                                            $cell = number_format($num * 100, 2, ',', '.') . ' %';
                                        } else {
                                            $cell = number_format($num, 2, ',', '.') . ' %';
                                        }
                                    } elseif (in_array($key, ['vencimento', 'data_compra'])) {
                                        if ($cell) {
                                            $dt = DateTime::createFromFormat('Y-m-d', $cell) ?: DateTime::createFromFormat('d/m/Y', $cell);
                                            $cell = $dt ? $dt->format('d/m/Y') : $cell;
                                        }
                                    } elseif ($key === 'emissor') {
                                        $cnpj = preg_replace('/[^0-9]/', '', (string)$cell);
                                        if (strlen($cnpj) === 14) {
                                            $cell = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
                                        } else {
                                            $cell = htmlspecialchars($cell ?? '');
                                        }
                                    } else {
                                        $cell = htmlspecialchars($cell ?? '');
                                    }
                                    ?>
                                    <td style="padding:8px;border:1px solid #eee;"><?= $cell ?></td>
                                <?php endforeach; ?>

                                <!-- Coluna Ações -->
                                <td style="padding:8px;border:1px solid #eee; text-align:center;">
                                    <?php
                                    // Monta parâmetros curtos para o formulário de negociação
                                    $params = http_build_query([
                                        'conta' => $aggLower['conta'] ?? '',
                                        'nome' => $aggLower['nome'] ?? '',
                                        'produto' => $aggLower['produto'] ?? '',
                                        'estrategia' => $aggLower['estrategia'] ?? '',
                                        'emissor' => $aggLower['emissor'] ?? '',
                                        'vencimento' => $aggLower['vencimento'] ?? '',
                                        'taxa_emissao' => $aggLower['taxa_emissao'] ?? '',
                                        'quantidade' => $aggLower['quantidade'] ?? '',
                                        'valor_bruto' => $aggLower['valor_bruto'] ?? '',
                                        'iof' => $aggLower['iof'] ?? '',
                                        'ir' => $aggLower['ir'] ?? '',
                                        'valor_liquido' => $aggLower['valor_liquido'] ?? '',
                                        'data_compra' => $aggLower['data_compra'] ?? ''
                                    ]);
                                    ?>
                                    <a href="index.php?controller=dados&action=negotiate_form&<?= $params ?>" 
                                       style="display:inline-block;padding:6px 10px;background:#17a2b8;color:white;border-radius:4px;text-decoration:none;font-weight:bold;">
                                       Negociar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
    
    <?php if (empty($aggregates)): ?>
        <section style="margin-top:20px; padding:20px; background:#fff3cd; border-radius:6px; border:1px solid #ffeeba; text-align:center;">
            <p style="font-size: 1.1em; margin-bottom: 15px;">
                <strong>Nenhum investimento negociável encontrado.</strong>
            </p>
            <p style="margin-bottom: 20px;">
                Verifique se há dados importados. A lista acima é baseada em dados agrupados e filtrados do banco de dados.
            </p>
            <a href="index.php?controller=upload&action=index" 
               style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Ir para a Página de Importação (Upload)
            </a>
        </section>
    <?php endif; ?>
</main>

