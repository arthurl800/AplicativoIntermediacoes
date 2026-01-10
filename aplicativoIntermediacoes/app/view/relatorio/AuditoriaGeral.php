<?php
// app/view/relatorio/AuditoriaGeral.php
?>
<main>
    <div class="page-header mb-4">
        <h1>Auditoria Geral do Sistema</h1>
        <p class="text-muted">Registro completo de todas as ações dos usuários</p>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Filtros</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="grid grid-3">
                <input type="hidden" name="controller" value="relatorio">
                <input type="hidden" name="action" value="auditoriaGeral">
                
                <div class="form-group">
                    <label for="modulo">Módulo</label>
                    <select id="modulo" name="modulo" class="form-control">
                        <option value="">Todos</option>
                        <option value="AUTENTICACAO" <?= ($filtros['modulo'] ?? '') === 'AUTENTICACAO' ? 'selected' : '' ?>>Autenticação</option>
                        <option value="USUARIOS" <?= ($filtros['modulo'] ?? '') === 'USUARIOS' ? 'selected' : '' ?>>Usuários</option>
                        <option value="UPLOAD" <?= ($filtros['modulo'] ?? '') === 'UPLOAD' ? 'selected' : '' ?>>Upload</option>
                        <option value="NEGOCIACOES" <?= ($filtros['modulo'] ?? '') === 'NEGOCIACOES' ? 'selected' : '' ?>>Negociações</option>
                        <option value="DADOS" <?= ($filtros['modulo'] ?? '') === 'DADOS' ? 'selected' : '' ?>>Dados</option>
                        <option value="DASHBOARD" <?= ($filtros['modulo'] ?? '') === 'DASHBOARD' ? 'selected' : '' ?>>Dashboard</option>
                        <option value="RELATORIO" <?= ($filtros['modulo'] ?? '') === 'RELATORIO' ? 'selected' : '' ?>>Relatório</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="acao">Ação</label>
                    <select id="acao" name="acao" class="form-control">
                        <option value="">Todas</option>
                        <option value="LOGIN_SUCESSO" <?= ($filtros['acao'] ?? '') === 'LOGIN_SUCESSO' ? 'selected' : '' ?>>Login Sucesso</option>
                        <option value="LOGIN_FALHA" <?= ($filtros['acao'] ?? '') === 'LOGIN_FALHA' ? 'selected' : '' ?>>Login Falha</option>
                        <option value="LOGOUT" <?= ($filtros['acao'] ?? '') === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                        <option value="CREATE" <?= ($filtros['acao'] ?? '') === 'CREATE' ? 'selected' : '' ?>>Criação</option>
                        <option value="UPDATE" <?= ($filtros['acao'] ?? '') === 'UPDATE' ? 'selected' : '' ?>>Atualização</option>
                        <option value="DELETE" <?= ($filtros['acao'] ?? '') === 'DELETE' ? 'selected' : '' ?>>Exclusão</option>
                        <option value="VIEW" <?= ($filtros['acao'] ?? '') === 'VIEW' ? 'selected' : '' ?>>Visualização</option>
                        <option value="UPLOAD" <?= ($filtros['acao'] ?? '') === 'UPLOAD' ? 'selected' : '' ?>>Upload</option>
                        <option value="NEGOCIACAO" <?= ($filtros['acao'] ?? '') === 'NEGOCIACAO' ? 'selected' : '' ?>>Negociação</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="data_inicio">Data Início</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" 
                           value="<?= htmlspecialchars($filtros['data_inicio'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="data_fim">Data Fim</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" 
                           value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <a href="index.php?controller=relatorio&action=auditoriaGeral" class="btn btn-secondary btn-block">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="grid grid-4 mb-4">
        <div class="card-kpi">
            <h3>Total de Registros</h3>
            <p class="value"><?= number_format($totalLogs, 0, ',', '.') ?></p>
        </div>
        <div class="card-kpi">
            <h3>Página Atual</h3>
            <p class="value"><?= $page ?> de <?= $totalPaginas ?></p>
        </div>
        <div class="card-kpi">
            <h3>Registros/Página</h3>
            <p class="value"><?= count($logs) ?></p>
        </div>
        <div class="card-kpi">
            <h3>Total de Páginas</h3>
            <p class="value"><?= $totalPaginas ?></p>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <?php if (count($logs) > 0): ?>
        <div class="table-wrapper card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Módulo</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($log['id']) ?></strong></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['data_acao'])) ?></td>
                            <td>
                                <?= htmlspecialchars($log['usuario_nome'] ?? 'Sistema') ?>
                                <small class="text-muted">(ID: <?= $log['usuario_id'] ?? 'N/A' ?>)</small>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($log['modulo']) ?></span></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-info';
                                if (strpos($log['acao'], 'FALHA') !== false) $badgeClass = 'badge-danger';
                                elseif (strpos($log['acao'], 'DELETE') !== false) $badgeClass = 'badge-delete';
                                elseif (strpos($log['acao'], 'CREATE') !== false) $badgeClass = 'badge-insert';
                                elseif (strpos($log['acao'], 'UPDATE') !== false) $badgeClass = 'badge-update';
                                elseif (strpos($log['acao'], 'LOGIN_SUCESSO') !== false) $badgeClass = 'badge-success';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($log['acao']) ?></span>
                            </td>
                            <td class="text-left">
                                <?= htmlspecialchars(strlen($log['descricao']) > 60 ? substr($log['descricao'], 0, 60) . '...' : $log['descricao']) ?>
                            </td>
                            <td><small><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></small></td>
                            <td>
                                <button class="btn btn-small btn-primary" onclick="verDetalhes(<?= $log['id'] ?>, '<?= addslashes($log['descricao']) ?>', <?= htmlspecialchars(json_encode($log['dados_antes'])) ?>, <?= htmlspecialchars(json_encode($log['dados_depois'])) ?>)">
                                    Ver
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div class="pagination mt-4">
                <?php if ($page > 1): ?>
                    <a href="?controller=relatorio&action=auditoriaGeral&page=<?= $page - 1 ?><?= !empty($filtros) ? '&' . http_build_query($filtros) : '' ?>">← Anterior</a>
                <?php endif; ?>
                
                <span>Página <?= $page ?> de <?= $totalPaginas ?></span>
                
                <?php if ($page < $totalPaginas): ?>
                    <a href="?controller=relatorio&action=auditoriaGeral&page=<?= $page + 1 ?><?= !empty($filtros) ? '&' . http_build_query($filtros) : '' ?>">Próximo →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <h3>Nenhum registro de auditoria encontrado</h3>
            <p>Não há logs com os filtros aplicados.</p>
        </div>
    <?php endif; ?>
