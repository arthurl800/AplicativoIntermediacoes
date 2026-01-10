<?php
// app/controller/NegociacaoController.php

require_once dirname(dirname(__DIR__)) . '/app/util/AuthManager.php';
require_once dirname(dirname(__DIR__)) . '/app/model/NegociacaoModel.php';
require_once dirname(dirname(__DIR__)) . '/app/model/AuditoriaModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/AuditLogger.php';

class NegociacaoController {
    private $authManager;
    private $negociacaoModel;
    private $auditoriaModel;
    private $auditLogger;

    public function __construct() {
        $this->authManager = new AuthManager();
        $this->negociacaoModel = new NegociacaoModel();
        $this->auditoriaModel = new AuditoriaModel();
        $this->auditLogger = AuditLogger::getInstance();

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

            // Não realizar cálculos no servidor: enviar apenas dados brutos para que o cliente (JS) faça todos os cálculos.
            // Mantemos apenas os campos essenciais para a view renderizar e o JS operar.
            $negociacao['valor_bruto_importado_raw'] = $negociacao['valor_bruto_centavos'] ?? 0;
            $negociacao['quantidade_disponivel'] = (int)($negociacao['quantidade_disponivel'] ?? 0);

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

            // NÃO recalcular no servidor: aceitar os valores calculados no cliente (JS)
            // Lemos os campos enviados pelo formulário (gerados pelo JS) e os persistimos como recebidos.
            $taxa_saida = (float)($_POST['taxa_saida'] ?? 0);
            $taxa_entrada = (float)($_POST['taxa_entrada'] ?? 0);
            $bruto_saida_total = (float)($_POST['valor_bruto_saida'] ?? 0);
            $valor_liquido_saida = (float)($_POST['valor_liquido_saida'] ?? 0);
            $preco_unitario_saida = (float)($_POST['preco_unitario_saida'] ?? 0);
            $ganho_saida = (float)($_POST['ganho_saida'] ?? 0);
            $rentabilidade_saida = (float)($_POST['rentabilidade_saida'] ?? 0);

            $valor_entrada_input = (float)($_POST['valor_entrada'] ?? 0);
            $preco_unitario_entrada = (float)($_POST['preco_unitario_entrada'] ?? 0);

            $valor_plataforma = (float)($_POST['valor_plataforma'] ?? 0);
            $corretagem_assessor = (float)($_POST['corretagem_assessor'] ?? $_POST['corretagem'] ?? 0);
            $roa_assessor = (float)($_POST['roa_assessor'] ?? $_POST['roa'] ?? 0);

            // Valor bruto importado vindo do registro (centavos possivelmente) - mantemos como fornecido pela origem
            $valor_bruto_centavos = $negociacao['valor_bruto_centavos'] ?? 0;

            // Recolhe campos a salvar (usando valores calculados)
            $dataToSave = [
                'conta_vendedor' => $negociacao['conta'] ?? null,
                'nome_vendedor' => $negociacao['cliente'] ?? null,
                'produto' => $negociacao['produto'] ?? null,
                'id_registro_source' => $negociacao_id,  // ID da intermediação original
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
                
                // Registra negociação na auditoria (NEGOCIACOES_AUDITORIA)
                $currentUser = $this->authManager->getCurrentUser();
                $this->auditoriaModel->registrarAuditoria(
                    $insertId,  // ID da negociação criada
                    'CRIACAO',
                    $currentUser ? $currentUser['name'] : 'Sistema',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    null,  // Dados antes (não existe antes da criação)
                    array_merge($dataToSave, [
                        'negociacao_id' => $negociacao_id,
                        'quantidade_disponivel_antes' => $quantidade_disponivel,
                        'quantidade_disponivel_depois' => $quantidade_nova,
                        'insert_id' => $insertId
                    ]),
                    "Negociação criada - Quantidade vendida: {$quantidade_vendida}, Quantidade remanescente: {$quantidade_nova}"
                );
                
                // Registra também no log geral do sistema
                $this->auditLogger->logNegociacao($negociacao_id, array_merge($dataToSave, [
                    'negociacao_id' => $negociacao_id,
                    'quantidade_disponivel_antes' => $quantidade_disponivel,
                    'quantidade_disponivel_depois' => $quantidade_nova,
                    'insert_id' => $insertId
                ]));
                
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

    /**
     * Estorna uma negociação
     */
    public function estornar() {
        $base_dir = dirname(dirname(__DIR__));
        
        // Verifica se o ID foi fornecido
        $negociacaoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($negociacaoId <= 0) {
            $_SESSION['mensagem_erro'] = "ID de negociação inválido.";
            AuthManager::redirectTo('index.php?controller=dados&action=visualizar_negociadas');
            exit;
        }
        
        // Processa o estorno
        $resultado = $this->negociacaoModel->estornarNegociacao($negociacaoId);
        
        if ($resultado['success']) {
            // Registra o estorno na auditoria
            $negociacao = $resultado['negociacao'];
            $currentUser = $this->authManager->getCurrentUser();
            
            $this->auditoriaModel->registrarAuditoria(
                $negociacaoId,
                'ESTORNO',
                $currentUser ? $currentUser['Nome'] : 'Sistema',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $negociacao,  // Dados antes do estorno
                null,  // Dados depois (negociação deletada)
                "Negociação estornada - Produto: {$negociacao['Produto']}, Quantidade: {$negociacao['Quantidade_negociada']}"
            );
            
            // Registra também no log geral do sistema
            $this->auditLogger->logDelete('NEGOCIACAO', 'Estorno de negociação ID ' . $negociacaoId, $negociacao);
            
            $_SESSION['mensagem_sucesso'] = $resultado['message'];
        } else {
            $_SESSION['mensagem_erro'] = $resultado['message'];
        }
        
        AuthManager::redirectTo('index.php?controller=dados&action=visualizar_negociadas');
    }
}
