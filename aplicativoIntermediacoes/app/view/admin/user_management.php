<!-- app/view/admin/user_management.php -->
<main>
    <h2>Administração de Usuários</h2>
    <p>Crie e gerencie os usuários que terão acesso ao sistema de intermediações.</p>

    <?php if (isset($success)): ?>
        <div class="message success">
            Sucesso: <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="message error">
            Erro: <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h3 class="form-section-title">Criar Novo Usuário</h3>
        <form action="index.php?controller=admin&action=createUser" method="POST">
            
            <div class="form-group">
                <label for="username">Nome de Usuário:</label>
                <input type="text" id="username" name="username" required class="input-field">
            </div>
            
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required class="input-field">
            </div>

            <div class="form-group">
                <label for="cpf">CPF (Apenas números ou formato completo):</label>
                <input type="text" id="cpf" name="cpf" required minlength="11" maxlength="14" 
                       placeholder="Ex: 123.456.789-00" class="input-field">
            </div>
            
            <div class="form-group">
                <label for="role">Nível de Acesso:</label>
                <select id="role" name="role" class="input-field">
                    <option value="user">Usuário Comum (Monetização)</option>
                    <option value="admin">Administrador (Sem Limitações)</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Criar Usuário
            </button>
        </form>
    </div>

    <!-- Aqui entraria a listagem de usuários existentes -->
    <div class="mt-4">
        <h3 class="form-section-title">Usuários Ativos (Listagem)</h3>
        <p>A listagem e edição de usuários será implementada na próxima etapa.</p>
    </div>
</main>
