<?php
// app/dashboard/painel.php
?>
<main>
    <h2>Bem-vindo, <?= htmlspecialchars($username) ?>!</h2>
    <p>Seu nível de acesso é: <strong><?= htmlspecialchars($role) ?></strong>.</p>
    
    <p>A partir daqui, você pode iniciar a manipulação dos dados importados.</p>

    <?php if ($is_admin): ?>
        <div class="message info mt-4" 
            style="background-color: #e0f7fa; border: 2px solid #004d40; border-radius: 8px; padding: 15px;">
            <h3 class="form-section-title">Área Administrativa</h3>
            <p>Como administrador, você tem acesso às seguintes funções:</p>
            <ul>
                <br>
                <a href="index.php?controller=admin&action=users" class="btn-link">Gerenciar Usuários</a>
                </br>
            </ul>
        </div>
    <?php endif; ?>

    <div class="message success mt-4" 
        style="background-color: #fffde7; border: 2px solid #f9a825; border-radius: 8px; padding: 15px;">
        <h3 class="form-section-title">Funções de Análise de Dados</h3>
        <p>Gerenciar os arquivos importados</p>
        <ul>
            <br>
            <a href="index.php?controller=upload&action=index">Importar e Visualizar os Dados</a>
            </br>
        </ul>
        <ul>
            <br>
            <a href="index.php?controller=dados&action=visualizar&only_negotiations=1#negociacoes">Negociações</a>
            </br>
        </ul> 
    </div>
</main>
