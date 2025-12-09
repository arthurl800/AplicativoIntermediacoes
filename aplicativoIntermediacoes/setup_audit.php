<?php
// setup_audit.php - Execute este script uma vez para criar as tabelas de auditoria e triggers
// Acesse via navegador: http://localhost:8000/setup_audit.php

require_once __DIR__ . '/app/util/Database.php';

// Lê o arquivo SQL de triggers
$sqlFile = __DIR__ . '/database/triggers_auditoria.sql';

if (!file_exists($sqlFile)) {
    die('❌ Arquivo SQL não encontrado: ' . $sqlFile);
}

$sqlContent = file_get_contents($sqlFile);

if (!$sqlContent) {
    die('❌ Não foi possível ler o arquivo SQL.');
}

// Conecta ao banco de dados
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Executa cada comando SQL separadamente (por conta dos DELIMITERS)
    $statements = array_filter(
        array_map('trim', preg_split('/;/', $sqlContent)),
        function($s) { return !empty($s) && !preg_match('/^--/', trim($s)); }
    );
    
    $executed = 0;
    $errors = array();
    
    foreach ($statements as $statement) {
        // Remove comentários e DELIMITER
        $statement = preg_replace('/^DELIMITER\s+\/\/\s*/i', '', $statement);
        $statement = preg_replace('/^DELIMITER\s+;\s*/i', '', $statement);
        $statement = trim($statement);
        
        if (empty($statement)) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Alguns erros são esperados (ex: tabela já existe)
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    echo '<h2>✅ Setup de Auditoria Concluído</h2>';
    echo '<p>Comandos SQL executados: ' . $executed . '</p>';
    
    if (!empty($errors)) {
        echo '<h3>⚠️ Avisos/Erros (alguns podem ser esperados):</h3>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
    }
    
    // Verifica se a tabela foi criada
    $checkTable = $pdo->query("SHOW TABLES LIKE 'NEGOCIACOES_AUDITORIA'");
    if ($checkTable->rowCount() > 0) {
        echo '<p style="color: green; font-weight: bold;">✓ Tabela NEGOCIACOES_AUDITORIA criada com sucesso!</p>';
    }
    
    // Verifica se as views foram criadas
    $checkView = $pdo->query("SHOW TABLES LIKE 'VW_RESUMO_EXECUTIVO_NEGOCIACOES'");
    if ($checkView->rowCount() > 0) {
        echo '<p style="color: green; font-weight: bold;">✓ Views analíticas criadas com sucesso!</p>';
    }
    
    echo '<hr>';
    echo '<p><a href="index.php">← Voltar ao Sistema</a></p>';
    
} catch (Exception $e) {
    echo '❌ Erro ao conectar ao banco de dados: ' . htmlspecialchars($e->getMessage());
}
?>
