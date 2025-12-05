<!-- app/view/admin/UserList.php -->
<?php
// Copiado de user_list.php para PascalCase refatorado
?>
<main>
    <h2>Gerenciamento de Usuários</h2>

    <?php if (isset($message) && $message): ?>
        <div style="padding: 10px; margin-bottom: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p>Nenhum usuário encontrado no sistema.</p>
    <?php else: ?>
        <table class="data-table">
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
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['cpf']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
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
                                <!-- Em um sistema completo, haveria um botão para editar -->
                                <button class="action-btn btn-edit" disabled>Editar</button>
                            <?php else: ?>
                                <span style="color: #007bff; font-weight: bold;">(Conta Logada)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
