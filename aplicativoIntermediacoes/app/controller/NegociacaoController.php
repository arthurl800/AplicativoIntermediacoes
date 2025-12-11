<?php
// app/controller/NegociacaoController.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
require_once dirname(dirname(__DIR__)) . '/app/model/NegociacaoModel.php';

class NegociacaoController {
    private $authManager;
    private $negociacaoModel;

    public function __construct() {
        $this->authManager = new AuthManager();
        $this->negociacaoModel = new NegociacaoModel();

        // Proteção: Apenas usuários logados
        if (!$this->authManager->isLoggedIn()) {
            AuthManager::redirectTo('index.php?controller=auth&action=login');
            exit;
        }
    }

    /**
     * Painel de negociações - Lista todas as intermediações disponíveis
     */
    public function painel() {
        try {
            $negociacoes = $this->negociacaoModel->listarIntermedicoesDisponiveis();
            
            $base_dir = dirname(dirname(__DIR__));
            include $base_dir . '/includes/header.php';
            include $base_dir . '/app/view/negociacoes/PainelNegociacoes.php';
            include $base_dir . '/includes/footer.php';
        } catch (Exception $e) {
            error_log("Erro ao carregar painel de negociações: " . $e->getMessage());
            $this->mostrarErro("Erro ao carregar negociações.");
        }
    }

    /**
     * Formulário de negociação - Pré-preenchido com dados da intermediação selecionada
     */
    public function formulario() {
        try {
            $negociacao_id = (int)($_GET['id'] ?? 0);

            if ($negociacao_id <= 0) {
                $this->mostrarErro("Negociação não especificada.");
                return;
            }

            $negociacao = $this->negociacaoModel->obterIntermediacao($negociacao_id);

            if (!$negociacao) {
                $this->mostrarErro("Negociação não encontrada.");
                return;
            }

            // Preenche valores padrão para o formulário (cálculos iniciais)
            $quantidade_disponivel = (int)($negociacao['quantidade_disponivel'] ?? 0);
            $valor_bruto_centavos = $negociacao['valor_bruto_centavos'] ?? 0;
            // Tenta interpretar valor bruto importado como centavos (se inteiro grande) ou decimal
            $valor_bruto_importado_real = 0.0;
            if (is_numeric($valor_bruto_centavos)) {
                // Se vier como inteiro grande (centavos), divide por 100
                if ((int)$valor_bruto_centavos > 1000) {
                    $valor_bruto_importado_real = ((float)$valor_bruto_centavos) / 100.0;
                } else {
                    $valor_bruto_importado_real = (float)$valor_bruto_centavos;
                }
            }

            $preco_unitario_importado = ($quantidade_disponivel > 0) ? ($valor_bruto_importado_real / $quantidade_disponivel) : 0.0;

            // Defaults que a view pode usar (cálculos iniciais em PHP)
            $quantidade_vendida_default = ($quantidade_disponivel > 0) ? 1 : 0;
            $unit_importado = ($quantidade_disponivel > 0) ? ($valor_bruto_importado_real / $quantidade_disponivel) : 0.0;
            $bruto_saida_default = $unit_importado * $quantidade_vendida_default;
            $taxa_saida_default = 0.0;
            $valor_liquido_saida_default = $bruto_saida_default * (1 - ($taxa_saida_default / 100.0));
            $custo_importado_total_default = $unit_importado * $quantidade_vendida_default;
            $preco_unitario_vendedor_default = $this->negociacaoModel->calcularPrecoUnitarioSaida($valor_liquido_saida_default, $quantidade_vendida_default);
            $ganho_vendedor_default = $this->negociacaoModel->calcularGanhoSaida($valor_liquido_saida_default, $custo_importado_total_default);
            $rentabilidade_vendedor_default = $this->negociacaoModel->calcularRentabilidade($ganho_vendedor_default, $custo_importado_total_default);

            $negociacao['preco_unitario_importado'] = number_format($preco_unitario_importado, 2, '.', '');
            $negociacao['valor_bruto_saida_default'] = number_format($bruto_saida_default, 2, '.', '');
            $negociacao['taxa_saida_default'] = number_format($taxa_saida_default, 2, '.', '');
            $negociacao['valor_liquido_saida_default'] = number_format($valor_liquido_saida_default, 2, '.', '');
            $negociacao['preco_unitario_vendedor'] = $preco_unitario_vendedor_default;
            $negociacao['ganho_vendedor'] = $ganho_vendedor_default;
            $negociacao['rentabilidade_vendedor'] = $rentabilidade_vendedor_default;
            $negociacao['preco_unitario_comprador'] = 0.0;
            $negociacao['corretagem_assessor'] = 0.0;
            $negociacao['roa_assessor'] = 0.0;
            $negociacao['valor_bruto_importado_raw'] = $valor_bruto_centavos;

            $base_dir = dirname(dirname(__DIR__));
            include $base_dir . '/includes/header.php';
            include $base_dir . '/app/view/negociacoes/Formulario.php';
            include $base_dir . '/includes/footer.php';
        } catch (Exception $e) {
            error_log("Erro ao carregar formulário de negociação: " . $e->getMessage());
            $this->mostrarErro("Erro ao carregar formulário.");
        }
    }

