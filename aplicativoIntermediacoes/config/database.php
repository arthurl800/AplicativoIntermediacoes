<?php
// config/database.php

// Carrega variáveis de ambiente do arquivo .env (se existir)
// Retorna array com as variáveis parseadas
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        $env = [];
        
        if (!file_exists($path)) {
            return $env;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignora comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse linha no formato KEY=VALUE
            if (strpos($line, '=') === false) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Armazena no array
            $env[$name] = $value;
        }
        
        return $env;
    }
}

// Carrega .env do diretório raiz (somente se ainda não foi carregado)
if (!isset($GLOBALS['envVars'])) {
    $GLOBALS['envVars'] = loadEnv(__DIR__ . '/../.env');
}

// Função helper para pegar variáveis de ambiente
if (!function_exists('env')) {
    function env($key, $default = null) {
        // Usa $GLOBALS ao invés de global para funcionar em qualquer escopo
        $envVars = $GLOBALS['envVars'] ?? [];
        
        // Tenta pegar do array parseado primeiro
        if (isset($envVars[$key])) {
            return $envVars[$key];
        }
        
        // Fallback para getenv (caso esteja disponível)
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Fallback para $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        return $default;
    }
}

// Configurações do banco de dados
return [
    'DB_HOST'     => env('DB_HOST', 'localhost'),
    'DB_NAME'     => env('DB_NAME', 'INTERMEDIACOES'),
    'DB_USER'     => env('DB_USER', 'INTERMEDIACOES_USER'),
    'DB_PASS'     => env('DB_PASS', ''),
    'DB_CHARSET'  => env('DB_CHARSET', 'utf8mb4'),
    'TABLE_NAME'  => env('TABLE_NAME', 'INTERMEDIACOES_TABLE'),
    'USER_TABLE'  => env('USER_TABLE', 'USUARIOS_TABLE')
];
