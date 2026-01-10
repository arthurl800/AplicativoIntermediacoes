<?php
// app/util/XlsxProcessor.php
require_once __DIR__ . '/IFileProcessor.php';
// Certifica-se de que o autoloader do Composer foi incluído no index.php
use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxProcessor implements IFileProcessor {
    // Número de colunas na nova estrutura da tabela
    private $expectedColumns = 23;  // Conta até ID_Registro (Data_Importacao é automático)
    
    // Mapeamento das colunas esperadas na ordem correta
    private $columnMap = [
        'Conta', 'Nome', 'Mercado', 'Sub_Mercado', 'Ativo',
        'Produto', 'CNPJ', 'Emissor', 'Data_Compra', 'Taxa_Compra',
        'Taxa_Emissao', 'Vencimento', 'Quantidade', 'Valor_Bruto',
        'IR', 'IOF', 'Valor_Liquido', 'Estrategia', 'Escritorio',
        'Data_Registro', 'Data_Cotizacao_Prev', 'Tipo_Plano', 'ID_Registro'
    ];

    /**
     * Lê o arquivo XLSX e retorna um array de dados.
     */
    public function read(string $filePath): array {
        if (!is_uploaded_file($filePath)) {
            throw new Exception("Falha no upload do arquivo.");
        }

        // Aumenta o limite de memória temporariamente para este processo
        $oldMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1024M');
        
        try {
            // Configura o leitor para usar menos memória
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);  // Ignora formatação, lê apenas dados
            
            // Carrega o arquivo
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            $rows = [];
            $header = null;
            $isFirstRow = true;

            // Itera pelas linhas
            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                // Itera pelas células na linha
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

            // Garante o número correto de colunas e limita
            $rowData = array_pad($rowData, $this->expectedColumns, null);
            $rowData = array_slice($rowData, 0, $this->expectedColumns);

            if ($isFirstRow) {
                $header = $rowData;
                $isFirstRow = false;
                continue;
            }

            // Verifica se a linha tem dados válidos (não está vazia)
            $hasData = false;
            foreach ($rowData as $value) {
                if (!empty($value) && $value !== null && $value !== '') {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData && count($rowData) === $this->expectedColumns) {
                // Converte datas do Excel (números seriais) para formato MySQL
                // Data_Compra (índice 8)
                if (is_numeric($rowData[8]) && $rowData[8] > 1) {
                    $rowData[8] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[8])->format('Y-m-d');
                }
                
                // Vencimento (índice 11)
                if (is_numeric($rowData[11]) && $rowData[11] > 1) {
                    $rowData[11] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[11])->format('Y-m-d');
                }
                
                // Data_Registro (índice 19)
                if (is_numeric($rowData[19]) && $rowData[19] > 1) {
                    $rowData[19] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[19])->format('Y-m-d');
                }
                
                // Data_Cotizacao_Prev (índice 20)
                if (is_numeric($rowData[20]) && $rowData[20] > 1) {
                    $rowData[20] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData[20])->format('Y-m-d');
                }
                
                // Trata valores numéricos
                $rowData[12] = (int)$rowData[12];  // Quantidade como inteiro
                $rowData[13] = (float)str_replace(['R$', '.', ','], ['', '', '.'], $rowData[13]); // Valor_Bruto
                $rowData[14] = (float)str_replace(['R$', '.', ','], ['', '', '.'], $rowData[14]); // IR
                $rowData[15] = (float)str_replace(['R$', '.', ','], ['', '', '.'], $rowData[15]); // IOF
                $rowData[16] = (float)str_replace(['R$', '.', ','], ['', '', '.'], $rowData[16]); // Valor_Liquido

                // Trata taxas (remove % e converte para decimal)
                $rowData[9] = (float)str_replace(['%', ','], ['', '.'], $rowData[9]);  // Taxa_Compra
                $rowData[10] = (float)str_replace(['%', ','], ['', '.'], $rowData[10]); // Taxa_Emissao

                $rows[] = $rowData;
            }
        }
        
        // Libera memória do spreadsheet
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return ['header' => $header, 'rows' => $rows];
        
        } catch (Exception $e) {
            // Restaura o limite de memória original em caso de erro
            ini_set('memory_limit', $oldMemoryLimit);
            throw $e;
        } finally {
            // Restaura o limite de memória original
            ini_set('memory_limit', $oldMemoryLimit);
        }
    }
}