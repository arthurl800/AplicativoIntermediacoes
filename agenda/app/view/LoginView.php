<?php

class LoginView
{
    public function render($errorMessage = null)
    {
        echo '<div class="container">';
        echo '<h2>Login</h2>';

        // Exibe mensagem de erro se houver
        if ($errorMessage) {
            echo '<div class="alert alert-danger">' . $errorMessage . '</div>';
        }

        // Formulário de login
        echo '<form action="?route=login&action=login" method="POST">';
        echo '<div class="form-group">';
        echo '<label for="email">Email:</label>';
        echo '<input type="email" name="email" id="email" class="form-control" required>';
        echo '</div>';

        echo '<div class="form-group">';
        echo '<label for="senha">Senha:</label>';
        echo '<input type="password" name="senha" id="senha" class="form-control" required>';
        echo '</div>';
        echo '<br>';
        echo '<button type="submit" class="btn btn-primary">Entrar</button>';
        echo '</form>';
        echo '</div>';
    }
}
