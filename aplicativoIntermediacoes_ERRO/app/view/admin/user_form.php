<main class="container mx-auto p-4">
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
        
        <h2 class="text-3xl font-extrabold mb-6 text-gray-900 border-b pb-3">
            <?= htmlspecialchars($title) ?>
        </h2>

        <!-- Mensagens de Erro/Sucesso -->
        <?php if (isset($message) && $message): ?>
            <div class="p-4 mb-6 text-sm rounded-lg 
                <?php echo strpos($message, 'Erro') !== false ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>" 
                 role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php 
            // Determina se é criação ou edição
            $isEditing = $user['id'] !== null;
            
            // Determina a ação do formulário
            $action = $isEditing 
                ? "index.php?controller=admin&action=processEditUser" 
                : "index.php?controller=admin&action=processCreateUser";
        ?>

        <form action="<?= $action ?>" method="POST">
            
            <!-- Campo ID (Apenas para Edição) -->
            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            <?php endif; ?>

            <!-- Campo Nome de Usuário -->
            <div class="mb-5">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Nome de Usuário</label>
                <input type="text" id="username" name="username" required
                       value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150">
            </div>

            <!-- Campo CPF -->
            <div class="mb-5">
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">CPF (Somente dígitos)</label>
                <input type="text" id="cpf" name="cpf" required minlength="11" maxlength="11" pattern="\d{11}"
                       value="<?= htmlspecialchars($user['cpf'] ?? '') ?>"
                       placeholder="Ex: 12345678901"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                <p class="mt-1 text-xs text-gray-500">O CPF deve ter exatamente 11 dígitos.</p>
            </div>

            <!-- Campo Nível de Acesso (Role) -->
            <div class="mb-5">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Usuário</label>
                <select id="role" name="role" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white transition duration-150">
                    <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>Usuário Comum</option>
                    <option value="admin" <?= ($user['role'] ?? 'user') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <!-- Campo Senha -->
            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha 
                    <?php if (!$isEditing): ?>
                        <span class="text-red-500">* (Obrigatório)</span>
                    <?php else: ?>
                        <span class="text-gray-500">(Deixe vazio para manter a senha atual)</span>
                    <?php endif; ?>
                </label>
                <input type="password" id="password" name="password" 
                       <?= $isEditing ? '' : 'required' ?>
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150">
            </div>
            
            <!-- Campo Confirmação de Senha (Apenas para Criação) -->
            <?php if (!$isEditing): ?>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha <span class="text-red-500">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition duration-150">
                </div>
            <?php endif; ?>
            
            <div class="flex justify-between items-center mt-6 pt-4 border-t">
                <!-- Botão de Ação (Criar ou Salvar) -->
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-150 ease-in-out">
                    <?= $isEditing ? 'Salvar Alterações' : 'Cadastrar Usuário' ?>
                </button>
                
                <!-- Botão de Voltar -->
                <a href="index.php?controller=admin&action=users"
                   class="text-gray-600 hover:text-gray-900 font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out border border-gray-300 hover:bg-gray-50">
                    Cancelar e Voltar
                </a>
            </div>

        </form>
    </div>
</main>
