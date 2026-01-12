<?php
// app/dashboard/painel.php
?>
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-xl);
        margin-top: var(--space-2xl);
    }
    
    .dashboard-card {
        background: white;
        padding: var(--space-xl);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        transition: all var(--transition-base);
        border-left: 4px solid transparent;
    }
    
    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }
    
    .dashboard-card.primary { border-left-color: #667eea; }
    .dashboard-card.success { border-left-color: #43e97b; }
    .dashboard-card.info { border-left-color: #4facfe; }
    .dashboard-card.warning { border-left-color: #fa709a; }
    
    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: var(--space-lg);
        color: white;
    }
    
    .card-icon.primary { background: var(--primary-gradient); }
    .card-icon.success { background: var(--success-gradient); }
    .card-icon.info { background: var(--accent-gradient); }
    .card-icon.warning { background: var(--warning-gradient); }
    
    .card-links {
        display: flex;
        flex-direction: column;
        gap: var(--space-sm);
        margin-top: var(--space-md);
    }
    
    .card-link {
        padding: var(--space-sm) var(--space-md);
        background: var(--gray-100);
        border-radius: var(--radius-md);
        text-decoration: none;
        color: var(--text-primary);
        font-weight: 500;
        transition: all var(--transition-base);
    }
    
    .card-link:hover {
        background: var(--primary-gradient);
        color: white;
        transform: translateX(4px);
    }
    
    .welcome-section {
        background: white;
        padding: var(--space-2xl);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--space-2xl);
        text-align: center;
    }
    
    .welcome-badge {
        display: inline-block;
        padding: var(--space-sm) var(--space-lg);
        background: var(--primary-gradient);
        color: white;
        border-radius: var(--radius-full);
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: var(--space-md);
    }
</style>

<div class="welcome-section slide-up">
    <h1>Bem-vindo, <?= htmlspecialchars($username) ?>!</h1>
    <p>Pronto para gerenciar suas intermediações financeiras</p>
    <span class="welcome-badge"><?= htmlspecialchars($role) ?></span>
</div>

<div class="dashboard-grid">
    <?php if ($is_admin): ?>
        <div class="dashboard-card warning slide-up">
            <div class="card-icon warning">ADM</div>
            <h3>Área Administrativa</h3>
            <p>Gerencie usuários e permissões do sistema</p>
            <div class="card-links">
                <a href="index.php?controller=admin&action=userList" class="card-link">Gerenciar Usuários</a>
                <a href="index.php?controller=relatorio&action=auditoria" class="card-link">Auditoria Completa</a>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="dashboard-card primary slide-up">
        <div class="card-icon primary">01</div>
        <h3>Importar Dados</h3>
        <p>Upload e processamento de arquivos Excel</p>
        <div class="card-links">
            <a href="index.php?controller=upload&action=index" class="card-link">Nova Importação</a>
            <a href="index.php?controller=data&action=viewData" class="card-link">Ver Dados Importados</a>
        </div>
    </div>
    
    <div class="dashboard-card success slide-up">
        <div class="card-icon success">02</div>
        <h3>Negociações</h3>
        <p>Gerencie intermediações pendentes e efetivadas</p>
        <div class="card-links">
            <a href="index.php?controller=negociacao&action=index" class="card-link">Painel de Negociações</a>
            <a href="index.php?controller=data&action=viewNegociadas" class="card-link">Intermediações Efetivadas</a>
        </div>
    </div>
    
    <div class="dashboard-card info slide-up">
        <div class="card-icon info">03</div>
        <h3>Relatórios</h3>
        <p>Visualize estatísticas e histórico de operações</p>
        <div class="card-links">
            <a href="index.php?controller=relatorio&action=auditoria" class="card-link">Relatório de Auditoria</a>
        </div>
    </div>
</div>
