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
        $result = ['success' => false, 'message' => '', 'errors' => []];

        if (isset($_FILES["arquivo_csv"])) {
            $arquivo = $_FILES["arquivo_csv"];
            $arquivo_temp = $arquivo["tmp_name"];
            $arquivo_nome = $arquivo["name"];
            
            try {
                // 1. Injeta o Processador correto com base na extensão
                $processor = $this->getProcessor($arquivo_nome);

                // 2. Processa/Lê o arquivo (Utility)
                $records = $processor->read($arquivo_temp);

                if (empty($records)) {
                    throw new Exception("Nenhum dado válido encontrado no arquivo. Verifique o conteúdo e o cabeçalho.");
                }

                // 3. Salva os dados no DB (Model)
                $db_result = $this->model->insertBatch($records);

                $result['success'] = true;
                $result['message'] = "Arquivo '{$arquivo_nome}' processado com sucesso! **{$db_result['inserted']}** registros inseridos.";
                $result['errors'] = $db_result['errors'];

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
