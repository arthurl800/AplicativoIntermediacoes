<?php
// app/view/relatorio/auditoria.php
?>

<div class="auditoria-container">
    <h1>Histórico de Auditoria</h1>
    
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Auditoria</th>
                    <th>ID Negociação</th>
                    <th>Ação</th>
                    <th>Usuário</th>
                    <th>Data/Hora</th>
                    <th>Descrição da Mudança</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($auditoria)): ?>
                    <tr>
                        <td colspan="7" class="text-center p-4">Nenhum registro de auditoria encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($auditoria as $log): ?>
                        <tr>
                            <td data-label="ID Auditoria"><?php echo $log['id']; ?></td>
                            <td data-label="ID Negociação"><?php echo $log['negociacao_id']; ?></td>
                            <td data-label="Ação">
                                <span class="badge badge-<?php echo strtolower($log['acao']); ?>">
                                    <?php echo ucfirst($log['acao']); ?>
                                </span>
                            </td>
                            <td data-label="Usuário"><?php echo htmlspecialchars($log['usuario_name'] ?? 'Sistema'); ?></td>
                            <td data-label="Data/Hora"><?php echo date('d/m/Y H:i:s', strtotime($log['data_acao'])); ?></td>
                            <td data-label="Descrição"><?php echo htmlspecialchars($log['descricao_mudanca'] ?? '-'); ?></td>
                            <td data-label="Ações">
                                <a href="index.php?controller=relatorio&action=detalhesAuditoria&id=<?php echo $log['id']; ?>" class="action-btn btn-primary">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="index.php?controller=relatorio&action=auditoria&page=<?php echo $page - 1; ?>" class="btn btn-secondary">← Anterior</a>
        <?php endif; ?>
        
        <span class="pagination-info">Página <?php echo $page; ?></span>
        
        <?php if (count($auditoria) >= 50): ?>
            <a href="index.php?controller=relatorio&action=auditoria&page=<?php echo $page + 1; ?>" class="btn btn-secondary">Próxima →</a>
        <?php endif; ?>
    </div>

    <div class="back-link">
        <a href="index.php?controller=negociacao&action=index">← Voltar as Negociações</a>
    </div>
</div>
