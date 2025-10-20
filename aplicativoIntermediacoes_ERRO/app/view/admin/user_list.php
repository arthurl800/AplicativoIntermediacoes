<?php 
// Verifica se $authManager foi passado, caso contrário, inicializa-o para evitar erros
if (!isset($authManager)) {
    require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
    $authManager = new AuthManager();
}
?>
<main class="container mx-auto p-4">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Gerenciamento de Usuários</h2>

    <!-- Botão de Cadastro de Novo Usuário -->
    <div class="mb-4">
        <a href="index.php?controller=admin&action=createUser" 
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-150 ease-in-out">
            Cadastrar Novo Usuário
        </a>
    </div>

    <?php if (isset($message) && $message): ?>
        <div class="p-4 mb-4 text-sm rounded-lg 
            <?php echo strpos($message, 'Erro') !== false ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>" 
             role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p class="text-gray-600">Nenhum usuário encontrado no sistema.</p>
    <?php else: ?>
        <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome de Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Usuário</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($u['cpf']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $u['role'] === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?= htmlspecialchars(ucfirst($u['role'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-center">
                                <?php 
                                    $isCurrentUser = ($u['id'] == ($authManager->getCurrentUser()['id'] ?? null));
                                ?>
                                
                                <?php if (!$isCurrentUser): ?>
                                    
                                    <!-- Botão/Link para Editar -->
                                    <a href="index.php?controller=admin&action=editUser&id=<?= $u['id'] ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-3 p-1 rounded hover:bg-indigo-50 transition">
                                        Editar
                                    </a>

                                    <!-- Formulário para deletar o usuário -->
                                    <form action="index.php?controller=admin&action=deleteUser" method="POST" class="inline-block"
                                          onsubmit="return confirm('ATENÇÃO: Tem certeza que deseja remover o usuário \'<?= htmlspecialchars($u['username']) ?>\'? Esta ação é irreversível.');">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition">
                                            Remover
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">(Conta Logada)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
