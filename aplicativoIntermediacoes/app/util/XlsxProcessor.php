<?php
// app/util/XlsxProcessor.php
require_once __DIR__ . '/IFileProcessor.php';
// Certifique-se de que o autoloader do Composer foi incluído no index.php
use PhpOffice\PhpSpreadsheet\IOFactory;

class XlsxProcessor implements IFileProcessor {
    private $expectedColumns = 23;

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
        
        $data = [];
        $isFirstRow = true;

        // Itera pelas linhas
        foreach ($sheet->getRowIterator() as $row) {
            
            // Pula o cabeçalho
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

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
            
            if (count($rowData) === $this->expectedColumns) {
                $data[] = $rowData;
            } 
        }
        
        return $data;
    }
}