<?php
// app/controller/UploadController.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacaoModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/IFileProcessor.php';
require_once dirname(dirname(__DIR__)) . '/app/util/CsvProcessor.php';
require_once dirname(dirname(__DIR__)) . '/app/util/XlsxProcessor.php';
require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacoesNegociadaModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/AuditLogger.php';

class UploadController {
    private $model;
    private $auditLogger;

    public function __construct() {
        $this->model = new IntermediacaoModel();
        $this->auditLogger = AuditLogger::getInstance();
    }

    // Ação Padrão (index) para exibir o formulário
    public function index() {
        // Carrega o header/footer da pasta includes
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        // Carrega a view de formulário
        include dirname(dirname(__DIR__)) . '/app/view/UploadForm.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }

    // Método de fábrica para obter o processador correto
    private function getProcessor(string $fileType): IFileProcessor {
        $extension = strtolower(pathinfo($fileType, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return new CsvProcessor(',');
        } elseif (in_array($extension, ['xlsx', 'xls', 'ods'])) {
            return new XlsxProcessor();
        } else {
            throw new Exception("Formato de arquivo não suportado: {$extension}. Use CSV ou XLSX.");
        }
    }

    // Ação para processar o upload
    public function processUpload() {

        // --- OTIMIZAÇÃO DE PERFORMANCE PARA ARQUIVOS GRANDES ---
        set_time_limit(300); // Aumenta o limite de tempo para 5 minutos
        ini_set('memory_limit', '2048M'); // Aumenta o limite de memória para 2GB
        gc_enable(); // Habilita coleta de lixo
        // -------------------------------------------------------

        $result = ['success' => false, 'message' => '', 'errors' => []];

        if (isset($_FILES["arquivo_csv"])) {
            $arquivo = $_FILES["arquivo_csv"];
            $arquivo_temp = $arquivo["tmp_name"];
            $arquivo_nome = $arquivo["name"];
            
            try {
                // Injeta o Processador correto com base na extensão
                $processor = $this->getProcessor($arquivo_nome);

                // Processa/Lê o arquivo (Utility)
                $resultData = $processor->read($arquivo_temp);

                // Compatibilidade: Verifica se o resultado tem header e rows
                if (is_array($resultData) && array_key_exists('rows', $resultData)) {
                    $header = $resultData['header'] ?? null;
                    $records = $resultData['rows'] ?? [];
                } else {
                    // fallback (antigo behaviour)
                    $header = null;
                    $records = $resultData;
                }

                if (empty($records)) {
                    throw new Exception("Nenhum dado válido encontrado no arquivo. Verifique o conteúdo e o cabeçalho.");
                }
                
                // Log para debug
                error_log("UPLOAD: Total de linhas lidas do arquivo: " . count($records));
                
                // Força coleta de lixo após processar o arquivo
                gc_collect_cycles();

                // Obtém estratégia de duplicatas do formulário
                $duplicateStrategy = $_POST['duplicate_strategy'] ?? 'error';
                if (!in_array($duplicateStrategy, ['skip', 'replace', 'error'])) {
                    $duplicateStrategy = 'error';
                }

                // Salva os dados no DB (Model)
                $db_result = $this->model->insertBatch($records, $duplicateStrategy);
                
                // Log do resultado
                error_log("UPLOAD: Linhas inseridas: " . $db_result['inserted'] . " | Erros: " . count($db_result['errors']));
                if (!empty($db_result['errors'])) {
                    error_log("UPLOAD: Detalhes dos erros: " . implode(" | ", array_slice($db_result['errors'], 0, 5)));
                }

                // Após inserir na tabela principal, copia registros para a tabela de negociadas
                // (copia todos os registros com Quantidade > 0 que ainda não existam na tabela negociada)
                try {
                    $negCopyModel = new IntermediacoesNegociadaModel();
                    $negCopyModel->copyNegotiatedRecords([], 0);
                } catch (Exception $e) {
                    // não interrompe o fluxo principal em caso de falha na cópia; apenas loga
                    error_log('Falha ao copiar registros para INTERMEDIACOES_TABLE_NEGOCIADA: ' . $e->getMessage());
                    $result['errors'][] = 'Falha ao copiar registros para a tabela negociada: ' . $e->getMessage();
                }

                $result['success'] = true;
                $message = "Importado com sucesso {$db_result['inserted']} linhas";
                
                if (isset($db_result['skipped']) && $db_result['skipped'] > 0) {
                    $message .= " ({$db_result['skipped']} duplicatas ignoradas)";
                }
                
                $result['message'] = $message;
                $result['errors'] = $db_result['errors'];

                // Registra upload na auditoria
                $this->auditLogger->logUpload($arquivo_nome, $db_result['inserted']);

                // Armazena um preview (header + head 25 rows) na sessão para ser exibido na página de visualização de dados
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['last_import_preview'] = ['header' => $header, 'rows' => array_slice($records, 0, 25)];
                // Mantém o preview na sessão para visualização posterior
                // A view de resultado exibirá a mensagem de sucesso.

            } catch (\Exception $e) {
                $result['message'] = "Erro de Processamento: " . $e->getMessage();
            }
        } else {
            $result['message'] = "Erro: Nenhum arquivo enviado.";
        }

        // Carrega a view de resultado
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/UploadResult.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }
}