</main>

<!-- Modal para detalhes -->
<div id="modalDetalhes" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <h2>Detalhes do Log de Auditoria</h2>
        <div id="modalContent" style="max-height: 500px; overflow-y: auto;"></div>
        <div class="form-actions mt-3">
            <button class="btn btn-secondary" onclick="fecharModal()">Fechar</button>
        </div>
    </div>
</div>

<script>
function verDetalhes(id, descricao, dadosAntes, dadosDepois) {
    let html = '<div class="card mb-3"><div class="card-body">';
    html += '<p><strong>ID:</strong> ' + id + '</p>';
    html += '<p><strong>Descrição Completa:</strong></p>';
    html += '<p>' + descricao + '</p>';
    
    if (dadosAntes && Object.keys(dadosAntes).length > 0) {
        html += '<h3>Dados Antes:</h3>';
        html += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto;">' + JSON.stringify(dadosAntes, null, 2) + '</pre>';
    }
    
    if (dadosDepois && Object.keys(dadosDepois).length > 0) {
        html += '<h3>Dados Depois:</h3>';
        html += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto;">' + JSON.stringify(dadosDepois, null, 2) + '</pre>';
    }
    
    html += '</div></div>';
    
    document.getElementById('modalContent').innerHTML = html;
    document.getElementById('modalDetalhes').classList.add('active');
}

function fecharModal() {
    document.getElementById('modalDetalhes').classList.remove('active');
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('modalDetalhes');
    if (event.target == modal) {
        fecharModal();
    }
}
</script>

<style>
.table {
    font-size: 0.9rem;
}

.table td {
    vertical-align: middle;
}

.table small {
    display: block;
    font-size: 0.75rem;
}

pre {
    font-size: 0.85rem;
    line-height: 1.4;
}
</style>
