<?php
// app/util/CsvProcessor.php
require_once __DIR__ . '/IFileProcessor.php';

class CsvProcessor implements IFileProcessor {
    private $delimiter = ',';
    private $expectedColumns = 23;

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

        while (($row = fgetcsv($handle, 1000, $this->delimiter)) !== FALSE) {
            // Só processa se o número de colunas estiver correto
            if (count($row) === $this->expectedColumns) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return ['header' => $header, 'rows' => $rows];
    }
}
