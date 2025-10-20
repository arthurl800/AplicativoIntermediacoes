<?php
// app/util/IFileProcessor.php

/**
 * Define o contrato para qualquer processador de arquivo (CSV, XLSX, etc.)
 */
interface IFileProcessor {
    /**
     * Lê o arquivo no caminho fornecido e retorna um array de linhas.
     * @param string $filePath O caminho temporário do arquivo.
     * @return array Array de arrays, onde cada array interno é uma linha de dados.
     */
    public function read(string $filePath): array;
}