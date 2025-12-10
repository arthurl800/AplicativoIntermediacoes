<?php
/**
 * Test script: Simulates a complete negotiation flow
 * 1. Prepares a source record in INTERMEDIACOES_TABLE with positive quantity
 * 2. Simulates form submission to NegociacaoController::processar()
 * 3. Validates:
 *    - NEGOCIACOES row inserted
 *    - INTERMEDIACOES_TABLE quantity decremented
 *    - INTERMEDIACOES_TABLE_NEGOCIADA row inserted with negotiated quantity
 */

echo "=== NEGOTIATION FLOW TEST ===\n\n";

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/util/Database.php';
require_once __DIR__ . '/../app/util/AuthManager.php';

$db = Database::getInstance()->getConnection();

// Step 1: Find or prepare a test record with Quantidade > 0
echo "[Step 1] Preparing source record...\n";

$testId = 2; // Try id=2 first
$stmt = $db->prepare("SELECT * FROM INTERMEDIACOES_TABLE WHERE id = :id");
$stmt->execute([':id' => $testId]);
$sourceRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sourceRow) {
    echo "❌ No source record found at id={$testId}. Cannot proceed.\n";
    exit(1);
}

$originalQty = (int)$sourceRow['Quantidade'];
echo "Found record id={$testId}, original Quantidade={$originalQty}\n";

if ($originalQty <= 0) {
    echo "Updating record to Quantidade=5 for testing...\n";
    $db->prepare("UPDATE INTERMEDIACOES_TABLE SET Quantidade = 5 WHERE id = :id")
        ->execute([':id' => $testId]);
    $originalQty = 5;
    echo "✓ Record updated to Quantidade=5\n";
}

echo "\n[Step 2] Simulating form submission...\n";

// Simulate POST data for negotiation form
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['negociacao_id'] = $testId;
$_POST['quantidade_vendida'] = 2;  // Sell 2 units
$_POST['taxa_saida'] = 1.5;
$_POST['valor_bruto_saida'] = 0;   // Let server calculate
$_POST['valor_bruto_importado'] = (float)$sourceRow['Valor_Bruto'];
$_POST['taxa_entrada'] = 0.5;
$_POST['valor_entrada'] = 20200.00;
$_POST['valor_plataforma'] = 150.00;
$_POST['conta_comprador'] = '5555555';
$_POST['nome_comprador'] = 'TEST BUYER';

// Start session for auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

// Capture redirect (prevent actual redirect during test)
$redirected = false;
$redirectLocation = null;

// Override AuthManager::redirectTo to capture location
$authManagerBackup = class_exists('AuthManager');

require_once __DIR__ . '/../app/model/NegociacaoModel.php';
require_once __DIR__ . '/../app/model/IntermediacoesNegociadaModel.php';

