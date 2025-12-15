<?php
// /config/Config.php

class Config {
    /**
     * Obtém um valor de configuração das variáveis de ambiente.
     *
     * @param string $key A chave da variável de ambiente.
     * @param mixed $default O valor padrão a ser retornado se a variável não estiver definida.
     * @return mixed O valor da configuração.
     */
    public static function get(string $key, $default = null) {
        return $_ENV[$key] ?? $default;
    }

    // --- Configurações do Banco de Dados ---
    public static function dbHost() { return self::get('DB_HOST', 'db'); }
    public static function dbName() { return self::get('DB_DATABASE', 'app_data'); }
    public static function dbUser() { return self::get('DB_USERNAME', 'root'); }
    public static function dbPassword() { return self::get('DB_PASSWORD', 'secret'); }

    // --- Nomes das Tabelas ---
    // Usamos variáveis de ambiente para os nomes das tabelas para flexibilidade.
    // Se não definidas, usamos valores padrão.
    public static function tableNameIntermediacoes() { 
        return self::get('TABLE_INTERMEDIACOES', 'INTERMEDIACOES'); 
    }
    public static function tableNameIntermediacoesNegociada() { 
        return self::get('TABLE_INTERMEDIACOES_NEGOCIADA', 'INTERMEDIACOES_TABLE_NEGOCIADA'); 
    }
    public static function tableNameUsers() { return self::get('TABLE_USERS', 'USUARIOS'); }
    public static function tableNameNegociacoes() { return self::get('TABLE_NEGOCIACOES', 'NEGOCIACOES'); }
}