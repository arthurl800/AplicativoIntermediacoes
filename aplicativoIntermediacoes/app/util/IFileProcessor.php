<?php
// app/util/IFileProcessor.php

/**
 * Define o contrato para qualquer processador de arquivo (CSV, XLSX, etc.)
 */
interface IFileProcessor {
    /**
     * Lê o arquivo no caminho fornecido e retorna um array com header e rows.
     * @param string $filePath O caminho temporário do arquivo.
    * @return array Estrutura associativa com chaves 'header' (array|null) e 'rows' (array de linhas)
    * - header: array com nomes de colunas (ou null se não houver)
    * - rows: array de arrays onde cada array interno é uma linha de dados
     */
    public function read(string $filePath): array;
}