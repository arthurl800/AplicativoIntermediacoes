<?php
// app/view/relatorio/dashboard.php
?>

<div class="dashboard-container">
    <h1>Dashboard de Negociações</h1>
    
    <!-- Resumo Executivo -->
    <div class="resumo-executivo">
        <div class="card-kpi">
            <h3>Total de Negociações</h3>
            <p class="kpi-valor"><?php echo isset($resumo[0]['total_negociacoes']) ? number_format($resumo[0]['total_negociacoes'], 0, ',', '.') : '0'; ?></p>
        </div>
        <div class="card-kpi">
            <h3>Valor Total (R$)</h3>
            <p class="kpi-valor">R$ <?php echo isset($resumo[0]['valor_saida_total']) ? number_format($resumo[0]['valor_saida_total'], 2, ',', '.') : '0,00'; ?></p>
        </div>
        <div class="card-kpi">
            <h3>Quantidade Total</h3>
            <p class="kpi-valor"><?php echo isset($resumo[0]['quantidade_total']) ? number_format($resumo[0]['quantidade_total'], 0, ',', '.') : '0'; ?></p>
        </div>
        <div class="card-kpi">
            <h3>Clientes Únicos</h3>
            <p class="kpi-valor"><?php echo isset($resumo[0]['clientes_unicos']) ? number_format($resumo[0]['clientes_unicos'], 0, ',', '.') : '0'; ?></p>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="graficos-container">
        <!-- Gráfico: Negociações por Operador -->
        <div class="grafico-box">
            <h3>Negociações por Operador</h3>
            <canvas id="chartOperadores"></canvas>
        </div>

        <!-- Gráfico: Negociações por Produto -->
        <div class="grafico-box">
            <h3>Negociações por Produto</h3>
            <canvas id="chartProdutos"></canvas>
        </div>

        <!-- Gráfico: Tendência de Negociações (Últimos 30 dias) -->
        <div class="grafico-box grafico-full">
            <h3>Tendência de Negociações (Últimos 30 dias)</h3>
            <canvas id="chartTendencia"></canvas>
        </div>
    </div>

    <!-- Botões de Exportação -->
    <div class="export-buttons">
        <a href="index.php?controller=relatorio&action=exportarCSV" class="btn btn-info">
            📥 Exportar Relatório (CSV)
        </a>
        <a href="index.php?controller=relatorio&action=auditoria" class="btn btn-secondary">
            📋 Histórico de Auditoria
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Dados dos gráficos (passados do PHP)
var dataOperadores = <?php echo $dataOperadores; ?>;
var dataProdutos = <?php echo $dataProdutos; ?>;
var dataPorDia = <?php echo $dataPorDia; ?>;

// Gráfico: Negociações por Operador (Bar Chart)
var ctxOperadores = document.getElementById('chartOperadores').getContext('2d');
new Chart(ctxOperadores, {
    type: 'bar',
    data: {
        labels: dataOperadores.labels,
        datasets: [
            {
                label: 'Negociações',
                data: dataOperadores.negociacoes,
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1
            },
            {
                label: 'Valor (R$)',
                data: dataOperadores.valores,
                backgroundColor: '#28a745',
                borderColor: '#1e7e34',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});

// Gráfico: Negociações por Produto (Pie Chart)
var ctxProdutos = document.getElementById('chartProdutos').getContext('2d');
new Chart(ctxProdutos, {
    type: 'doughnut',
    data: {
        labels: dataProdutos.labels,
        datasets: [
            {
                label: 'Negociações',
                data: dataProdutos.negociacoes,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Gráfico: Tendência (Line Chart)
var ctxTendencia = document.getElementById('chartTendencia').getContext('2d');
new Chart(ctxTendencia, {
    type: 'line',
    data: {
        labels: dataPorDia.labels,
        datasets: [
            {
                label: 'Valor Negociado (R$)',
                data: dataPorDia.valores,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Quantidade de Negociações',
                data: dataPorDia.negociacoes,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Valor (R$)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Quantidade'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});
</script>
