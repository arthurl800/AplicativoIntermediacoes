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

        // O IOFactory detecta automaticamente o tipo de arquivo (XLSX, XLS, ODS)
        $spreadsheet = IOFactory::load($filePath);
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
                // Converte o valor da célula para o formato correto (especialmente datas)
                $value = $cell->getCalculatedValue();

                // Trata valores de data/hora do Excel (que são números float)
                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    // Se for data, formata para 'Y-m-d' (formato MySQL DATE)
                    $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                }

                $rowData[] = $value;
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

        return ['header' => $header, 'rows' => $rows];
    }
}