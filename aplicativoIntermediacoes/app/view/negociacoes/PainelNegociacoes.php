<?php
// app/view/negociacoes/PainelNegociacoes.php
?>
<main>
    <div class="page-header mb-4">
        <h1> Painel de Negociações</h1>
        <p class="text-muted">Gerenciamento das intermediações disponíveis</p>
    </div>

    <!-- Mensagem de Sucesso -->
    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert alert-success mb-4">
            <?= htmlspecialchars($_SESSION['mensagem_sucesso']) ?>
        </div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>

    <!-- Filtros (Opcional) -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="controller" value="negociacao">
                <input type="hidden" name="action" value="painel">
                
                <div class="form-group flex-1">
                    <label for="filtro_cliente">Cliente</label>
                    <input type="text" id="filtro_cliente" name="cliente" placeholder="Filtrar por cliente..." 
                           class="form-control">
                </div>
                
                <div class="form-group flex-1">
                    <label for="filtro_produto">Produto</label>
                    <input type="text" id="filtro_produto" name="produto" placeholder="Filtrar por produto..." 
                           class="form-control">
                </div>
                
                <div class="form-group flex-1">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block"> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Negociações -->
    <?php if (count($negociacoes) > 0): ?>
        <div class="table-wrapper card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Conta</th>
                        <th>Cliente</th>
                        <th>Produto</th>
                        <th>Estratégia</th>
                        <th>Qtd.</th>
                        <th>Vl. Bruto</th>
                        <th>Vl. Líquido</th>
                        <th>Vencimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($negociacoes as $neg): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($neg['conta'] ?? '---') ?></strong></td>
                            <td><?= htmlspecialchars($neg['cliente'] ?? '---') ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?= htmlspecialchars($neg['produto'] ?? '---') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($neg['estrategia'] ?? '---') ?></td>
                            <td class="text-right">
                                <strong><?= htmlspecialchars($neg['quantidade'] ?? '0') ?></strong>
                            </td>
                            <td class="text-right">
                                <?= htmlspecialchars($neg['valor_bruto'] ?? '---') ?>
                            </td>
                            <td class="text-right">
                                <strong><?= htmlspecialchars($neg['valor_liquido'] ?? '---') ?></strong>
                            </td>
                            <td><?= htmlspecialchars($neg['vencimento'] ?? '---') ?></td>
                            <td>
                                <a href="index.php?controller=negociacao&action=formulario&id=<?= $neg['id'] ?>" 
                                   class="btn btn-small btn-primary">
                                     Negociar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
    <div style="background-color: #fffde7; 
                border: 2px solid #f9a825; 
                border-radius: 8px; 
                padding: 20px; 
                text-align: center;">
        <h2> Nenhuma Negociação Disponível</h2>
        <p>Não há intermediações disponíveis no momento. Importe dados para começar.</p>
        <a href="index.php?controller=upload&action=index" class="btn btn-primary mt-3">
            Importar Dados
        </a>
    </div>
    <?php endif; ?>
</main>
