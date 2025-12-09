<!-- app/view/dashboard/index.php -->
<main>
    <h2>Bem-vindo, <?= htmlspecialchars($username) ?>!</h2>
    <p>Seu nível de acesso é: <strong><?= htmlspecialchars($role) ?></strong>.</p>
    
    <p>A partir daqui, você pode iniciar a manipulação dos dados importados.</p>

    <?php if ($is_admin): ?>
        <div class="message info mt-4">
            <h3 class="form-section-title">Área Administrativa</h3>
            <p>Como administrador, você tem acesso às seguintes funções:</p>
            <ul>
                <li><a href="index.php?controller=admin&action=users" class="btn-link">Gerenciar Usuários</a></li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="message success mt-4">
        <h3 class="form-section-title">Funções de Análise de Dados</h3>
        <p>Gerenciar os arquivos importados</p>
        <ul>
            <li><a href="index.php?controller=upload&action=index">Importar e Visualizar os Dados</a></li>
            <li><a href="index.php?controller=dados&action=visualizar&only_negotiations=1#negociacoes">Negociações</a></li>
        </ul>
    </div>
</main>
