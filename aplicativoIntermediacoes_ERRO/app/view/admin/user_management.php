<!-- app/view/admin/user_management.php -->
<main>
    <h2>Administração de Usuários</h2>
    <p>Crie e gerencie os usuários que terão acesso ao sistema de intermediações.</p>

    <?php if (isset($success)): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; background-color: #e6ffe6; border-radius: 4px;">
            Sucesso: <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; background-color: #ffe6e6; border-radius: 4px;">
            Erro: <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="border: 1px solid #ccc; padding: 20px; border-radius: 8px; margin-top: 20px;">
        <h3>Criar Novo Usuário</h3>
        <form action="index.php?controller=admin&action=createUser" method="POST" style="display: grid; gap: 15px; max-width: 500px;">
            
            <div>
                <label for="username" style="display: block; margin-bottom: 5px;">Nome de Usuário:</label>
                <input type="text" id="username" name="username" required 
                       style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div>
                <label for="password" style="display: block; margin-bottom: 5px;">Senha:</label>
                <input type="password" id="password" name="password" required 
                       style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div>
                <label for="cpf" style="display: block; margin-bottom: 5px;">CPF (Apenas números ou formato completo):</label>
                <input type="text" id="cpf" name="cpf" required minlength="11" maxlength="14" 
                       placeholder="Ex: 123.456.789-00"
                       style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div>
                <label for="role" style="display: block; margin-bottom: 5px;">Nível de Acesso:</label>
                <select id="role" name="role" 
                        style="width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="user">Usuário Comum (Monetização)</option>
                    <option value="admin">Administrador (Sem Limitações)</option>
                </select>
            </div>
            
            <button type="submit" 
                    style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                Criar Usuário
            </button>
        </form>
    </div>

    <!-- Aqui entraria a listagem de usuários existentes -->
    <div style="margin-top: 40px;">
        <h3>Usuários Ativos (Listagem)</h3>
        <p>A listagem e edição de usuários será implementada na próxima etapa.</p>
    </div>
</main>
