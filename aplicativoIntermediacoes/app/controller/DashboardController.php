<?php
// app/controller/DashboardController.php

require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacaoModel.php';

class DashboardController {
    private $authManager;

    public function __construct() {
        $this->authManager = new AuthManager();
    }

    public function index() {
        // Verifica se o usuário está logado
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
        }

        $username = $_SESSION['username'];
        $role = $_SESSION['role'];
        $is_admin = $this->authManager->isAdmin();

        // Carrega o header/footer
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/dashboard/index.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Formulário para negociação manual (venda/compra)
    public function negotiate() {
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
        }

        $interModel = new IntermediacaoModel();
        $records = $interModel->getAllData(); // retorna até 100

        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/dashboard/negotiate_form.php';
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Processa o formulário de negociação e calcula splits
    public function processNegotiation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('index.php?controller=dashboard&action=index');
        }

        // Recebe dados do form
        $recordId = (int)($_POST['record_id'] ?? 0);
        $seller_tax_pct = floatval(str_replace(',', '.', $_POST['seller_tax_pct'] ?? 0));
        $seller_valor_bruto = floatval(str_replace(['.', ' '], ['', ''], $_POST['seller_valor_bruto'] ?? 0));
        $seller_valor_liquido = floatval(str_replace(['.', ' '], ['', ''], $_POST['seller_valor_liquido'] ?? 0));

        $buyer_entries = $_POST['buyers'] ?? []; // array of buyer rows: [ ['conta'=>..., 'cliente'=>..., 'qtd'=>..., 'taxa_entrada'=>..., 'valor_bruto'=>...], ... ]

        // Carrega o registro base
        $interModel = new IntermediacaoModel();
        $record = null;
        if ($recordId > 0) {
            $all = $interModel->getAllData();
            foreach ($all as $r) {
                if ((int)$r['id'] === $recordId) { $record = $r; break; }
            }
        }

        // Caso registro não encontrado, tenta primeiro disponível
        if (!$record) {
            $all = $interModel->getAllData();
            $record = $all[0] ?? null;
        }

        // Calcula splits: se compradores especificados, aloca quantidade e valor proporcionalmente
        $allocations = [];
        $totalAssignedQty = 0;

        foreach ($buyer_entries as $be) {
            $qtd = (int)($be['qtd'] ?? 0);
            if ($qtd <= 0) continue;
            $totalAssignedQty += $qtd;
        }

        $originalQty = (int)($record['Quantidade'] ?? 0);
        $unitValue = 0.0;
        if ($originalQty > 0) {
            $unitValue = ((float)($record['Valor_Bruto'] ?? 0)) / $originalQty;
        }

        foreach ($buyer_entries as $be) {
            $qtd = (int)($be['qtd'] ?? 0);
            if ($qtd <= 0) continue;

            // If totalAssignedQty differs from originalQty, scale proportionally to preserve total
            $proportion = ($totalAssignedQty > 0) ? ($qtd / $totalAssignedQty) : 0;
            $valor_bruto_calc = ($totalAssignedQty > 0) ? ($proportion * ($record['Valor_Bruto'] ?? 0)) : ($qtd * $unitValue);

            $allocations[] = [
                'conta' => $be['conta'] ?? null,
                'cliente' => $be['cliente'] ?? null,
                'qtd' => $qtd,
                'valor_bruto' => $valor_bruto_calc,
                'taxa_entrada_pct' => floatval(str_replace(',', '.', $be['taxa_entrada'] ?? 0)),
                'valor_liquido_buyer' => $valor_bruto_calc * (1 - (floatval(str_replace(',', '.', $be['taxa_entrada'] ?? 0)) / 100))
            ];
        }

        // Prepare result variables for the view
        $result = [
            'record' => $record,
            'seller' => [
                'tax_pct' => $seller_tax_pct,
                'valor_bruto' => $seller_valor_bruto,
                'valor_liquido' => $seller_valor_liquido
            ],
            'allocations' => $allocations
        ];

        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/dashboard/negotiate_result.php';
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }
}
