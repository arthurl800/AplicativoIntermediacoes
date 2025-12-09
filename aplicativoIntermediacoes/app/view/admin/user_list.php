<!-- app/view/admin/user_list.php -->
<main>
    <h2>Gerenciamento de Usuários</h2>

    <?php if (isset($message) && $message): ?>
        <div class="message success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p class="message info">Nenhum usuário encontrado no sistema.</p>
    <?php else: ?>
        <div style="margin-bottom: 12px;">
            <a href="index.php?controller=admin&action=addUser" class="btn btn-primary">Adicionar Usuário</a>
        </div>
        <div class="table-wrapper">
        <table class="users-table data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome de Usuário</th>
                    <th>CPF</th>
                    <th>Tipo de Usuario</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($u['id']) ?></td>
                        <td data-label="Nome de Usuário"><?= htmlspecialchars($u['username']) ?></td>
                        <td data-label="CPF"><?= htmlspecialchars($u['cpf']) ?></td>
                        <td data-label="Tipo"><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <?php 
                                $isCurrentUser = ($u['id'] == ($authManager->getCurrentUser()['id'] ?? null));
                            ?>
                            
                            <?php if (!$isCurrentUser): ?>
                                <!-- Formulário para deletar o usuário -->
                                <form action="index.php?controller=admin&action=deleteUser" method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>"> 
                                    <button type="submit" class="action-btn btn-delete"
                                            onclick="return confirm('Tem certeza que deseja remover o usuário \'<?= htmlspecialchars($u['username']) ?>\'?');">
                                        Remover
                                    </button>
                                </form>
                                <!-- Botão de editar que abre o formulário de edição -->
                                <a href="index.php?controller=admin&action=editUser&id=<?= $u['id'] ?>" class="action-btn btn-edit">Editar</a>
                            <?php else: ?>
                                <span style="color: #007bff; font-weight: bold;">(Conta Logada)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</main>
