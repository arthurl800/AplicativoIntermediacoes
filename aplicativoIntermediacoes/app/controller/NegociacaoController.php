<?php
// app/controller/NegociacaoController.php

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
            include $base_dir . '/app/view/negociacoes/painel.php';
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

            $base_dir = dirname(dirname(__DIR__));
            include $base_dir . '/includes/header.php';
            include $base_dir . '/app/view/negociacoes/formulario.php';
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

            // Calcula nova quantidade
            $quantidade_nova = $quantidade_disponivel - $quantidade_vendida;

            // Atualiza no banco de dados
            $sucesso = $this->negociacaoModel->atualizarQuantidadeDisponivel($negociacao_id, $quantidade_nova);

            if ($sucesso) {
                $_SESSION['mensagem_sucesso'] = "Negociação realizada com sucesso! Quantidade vendida: {$quantidade_vendida}. Quantidade remanescente: {$quantidade_nova}";
                AuthManager::redirectTo('index.php?controller=negociacao&action=painel');
            } else {
                $this->mostrarErro("Erro ao processar a negociação. Tente novamente.");
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
