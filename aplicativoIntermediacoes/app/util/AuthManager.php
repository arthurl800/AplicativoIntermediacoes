<?php
// app/util/AuthManager.php

class AuthManager {

    public function __construct() {
        // Garante que a sessão esteja iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Realiza o login do usuário.
     * Salva o ID, username e role na sessão.
     * @param array $user Dados do usuário
     */
    public function login(array $user): void {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
    }

    /**
     * Realiza o logout do usuário.
     */
    public function logout(): void {
        session_unset();
        session_destroy();
    }

    /**
     * Verifica se o usuário está logado.
     * @return bool
     */
    public function isLoggedIn(): bool {
        return (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true);
    }

    /**
     * Verifica se o usuário logado é administrador.
     * @return bool
     */
    public function isAdmin(): bool {
        return $this->isLoggedIn() && ($_SESSION['role'] ?? 'user') === 'admin';
    }

    /**
     * Verifica se o usuário tem uma role específica.
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool {
        return $this->isLoggedIn() && ($_SESSION['role'] ?? 'user') === $role;
    }

    /**
     * Retorna os dados básicos do usuário logado (ID, username e role).
     * Esta função é usada no header para exibir informações e links.
     * @return array|null
     */
    public function getCurrentUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? 'Guest',
            'role' => $_SESSION['role'] ?? 'user',
        ];
    }

    /**
     * Redireciona para uma URL.
     * @param string $url
     */
    public static function redirectTo(string $url): void {
        header("Location: {$url}");
        exit();
    }
}
