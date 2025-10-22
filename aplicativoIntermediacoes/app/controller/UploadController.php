<?php
// app/controller/UploadController.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/model/IntermediacaoModel.php';
require_once dirname(dirname(__DIR__)) . '/app/util/IFileProcessor.php';
require_once dirname(dirname(__DIR__)) . '/app/util/CsvProcessor.php';
require_once dirname(dirname(__DIR__)) . '/app/util/XlsxProcessor.php';

class UploadController {
    private $model;

    public function __construct() {
        $this->model = new IntermediacaoModel();
    }

    // Ação Padrão (index) para exibir o formulário
    public function index() {
        // Carrega o header/footer da pasta includes
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        // Carrega a view de formulário
        include dirname(dirname(__DIR__)) . '/app/view/upload_form.php'; 
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
        set_time_limit(180); // Aumenta o limite de tempo para 3 minutos
        ini_set('memory_limit', '512M'); // Aumenta o limite de memória
        // -------------------------------------------------------

        $result = ['success' => false, 'message' => '', 'errors' => []];

        if (isset($_FILES["arquivo_csv"])) {
            $arquivo = $_FILES["arquivo_csv"];
            $arquivo_temp = $arquivo["tmp_name"];
            $arquivo_nome = $arquivo["name"];
            
            try {
                // 1. Injeta o Processador correto com base na extensão
                $processor = $this->getProcessor($arquivo_nome);

                // 2. Processa/Lê o arquivo (Utility)
                $resultData = $processor->read($arquivo_temp);

                // Compatibilidade: se o processador ainda retornar só rows, normalizamos
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

                // 3. Salva os dados no DB (Model)
                $db_result = $this->model->insertBatch($records);

                // --- PONTO DE DEBUG TEMPORÁRIO ---
                // Se a execução parar aqui, verifique o conteúdo de $db_result.
                // Ele deve mostrar se algum registro foi inserido e se houve erros de SQL.
                // echo "<pre>"; var_dump($db_result); 
                // echo "</pre>"; die;
                // --- FIM DO DEBUG TEMPORÁRIO ---

                $result['success'] = true;
                $result['message'] = "Arquivo '{$arquivo_nome}' processado com sucesso! **{$db_result['inserted']}** registros inseridos.";
                $result['errors'] = $db_result['errors'];

                // Armazena um preview (header + head 25 rows) na sessão para ser exibido na página de visualização de dados
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['last_import_preview'] = ['header' => $header, 'rows' => array_slice($records, 0, 25)];
                // Redireciona para a visualização de dados para integração (mostra preview)
                header('Location: index.php?controller=dados&action=visualizar&preview=1');
                exit;

            } catch (\Exception $e) {
                $result['message'] = "Erro de Processamento: " . $e->getMessage();
            }
        } else {
            $result['message'] = "Erro: Nenhum arquivo enviado.";
        }

        // Carrega a view de resultado
        include dirname(dirname(__DIR__)) . '/includes/header.php';
        include dirname(dirname(__DIR__)) . '/app/view/upload_result.php'; 
        include dirname(dirname(__DIR__)) . '/includes/footer.php';
    }
}
