<?php
// app/dados/ViewData.php
?>
<?php if (isset($aggregates) && count($aggregates) > 0): ?>
    <div class="table-container">
        <!-- Compact header for mobile: sticky and compact labels -->
        <div id="compact-header" class="compact-header">
            <div class="compact-header-content">
                <div class="compact-header-info">
                        <span>Cliente</span>
                        <span>Produto</span>
                        <span>Qtd.</span>
                        <span>Vl. Líquido</span>
                    </div>
                    <div class="compact-header-actions">Ações</div>
                </div>
            </div>
            <div class="table-wrapper">
                <div id="table-container" class="table-scroll-container">
                    <table id="data-table" class="data-table">
                        <thead>
                            <!-- Cabeçalhos de Coluna -->
                        </thead>
                        <tbody id="table-body">
                            <!-- Linhas de Dados -->
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    <?php else: ?> 
        <section class="message warning text-center">
            <p class="font-bold text-xl mb-3">
                Nenhum investimento negociável encontrado.
            </p>
            <p class="mb-4">
                Verifique se há dados importados. A lista acima é baseada em dados agrupados e filtrados do banco de dados.
            </p>
            <a href="index.php?controller=upload&action=index" class="btn btn-primary">
                Ir para a Página de Importação (Upload)
            </a>
        </section>
    <?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // ** INJEÇÃO DE DADOS PHP AQUI **
        const rawData = <?= json_encode($aggregates, JSON_NUMERIC_CHECK); ?>;
        // ** FIM DA INJEÇÃO **
        
        // Estado da Tabela
        let currentSort = { key: 'Produto', direction: 'asc' };
        let currentFilter = '';
        let dataSet = [...rawData]; // Cópia mutável dos dados originais

        // Mapeamento de colunas (as chaves devem corresponder exatamente às chaves de $aggregates)
        const columnMap = {
            Conta: { alias: 'Conta', type: 'string' },
            Nome: { alias: 'Cliente', type: 'string' },
            Produto: { alias: 'Produto', type: 'string' },
            Estrategia: { alias: 'Indexador', type: 'string' },
            Emissor: { alias: 'Emissor (CNPJ)', type: 'string' },
            Vencimento: { alias: 'Vencimento', type: 'date' },
            Taxa_Emissao: { alias: 'Taxa (%)', type: 'number' },
            Quantidade: { alias: 'Qtd.', type: 'number' },
            Valor_Bruto: { alias: 'Vl. Bruto', type: 'currency' },
            IOF: { alias: 'IOF', type: 'currency' },
            IR: { alias: 'IR', type: 'currency' },
            Valor_Liquido: { alias: 'Vl. Líquido', type: 'currency' },
            Data_Compra: { alias: 'Data Compra', type: 'date' },
        };

        // --- Funções de Formatação ---

        /** Formata um CNPJ ou retorna o texto. */
        function formatCNPJ(value) {
            if (!value) return '---';
            const cnpj = String(value).replace(/\D/g, '');
            if (cnpj.length === 14) {
                return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            }
            return value;
        }

        /** * Formata um número como moeda brasileira (R$). 
         * O valor é dividido por 100 antes de formatar.
         */
        function formatCurrency(value) {
            const scaledValue = (value || 0) / 100; // CORREÇÃO: Divide por 100
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(scaledValue);
        }

        /** Formata data YYYY-MM-DD para DD/MM/YYYY. */
        function formatDate(dateString) {
            if (!dateString) return '---';
            try {
                // Tenta lidar com YYYY-MM-DD (padrão DB) ou DD/MM/YYYY
                const dateParts = dateString.split(/[-\/]/).map(p => parseInt(p, 10));
                let date;

                if (dateParts.length === 3) {
                    // Se o primeiro valor for 4 dígitos (provavelmente ano), assume YYYY-MM-DD
                    if (dateParts[0] > 31 && dateParts[0] > 1900) { 
                        date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // YYYY, MM, DD
                    } else {
                        // Assume DD/MM/YYYY
                        date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]); // YYYY, MM, DD
                    }
                } else {
                    date = new Date(dateString);
                }

                if (isNaN(date.getTime())) return dateString;
                return new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
            } catch (e) {
                return dateString;
            }
        }
        
        /** * Formata taxa percentual. 
         * O valor é dividido por 100 antes de formatar.
         * *NOTA: Corrigido o erro de formatação que não incluía o '%'.
         */
        function formatRate(rate) {
            if (rate === null || rate === undefined) return '---';
            const scaledRate = parseFloat(rate) / 1;
            const num = parseFloat(scaledRate);
            if (isNaN(num)) return '---';
            // Usa toFixed para garantir 2 casas decimais e substitui '.' por ',' para padrão BR
            return num.toFixed(2).replace('.', ',');
        }
        
        /** Formata Quantidade. */
        function formatQuantity(value) {
            if (value === null || value === undefined) return '0';
            return new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(value);
        }


        // --- Funções de Manipulação de Dados ---

        /** Converte data para valor comparável para ordenação. */
        function dateToSortValue(dateStr) {
            if (!dateStr) return 0;
            // Padroniza YYYY-MM-DD para comparação de strings
            const parts = dateStr.split(/[-\/]/).map(p => String(p).padStart(2, '0'));
            
            // Assume YYYY-MM-DD se o primeiro valor for 4 dígitos (padrão DB)
            if (parts[0].length === 4) {
                 return parts.join(''); // YYYYMMDD
            }
            // Assume DD/MM/YYYY
            if (parts.length === 3) {
                return parts[2] + parts[1] + parts[0]; // YYYYMMDD
            }
            return 0;
        }

        /** Função de ordenação genérica. */
        function sortData(key, direction, type) {
            const isDesc = direction === 'desc';

            dataSet.sort((a, b) => {
                let valA = a[key] ?? (type === 'string' ? '' : -Infinity);
                let valB = b[key] ?? (type === 'string' ? '' : -Infinity);

                if (type === 'number' || type === 'currency') {
                    valA = parseFloat(valA);
                    valB = parseFloat(valB);
                    if (isNaN(valA)) valA = -Infinity;
                    if (isNaN(valB)) valB = -Infinity;

                    return isDesc ? valB - valA : valA - valB;
                } else if (type === 'date') {
                    valA = dateToSortValue(valA);
                    valB = dateToSortValue(valB);
                    return isDesc ? (valB > valA ? 1 : -1) : (valA > valB ? 1 : -1);
                } else { // string
                    return isDesc ? String(valB).localeCompare(String(valA)) : String(valA).localeCompare(String(valB));
                }
            });

            currentSort = { key, direction };
            renderTable(dataSet);
        }

        /** Função de callback para a caixa de pesquisa. */
        window.handleGlobalSearch = function(term) {
            currentFilter = term.toLowerCase().trim();
            
            if (!currentFilter) {
                dataSet = [...rawData]; 
            } else {
                dataSet = rawData.filter(item => 
                    Object.values(item).some(value => {
                        return String(value).toLowerCase().includes(currentFilter);
                    })
                );
            }
            const currentMap = columnMap[currentSort.key];
            if(currentMap) {
                sortData(currentSort.key, currentSort.direction, currentMap.type);
            } else {
                 renderTable(dataSet);
            }
        }

        /** Calcula e exibe os totais. */
        function updateTotals(data) {
            const totalBruto = data.reduce((sum, item) => sum + (parseFloat(item.Valor_Bruto) || 0), 0);
            const totalLiquido = data.reduce((sum, item) => sum + (parseFloat(item.Valor_Liquido) || 0), 0);

            // formatCurrency agora aplica a divisão por 100
            document.getElementById('total-bruto').textContent = formatCurrency(totalBruto);
            document.getElementById('total-liquido').textContent = formatCurrency(totalLiquido);
        }

        // --- Renderização Principal ---

        /** Cria os cabeçalhos da tabela e adiciona a lógica de ordenação. */
        function createHeader() {
            const thead = document.querySelector('#data-table thead');
            thead.innerHTML = '';
            const row = thead.insertRow();
            
            const visibleKeys = Object.keys(columnMap);

            visibleKeys.forEach(key => {
                const map = columnMap[key];
                const th = document.createElement('th');
                
                th.className = "px-4 py-3 text-xs font-semibold uppercase tracking-wider transition duration-150 ease-in-out";
                
                const isSortable = map.type !== 'string' || ['Produto', 'Nome', 'Estrategia'].includes(key);

                if (isSortable) {
                    th.classList.add('sortable', 'cursor-pointer', 'hover:bg-[var(--primary-dark)]');
                    th.dataset.key = key;
                    th.dataset.type = map.type;
                    
                    const iconUp = `<span class="sort-icon sort-icon-up" aria-hidden="true">&#9650;</span>`;
                    const iconDown = `<span class="sort-icon sort-icon-down" aria-hidden="true">&#9660;</span>`;

                    th.innerHTML = `<div class="flex items-center justify-between"><span>${map.alias}</span><div class="flex flex-col text-xs">${iconUp}${iconDown}</div></div>`;
                    
                    th.addEventListener('click', () => {
                        let direction = 'asc';
                        if (currentSort.key === key && currentSort.direction === 'asc') {
                            direction = 'desc';
                        } else if (currentSort.key !== key) {
                            direction = 'asc';
                        }
                        sortData(key, direction, map.type);
                    });
                } else {
                     th.textContent = map.alias;
                     th.classList.add('bg-[var(--primary-color)]');
                }

                // Ajuste de alinhamento visual para valores
                if (['Taxa_Emissao', 'Quantidade', 'Valor_Bruto', 'IOF', 'IR', 'Valor_Liquido'].includes(key)) {
                    th.style.textAlign = 'right';
                    if (isSortable) {
                         th.querySelector('div').classList.add('flex-row-reverse');
                         th.querySelector('span').classList.remove('justify-between');
                    }
                }
                
                row.appendChild(th);
            });
            
            // Coluna Ações
            const thActions = document.createElement('th');
            thActions.className = "px-4 py-3 text-xs font-semibold uppercase tracking-wider text-center";
            thActions.textContent = 'Ações';
            row.appendChild(thActions);
        }
        
        /** Renderiza o corpo da tabela com os dados atuais. */
        function renderTable(data) {
            const tbody = document.getElementById('table-body');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${Object.keys(columnMap).length + 1}" class="py-10 text-center text-gray-500 italic">Nenhum resultado encontrado. Tente redefinir a busca.</td></tr>`;
            }

            data.forEach((item) => {
                const row = tbody.insertRow();
                row.className = 'hover:bg-blue-50 transition duration-100 ease-in-out';
                
                // Mapeamento das classes Tailwind para alinhamento
                const alignClasses = { 
                    'Valor_Bruto': 'text-align-right text-strong',
                    'Valor_Liquido': 'text-align-right text-strong text-success-color',
                    'IOF': 'text-align-right text-muted text-danger-color',
                    'IR': 'text-align-right text-muted text-danger-color',
                    'Taxa_Emissao': 'text-align-right text-info-color',
                    'Quantidade': 'text-align-right text-muted',
                    'Vencimento': 'text-align-center text-muted',
                    'Data_Compra': 'text-align-center text-muted',
                    'Emissor': 'text-muted',
                    'default': '', // Default is left-aligned, no special color
                };
                
                const visibleKeys = Object.keys(columnMap);
                
                visibleKeys.forEach(key => {
                    const cell = row.insertCell();
                    cell.className = `px-4 py-2 text-sm ${alignClasses[key] || alignClasses.default}`;
                    // Define o label legível para o modo responsivo (data-label)
                    const label = (columnMap[key] && columnMap[key].alias) ? columnMap[key].alias : key;
                    cell.setAttribute('data-label', label + ':');
                    
                    const value = item[key];
                    let displayValue = value;

                    // Formatação
                    if (key === 'Valor_Bruto' || key === 'Valor_Liquido' || key === 'IOF' || key === 'IR') {
                        displayValue = formatCurrency(value);
                    } else if (key === 'Taxa_Emissao') {
                        displayValue = formatRate(value);
                    } else if (key === 'Vencimento' || key === 'Data_Compra') {
                        displayValue = formatDate(value);
                    } else if (key === 'Emissor') {
                        displayValue = formatCNPJ(value);
                    } else if (key === 'Quantidade') {
                        displayValue = formatQuantity(value);
                    } else {
                         displayValue = value || '---';
                    }

                    // Para o layout móvel, exibimos o label no ::before; aqui colocamos o valor real
                    const textNode = document.createTextNode(displayValue);
                    cell.appendChild(textNode);
                });

                // Coluna Ações
                const actionsCell = row.insertCell();
                actionsCell.className = "px-4 py-2 text-center";
                
                // Monta os parâmetros para a ação 'negotiate_form' do PHP
                const params = Object.keys(item).map(k => {
                    const val = item[k] === null || item[k] === undefined ? '' : item[k];
                    return `${k.toLowerCase()}=${encodeURIComponent(val)}`;
                }).join('&');
                
                // BOTÃO DE NEGOCIAÇÃO DESTACADO
                const negotiateLink = `<a href="index.php?controller=dados&action=negotiate_form&${params}" 
                                         class="btn btn-negotiate">
                                         Negociar
                                     </a>`;
                actionsCell.innerHTML = negotiateLink;
            });

            // Atualiza os totais
            updateTotals(data);
            
            // Atualiza os ícones de ordenação no cabeçalho
            updateSortIcons();
        }

        /** Atualiza os ícones de ordenação. */
        function updateSortIcons() {
            document.querySelectorAll('.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
                if (th.dataset.key === currentSort.key) {
                    th.classList.add(currentSort.direction);
                }
            });
        }
        
        // --- Inicialização ---
        if (rawData.length > 0) {
            createHeader();
            const initialKey = currentSort.key;
            sortData(initialKey, currentSort.direction, columnMap[initialKey].type);
        }
    });
</script>
</main>