    /**
     * Processa a negociação (venda de títulos)
     */
    public function processar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AuthManager::redirectTo('index.php?controller=negociacao&action=painel');
            return;
        }

        try {
            $negociacao_id = (int)($_POST['negociacao_id'] ?? 0);
            $quantidade_vendida = (int)($_POST['quantidade_vendida'] ?? 0);

            if ($negociacao_id <= 0 || $quantidade_vendida <= 0) {
                $mensagem_erro = "Dados inválidos para processar a negociação.";
                $this->mostrarErro($mensagem_erro);
                return;
            }

            $negociacao = $this->negociacaoModel->obterIntermediacao($negociacao_id);

            if (!$negociacao) {
                $this->mostrarErro("Negociação não encontrada.");
                return;
            }

            // Valida quantidade vendida
            $quantidade_disponivel = (int)$negociacao['quantidade_disponivel'];
            if ($quantidade_vendida > $quantidade_disponivel) {
                $this->mostrarErro("Quantidade vendida não pode ser maior que a quantidade disponível ({$quantidade_disponivel}).");
                return;
            }

            // Recalcula TODOS os valores no servidor para garantir integridade
            $taxa_saida = (float)($_POST['taxa_saida'] ?? 0);
            $taxa_entrada = (float)($_POST['taxa_entrada'] ?? 0);
            $valor_bruto_saida_input = (float)($_POST['valor_bruto_saida'] ?? 0);
            $valor_entrada_input = (float)($_POST['valor_entrada'] ?? 0);
            $valor_plataforma = (float)($_POST['valor_plataforma'] ?? 0);

            // Valor bruto importado vindo do registro (centavos possivelmente)
            $valor_bruto_centavos = $negociacao['valor_bruto_centavos'] ?? 0;
            $valor_bruto_importado_real = $this->negociacaoModel->toReaisFloat($valor_bruto_centavos);

            // Calcula unitário importado
            $unit_importado = ($quantidade_disponivel > 0) ? ($valor_bruto_importado_real / $quantidade_disponivel) : 0.0;

            // Determina bruto de saída total: preferir input explicitamente informado, caso contrário usar proporcional
            $bruto_saida_total = ($valor_bruto_saida_input > 0) ? $valor_bruto_saida_input : ($unit_importado * $quantidade_vendida);

            // Valor líquido após taxa de saída
            $valor_liquido_saida = $bruto_saida_total * (1 - ($taxa_saida / 100.0));

            // Custo importado proporcional para a quantidade vendida
            $custo_importado_total = $unit_importado * $quantidade_vendida;

            // Preço unitário e ganhos calculados via model
            $preco_unitario_saida = $this->negociacaoModel->calcularPrecoUnitarioSaida($valor_liquido_saida, $quantidade_vendida);
            $ganho_saida = $this->negociacaoModel->calcularGanhoSaida($valor_liquido_saida, $custo_importado_total);
            $rentabilidade_saida = $this->negociacaoModel->calcularRentabilidade($ganho_saida, $custo_importado_total);

            // Para comprador: usar valor_entrada_input se informado
            $preco_unitario_entrada = ($valor_entrada_input > 0 && $quantidade_vendida > 0) ? ($valor_entrada_input / $quantidade_vendida) : 0.0;

            // Corretagem e ROA do assessor
            $corretagem_assessor = $this->negociacaoModel->calcularCorretagem($valor_plataforma);
            $roa_assessor = $this->negociacaoModel->calcularRoa($corretagem_assessor, $valor_entrada_input);

            // Recolhe campos a salvar (usando valores calculados)
            $dataToSave = [
                'conta_vendedor' => $negociacao['conta'] ?? null,
                'nome_vendedor' => $negociacao['cliente'] ?? null,
                'produto' => $negociacao['produto'] ?? null,
                'estrategia' => $negociacao['estrategia'] ?? null,
                'quantidade_negociada' => $quantidade_vendida,
                'valor_bruto_importado_raw' => $valor_bruto_centavos,
                'taxa_saida' => $taxa_saida,
                'valor_bruto_saida' => $bruto_saida_total,
                'valor_liquido_saida' => $valor_liquido_saida,
                'preco_unitario_saida' => $preco_unitario_saida,
                'ganho_saida' => $ganho_saida,
                'rentabilidade_saida' => $rentabilidade_saida,

                'conta_comprador' => $_POST['conta_comprador'] ?? null,
                'nome_comprador' => $_POST['nome_comprador'] ?? null,
                'taxa_entrada' => $taxa_entrada,
                'valor_bruto_entrada' => $valor_entrada_input,
                'preco_unitario_entrada' => $preco_unitario_entrada,
                'valor_plataforma' => $valor_plataforma,
                'corretagem_assessor' => $corretagem_assessor,
                'roa_assessor' => $roa_assessor,
            ];

            // Salva a negociação detalhada
            $insertId = $this->negociacaoModel->save($dataToSave);

            if ($insertId <= 0) {
                $this->mostrarErro("Erro ao salvar os detalhes da negociação. Tente novamente.");
                return;
            }

            // Calcula nova quantidade e atualiza
            $quantidade_nova = $quantidade_disponivel - $quantidade_vendida;
            $sucesso = $this->negociacaoModel->atualizarQuantidadeDisponivel($negociacao_id, $quantidade_nova);

            if ($sucesso) {
                // Transfere a negociação para INTERMEDIACOES_TABLE_NEGOCIADA
                try {
                    require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacoesNegociadaModel.php';
                    $negociadaModel = new IntermediacoesNegociadaModel();
                    
                    $criteria = [
                        'source_id' => $negociacao_id,  // Usar ID direto para evitar ambiguidades
                    ];
                    
                    $transferOk = $negociadaModel->copyNegotiatedRecords($criteria, $quantidade_vendida);
                    if (!$transferOk) {
                        error_log("Warning: Falha ao transferir negociação {$insertId} para INTERMEDIACOES_TABLE_NEGOCIADA");
                    }
                } catch (Exception $e) {
                    error_log("Exception ao transferir para NEGOCIADA: " . $e->getMessage());
                }
                
                $_SESSION['mensagem_sucesso'] = "Negociação realizada com sucesso! Quantidade vendida: {$quantidade_vendida}. Quantidade remanescente: {$quantidade_nova}";
                AuthManager::redirectTo('index.php?controller=negociacao&action=painel');
            } else {
                $this->mostrarErro("Erro ao atualizar quantidade após salvar a negociação. ID do registro: {$insertId}");
            }
        } catch (Exception $e) {
            error_log("Erro ao processar negociação: " . $e->getMessage());
            $this->mostrarErro("Erro ao processar a negociação.");
        }
    }

    /**
     * Exibe mensagem de erro
     */
    private function mostrarErro($mensagem) {
        $base_dir = dirname(dirname(__DIR__));
        include $base_dir . '/includes/header.php';
        ?>
        <main>
            <div class="alert alert-danger">
                <h2>Erro</h2>
                <p><?= htmlspecialchars($mensagem) ?></p>
                <a href="index.php?controller=negociacao&action=painel" class="btn btn-primary">
                    Voltar ao Painel
                </a>
            </div>
        </main>
        <?php
        include $base_dir . '/includes/footer.php';
    }
}
