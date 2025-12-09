<?php
// test_dashboard.php - Script para testar o dashboard
// Acesse: http://localhost:8000/test_dashboard.php

require_once __DIR__ . '/app/util/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Busca dados da view de resumo executivo
    $stmt = $pdo->query("SELECT * FROM VW_RESUMO_EXECUTIVO_NEGOCIACOES LIMIT 1");
    $resumo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca dados da view por operador
    $stmt = $pdo->query("SELECT * FROM VW_NEGOCIACOES_POR_OPERADOR LIMIT 5");
    $operadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca dados da view por produto
    $stmt = $pdo->query("SELECT * FROM VW_NEGOCIACOES_POR_PRODUTO LIMIT 5");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Busca dados da view por data
    $stmt = $pdo->query("SELECT * FROM VW_NEGOCIACOES_POR_DATA LIMIT 30");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<h1>✅ Dashboard Data Test</h1>';
    
    echo '<h2>Resumo Executivo</h2>';
    echo '<pre>';
    print_r($resumo);
    echo '</pre>';
    
    echo '<h2>Negociações por Operador</h2>';
    echo '<pre>';
    print_r($operadores);
    echo '</pre>';
    
    echo '<h2>Negociações por Produto</h2>';
    echo '<pre>';
    print_r($produtos);
    echo '</pre>';
    
    echo '<h2>Negociações por Data</h2>';
    echo '<pre>';
    print_r($datas);
    echo '</pre>';
    
    echo '<hr>';
    echo '<p><a href="index.php">← Voltar ao Sistema</a></p>';
    
} catch (Exception $e) {
    echo '❌ Erro: ' . htmlspecialchars($e->getMessage());
    echo '<pre>';
    echo $e->getTraceAsString();
    echo '</pre>';
}
?>
