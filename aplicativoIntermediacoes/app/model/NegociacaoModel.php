<?php
// app/model/NegociacaoModel.php

require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class NegociacaoModel {
    private $pdo;
    private $table = 'NEGOCIACOES';

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Persiste uma negociação na tabela NEGOCIACOES.
     * @param array $data Campos esperados: conta, cliente, tipo, quantidade, quantidade_negociada,
     * valor_bruto_saida, taxa_saida, valor_bruto_entrada, taxa_entrada, corretagem, roa,
     * retorno_vendedor_valor, retorno_vendedor_pct, operador
     * @return int ID inserido ou 0 em falha
     */
    public function save(array $data): int {
        $sql = "INSERT INTO {$this->table} (
            conta, cliente, tipo, quantidade, quantidade_negociada,
            valor_bruto_saida, taxa_saida, valor_bruto_entrada, taxa_entrada,
            corretagem, roa, retorno_vendedor_valor, retorno_vendedor_pct, operador
        ) VALUES (
            :conta, :cliente, :tipo, :quantidade, :quantidade_negociada,
            :valor_bruto_saida, :taxa_saida, :valor_bruto_entrada, :taxa_entrada,
            :corretagem, :roa, :retorno_vendedor_valor, :retorno_vendedor_pct, :operador
        )";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':conta' => $data['conta'] ?? null,
                ':cliente' => $data['cliente'] ?? null,
                ':tipo' => $data['tipo'] ?? null,
                ':quantidade' => $data['quantidade'] ?? 0,
                ':quantidade_negociada' => $data['quantidade_negociada'] ?? 0,
                ':valor_bruto_saida' => $data['valor_bruto_saida'] ?? 0,
                ':taxa_saida' => $data['taxa_saida'] ?? 0,
                ':valor_bruto_entrada' => $data['valor_bruto_entrada'] ?? 0,
                ':taxa_entrada' => $data['taxa_entrada'] ?? 0,
                ':corretagem' => $data['corretagem'] ?? 0,
                ':roa' => $data['roa'] ?? 0,
                ':retorno_vendedor_valor' => $data['retorno_vendedor_valor'] ?? 0,
                ':retorno_vendedor_pct' => $data['retorno_vendedor_pct'] ?? 0,
                ':operador' => $data['operador'] ?? null,
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erro ao salvar negociacao: ' . $e->getMessage());
            return 0;
        }
    }
}