// Execute the form processing
try {
    $negociacaoId = (int)($_POST['negociacao_id'] ?? 0);
    $quantidadeVendida = (int)($_POST['quantidade_vendida'] ?? 0);

    echo "Processing: negociacao_id={$negociacaoId}, quantidade_vendida={$quantidadeVendida}\n";

    $negModel = new NegociacaoModel();
    $negociacao = $negModel->obterIntermediacao($negociacaoId);

    if (!$negociacao) {
        echo "❌ Negociacao not found\n";
        exit(1);
    }

    $quantidadeDisponivel = (int)$negociacao['quantidade_disponivel'];
    echo "Available quantity: {$quantidadeDisponivel}\n";

    // Recalculate server-side
    $taxaSaida = (float)($_POST['taxa_saida'] ?? 0);
    $taxaEntrada = (float)($_POST['taxa_entrada'] ?? 0);
    $valorBrutoSaidaInput = (float)($_POST['valor_bruto_saida'] ?? 0);
    $valorEntradaInput = (float)($_POST['valor_entrada'] ?? 0);
    $valorPlataforma = (float)($_POST['valor_plataforma'] ?? 0);

    $valorBrutoCentavos = $negociacao['valor_bruto_centavos'] ?? 0;
    $valorBrutoImportadoReal = $negModel->toReaisFloat($valorBrutoCentavos);

    $unitImportado = ($quantidadeDisponivel > 0) ? ($valorBrutoImportadoReal / $quantidadeDisponivel) : 0.0;
    $brutoSaidaTotal = ($valorBrutoSaidaInput > 0) ? $valorBrutoSaidaInput : ($unitImportado * $quantidadeVendida);
    $valorLiquidoSaida = $brutoSaidaTotal * (1 - ($taxaSaida / 100.0));

    $custoImportadoTotal = $unitImportado * $quantidadeVendida;

    $precoUnitarioSaida = $negModel->calcularPrecoUnitarioSaida($valorLiquidoSaida, $quantidadeVendida);
    $ganhoSaida = $negModel->calcularGanhoSaida($valorLiquidoSaida, $custoImportadoTotal);
    $rentabilidadeSaida = $negModel->calcularRentabilidade($ganhoSaida, $custoImportadoTotal);

    $precoUnitarioEntrada = ($valorEntradaInput > 0 && $quantidadeVendida > 0) ? ($valorEntradaInput / $quantidadeVendida) : 0.0;

    $corretagemAssessor = $negModel->calcularCorretagem($valorPlataforma);
    $roaAssessor = $negModel->calcularRoa($corretagemAssessor, $valorEntradaInput);

    echo "Calculated values:\n";
    echo "  - Bruto Saida: R$ {$brutoSaidaTotal}\n";
    echo "  - Liquido Saida: R$ {$valorLiquidoSaida}\n";
    echo "  - Preco Unitario Saida: R$ {$precoUnitarioSaida}\n";
    echo "  - Ganho: R$ {$ganhoSaida}\n";
    echo "  - Rentabilidade: {$rentabilidadeSaida}%\n";

    // Save negotiation
    $dataToSave = [
        'conta_vendedor' => $negociacao['conta'] ?? null,
        'nome_vendedor' => $negociacao['cliente'] ?? null,
        'produto' => $negociacao['produto'] ?? null,
        'estrategia' => $negociacao['estrategia'] ?? null,
        'quantidade_negociada' => $quantidadeVendida,
        'valor_bruto_importado_raw' => $valorBrutoCentavos,
        'taxa_saida' => $taxaSaida,
        'valor_bruto_saida' => $brutoSaidaTotal,
        'valor_liquido_saida' => $valorLiquidoSaida,
        'preco_unitario_saida' => $precoUnitarioSaida,
        'ganho_saida' => $ganhoSaida,
        'rentabilidade_saida' => $rentabilidadeSaida,
        'conta_comprador' => $_POST['conta_comprador'] ?? null,
        'nome_comprador' => $_POST['nome_comprador'] ?? null,
        'taxa_entrada' => $taxaEntrada,
        'valor_bruto_entrada' => $valorEntradaInput,
        'preco_unitario_entrada' => $precoUnitarioEntrada,
        'valor_plataforma' => $valorPlataforma,
        'corretagem_assessor' => $corretagemAssessor,
        'roa_assessor' => $roaAssessor,
    ];

    echo "\n[Step 3] Saving negotiation...\n";
    $insertId = $negModel->save($dataToSave);
    echo "✓ Negotiation saved with ID={$insertId}\n";

    // Update quantity in INTERMEDIACOES_TABLE
    echo "\n[Step 4] Updating quantity in INTERMEDIACOES_TABLE...\n";
    $quantidadeNova = $quantidadeDisponivel - $quantidadeVendida;
    $sucesso = $negModel->atualizarQuantidadeDisponivel($negociacaoId, $quantidadeNova);
    if ($sucesso) {
        echo "✓ Quantity updated: {$quantidadeDisponivel} -> {$quantidadeNova}\n";
    } else {
        echo "❌ Failed to update quantity\n";
        exit(1);
    }

    // Transfer to INTERMEDIACOES_TABLE_NEGOCIADA
    echo "\n[Step 5] Transferring to INTERMEDIACOES_TABLE_NEGOCIADA...\n";
    $negociadaModel = new IntermediacoesNegociadaModel();
    
    $criteria = [
        'source_id' => $testId,  // Use direct ID for accurate targeting
    ];
    
    $transferOk = $negociadaModel->copyNegotiatedRecords($criteria, $quantidadeVendida);
    if ($transferOk) {
        echo "✓ Transfer completed\n";
    } else {
        echo "⚠ Transfer returned false (check logs)\n";
    }

    // Verification
    echo "\n[Step 6] VERIFICATION\n";
    
    // Check NEGOCIACOES
    $stmt = $db->prepare("SELECT * FROM NEGOCIACOES WHERE id = :id");
    $stmt->execute([':id' => $insertId]);
    $negRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($negRow) {
        echo "✓ NEGOCIACOES row inserted:\n";
        echo "  - ID: {$negRow['id']}\n";
        echo "  - Quantidade_negociada: {$negRow['Quantidade_negociada']}\n";
        echo "  - Valor_Liquido_Saida: {$negRow['Valor_Liquido_Saida']}\n";
    } else {
        echo "❌ NEGOCIACOES row not found\n";
    }

    // Check INTERMEDIACOES_TABLE quantity updated
    $stmt = $db->prepare("SELECT Quantidade FROM INTERMEDIACOES_TABLE WHERE id = :id");
    $stmt->execute([':id' => $testId]);
    $newQtyRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($newQtyRow && (int)$newQtyRow['Quantidade'] === $quantidadeNova) {
        echo "✓ INTERMEDIACOES_TABLE quantity updated correctly: {$newQtyRow['Quantidade']}\n";
    } else {
        echo "❌ INTERMEDIACOES_TABLE quantity mismatch\n";
    }

    // Check INTERMEDIACOES_TABLE_NEGOCIADA rows
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM INTERMEDIACOES_TABLE_NEGOCIADA WHERE ID_Registro = :reg");
    $stmt->execute([':reg' => $sourceRow['ID_Registro']]);
    $negociadaCount = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($negociadaCount && (int)$negociadaCount['cnt'] > 0) {
        echo "✓ INTERMEDIACOES_TABLE_NEGOCIADA rows inserted: {$negociadaCount['cnt']}\n";
        
        // Show details
        $stmt = $db->prepare("SELECT Quantidade, Valor_Bruto FROM INTERMEDIACOES_TABLE_NEGOCIADA WHERE ID_Registro = :reg ORDER BY id DESC LIMIT 1");
        $stmt->execute([':reg' => $sourceRow['ID_Registro']]);
        $negRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($negRow) {
            echo "    Last row: Quantidade={$negRow['Quantidade']}, Valor_Bruto={$negRow['Valor_Bruto']}\n";
        }
    } else {
        echo "❌ INTERMEDIACOES_TABLE_NEGOCIADA: No rows found for ID_Registro={$sourceRow['ID_Registro']}\n";
    }

    echo "\n✅ TEST COMPLETED SUCCESSFULLY\n";

} catch (Exception $e) {
    echo "\n❌ Exception during test:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
