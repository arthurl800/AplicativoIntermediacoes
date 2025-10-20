<?php

include_once 'app/model/Usuario.php';
include_once 'app/view/LoginView.php';

class LoginController
{
    public function handleRequest($action, $params)
    {
        switch ($action) {
            case 'login':
                $this->login($params);
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                // Exibe a página de login por padrão
                $this->showLoginForm();
        }
    }

    public function login($params)
    {
        $email = $params['email'] ?? null;
        $senha = $params['senha'] ?? null;


        if ($email && $senha) {

            // Chama a autenticação da classe Usuario
            $user = Usuario::authenticate($email, $senha);
            
            if ($user) {

                // Armazena os dados do usuário na sessão
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_name'] = $user->getNome();

                // Redireciona para a página inicial ou outra protegida
                header('Location: /agenda');
                exit();
            } else {
                // Exibe o formulário de login com mensagem de erro
                $this->showLoginForm("Credenciais inválidas. Tente novamente.");
            }
        } else {
            // Se email ou senha estiverem vazios, exibe o formulário de login
            $this->showLoginForm("Por favor, preencha todos os campos.");
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: index.php');
        exit();
    }

    public function showLoginForm($errorMessage = null)
    {
        // Instancia a classe LoginView e chama o método render para exibir o formulário
        $loginView = new LoginView();
        $loginView->render($errorMessage);
    }
}