<?php
// app/util/CsvProcessor.php
require_once __DIR__ . '/IFileProcessor.php';

class CsvProcessor implements IFileProcessor {
    private $delimiter = ',';
    private $expectedColumns = 23;
    // Colunas esperadas na mesma ordem do banco (sem Data_Importacao)
    private $columnMap = [
        'Conta', 'Nome', 'Mercado', 'Sub_Mercado', 'Ativo',
        'Produto', 'CNPJ', 'Emissor', 'Data_Compra', 'Taxa_Compra',
        'Taxa_Emissao', 'Vencimento', 'Quantidade', 'Valor_Bruto',
        'IR', 'IOF', 'Valor_Liquido', 'Estrategia', 'Escritorio',
        'Data_Registro', 'Data_Cotizacao_Prev', 'Tipo_Plano', 'ID_Registro'
    ];

    public function __construct(string $delimiter = ',') {
        $this->delimiter = $delimiter;
    }

    /**
     * Lê o arquivo CSV, pulando o cabeçalho, e retorna um array de dados.
     */
    public function read(string $filePath): array {
        if (!is_uploaded_file($filePath)) {
            throw new Exception("Falha no upload do arquivo.");
        }

        // Tenta abrir o arquivo para leitura
        if (($handle = fopen($filePath, "r")) === FALSE) {
            throw new Exception("Erro ao abrir o arquivo CSV.");
        }

        $header = null;
        $rows = [];

        // Lê a primeira linha como cabeçalho, se possível
        $first = fgetcsv($handle, 1000, $this->delimiter);
        if ($first !== false) {
            $header = $first;
        }

        while (($row = fgetcsv($handle, 0, $this->delimiter)) !== FALSE) {
            // Normaliza número de colunas para expectedColumns
            $row = array_pad($row, $this->expectedColumns, null);

            // Verifica se a linha contém dados significativos
            $hasData = false;
            foreach ($row as $v) { if ($v !== null && $v !== '') { $hasData = true; break; } }
            if (!$hasData) continue;

            // Conversões (similares ao XlsxProcessor)
            // Quantidade -> inteiro (índice 12), Valor_Bruto -> float (13), IR(14), IOF(15), Valor_Liquido(16)
            $row[12] = isset($row[12]) ? (int)preg_replace('/[^0-9-]/', '', $row[12]) : null;
            $row[13] = isset($row[13]) ? (float)str_replace([',', 'R$', ' '], ['.', '', ''], $row[13]) : null;
            $row[14] = isset($row[14]) ? (float)str_replace([',', 'R$', ' '], ['.', '', ''], $row[14]) : null;
            $row[15] = isset($row[15]) ? (float)str_replace([',', 'R$', ' '], ['.', '', ''], $row[15]) : null;
            $row[16] = isset($row[16]) ? (float)str_replace([',', 'R$', ' '], ['.', '', ''], $row[16]) : null;

            $rows[] = $row;
        }

        fclose($handle);

        return ['header' => $header, 'rows' => $rows];
    }
}
