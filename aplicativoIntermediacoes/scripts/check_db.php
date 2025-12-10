<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/util/Database.php';

$db = Database::getInstance()->getConnection();
try {
    $stmt = $db->query("SELECT id, Quantidade FROM INTERMEDIACOES_TABLE WHERE id = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "INTERMEDIACOES_TABLE row for id=1:\n";
    var_export($row);
    echo "\n";

    $stmt2 = $db->query("SELECT COUNT(*) as c FROM NEGOCIACOES");
    $c = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "NEGOCIACOES count: " . ($c['c'] ?? 'unknown') . "\n";
} catch (Exception $e) {
    echo "Error querying DB: " . $e->getMessage() . "\n";
}
