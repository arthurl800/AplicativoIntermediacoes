<?php
// app/model/NegociacaoModel.php

// Inclui dependências
require_once dirname(dirname(__DIR__)) . '/app/util/Database.php';

class NegociacaoModel {
    private $pdo;
    private $table = 'NEGOCIACOES';
    private $tableIntermediacao = 'INTERMEDIACOES_TABLE'; // Tabela de intermediações para leitura

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        
        // Obtém o nome da tabela da configuração se disponível
        $configFile = dirname(dirname(__DIR__)) . '/config/database.php';
        if (file_exists($configFile)) {
            $cfg = include $configFile;
            $this->tableIntermediacao = $cfg['TABLE_NAME'] ?? 'INTERMEDIACOES_TABLE';
        }
    }

    /**
     * Persiste uma negociação completa na tabela NEGOCIACOES com todos os campos.
     * @param array $data Campos esperados conforme a estrutura NEGOCIACOES:
     *  - Conta_Vendedor, Nome_Vendedor, Produto, Estrategia
     *  - Quantidade_negociada, Valor_Bruto_Importado_Raw
     *  - Taxa_Saida, Valor_Bruto_Saida, Valor_Liquido_Saida, Preco_Unitario_Saida
     *  - Ganho_Saida, Rentabilidade_Saida
     *  - Conta_Comprador, Nome_Comprador, Taxa_Entrada, Valor_Bruto_Entrada
     *  - Preco_Unitario_Entrada, Valor_Plataforma
     *  - Corretagem_Assessor, Roa_Assessor
     * @return int ID inserido ou 0 em falha
     */
    public function save(array $data): int {
        $sql = "INSERT INTO {$this->table} (
            Data_Registro,
            Conta_Vendedor, Nome_Vendedor, Produto, ID_Registro_Source, Estrategia,
            Quantidade_negociada, Valor_Bruto_Importado_Raw,
            Taxa_Saida, Valor_Bruto_Saida, Valor_Liquido_Saida, Preco_Unitario_Saida,
            Ganho_Saida, Rentabilidade_Saida,
            Conta_Comprador, Nome_Comprador, Taxa_Entrada, Valor_Bruto_Entrada,
            Preco_Unitario_Entrada, Valor_Plataforma,
            Corretagem_Assessor, Roa_Assessor
        ) VALUES (
            NOW(),
            :conta_vendedor, :nome_vendedor, :produto, :id_registro_source, :estrategia,
            :quantidade_negociada, :valor_bruto_importado_raw,
            :taxa_saida, :valor_bruto_saida, :valor_liquido_saida, :preco_unitario_saida,
            :ganho_saida, :rentabilidade_saida,
            :conta_comprador, :nome_comprador, :taxa_entrada, :valor_bruto_entrada,
            :preco_unitario_entrada, :valor_plataforma,
            :corretagem_assessor, :roa_assessor
        )";

        try {
            $stmt = $this->pdo->prepare($sql);

            // Normaliza ID_Registro_Source: aceita string vazia e converte para NULL,
            // converte valores numéricos para inteiro antes de bindar.
            $idRegistroSource = null;
            if (isset($data['ID_Registro_Source'])) {
                $raw = $data['ID_Registro_Source'];
            } elseif (isset($data['id_registro_source'])) {
                $raw = $data['id_registro_source'];
            } else {
                $raw = null;
            }

            if (is_string($raw)) {
                $raw = trim($raw);
                if ($raw === '') {
                    $idRegistroSource = null;
                } elseif (is_numeric($raw)) {
                    $idRegistroSource = (int)$raw;
                }
            } elseif (is_numeric($raw)) {
                $idRegistroSource = (int)$raw;
            } else {
                $idRegistroSource = null;
            }

            $stmt->execute([
                ':conta_vendedor' => $data['conta_vendedor'] ?? $data['conta'] ?? null,
                ':nome_vendedor' => $data['nome_vendedor'] ?? $data['cliente'] ?? null,
                ':produto' => $data['produto'] ?? $data['tipo'] ?? null,
                ':id_registro_source' => $idRegistroSource,
                ':estrategia' => $data['estrategia'] ?? null,
                ':quantidade_negociada' => $data['quantidade_negociada'] ?? 0,
                ':valor_bruto_importado_raw' => $data['valor_bruto_importado_raw'] ?? $data['valor_bruto_importado'] ?? 0,
                ':taxa_saida' => $data['taxa_saida'] ?? 0,
                ':valor_bruto_saida' => $data['valor_bruto_saida'] ?? 0,
                ':valor_liquido_saida' => $data['valor_liquido_saida'] ?? 0,
                ':preco_unitario_saida' => $data['preco_unitario_saida'] ?? 0,
                ':ganho_saida' => $data['ganho_saida'] ?? 0,
                ':rentabilidade_saida' => $data['rentabilidade_saida'] ?? 0,
                ':conta_comprador' => $data['conta_comprador'] ?? null,
                ':nome_comprador' => $data['nome_comprador'] ?? null,
                ':taxa_entrada' => $data['taxa_entrada'] ?? 0,
                ':valor_bruto_entrada' => $data['valor_bruto_entrada'] ?? 0,
                ':preco_unitario_entrada' => $data['preco_unitario_entrada'] ?? 0,
                ':valor_plataforma' => $data['valor_plataforma'] ?? 0,
                ':corretagem_assessor' => $data['corretagem_assessor'] ?? $data['corretagem'] ?? 0,
                ':roa_assessor' => $data['roa_assessor'] ?? $data['roa'] ?? 0,
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erro ao salvar negociação: ' . $e->getMessage());
            error_log('Dados: ' . print_r($data, true));
            return 0;
        }
    }

    /**
     * NOVOS MÉTODOS: Buscar dados de negociações da tabela INTERMEDIACOES_TABLE;
     * com conversão automática de datas (AAAA-MM-DD → DD/MM/AAAA)
     * e valores (centavos → R$)
     */

    /**
     * Lista todas as negociações disponíveis na tabela INTERMEDIACOES_TABLE;
     * com dados convertidos para exibição
     */
    public function listarIntermedicoesDisponiveis(int $limit = 100): array {
        try {
            $sql = "SELECT 
                        id,
                        Conta as conta,
                        Nome as cliente,
                        Ativo as produto,
                        Estrategia as estrategia,
                        CNPJ as emissor,
                        Vencimento as vencimento,
                        Taxa_Compra as taxa,
                        Quantidade as quantidade,
                        Valor_Bruto as valor_bruto_centavos,
                        IR as ir_centavos,
                        Valor_Liquido as valor_liquido_centavos,
                        Data_Compra as data_compra,
                        Quantidade as quantidade_disponivel
                    FROM {$this->tableIntermediacao}
                    WHERE Quantidade > 0
                    ORDER BY Data_Compra DESC, Nome ASC
                    LIMIT :limit";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $negociacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Converte cada negociação
            return array_map(function($neg) {
                return $this->converterNegociacaoParaExibicao($neg);
            }, $negociacoes);
        } catch (PDOException $e) {
            error_log("Erro ao listar intermedições: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém uma intermedição específica pelo ID
     */
    public function obterIntermediacao(int $id): ?array {
        try {
            $sql = "SELECT 
                        id,
                        ID_Registro,
                        Conta as conta,
                        Nome as cliente,
                        Ativo as produto,
                        Estrategia as estrategia,
                        CNPJ as emissor,
                        Vencimento as vencimento,
                        Taxa_Compra as taxa,
                        Quantidade as quantidade,
                        Valor_Bruto as valor_bruto_centavos,
                        IR as ir_centavos,
                        Valor_Liquido as valor_liquido_centavos,
                        Data_Compra as data_compra,
                        Quantidade as quantidade_disponivel
                    FROM {$this->tableIntermediacao}
                    WHERE id = :id
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $negociacao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($negociacao) {
                return $this->converterNegociacaoParaExibicao($negociacao);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Erro ao obter intermedição ID $id: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualiza a quantidade disponível de uma intermedição após venda
     */
    public function atualizarQuantidadeDisponivel(int $id, int $quantidade_nova): bool {
        try {
            $sql = "UPDATE {$this->tableIntermediacao} 
                    SET Quantidade = :quantidade 
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':quantidade', max(0, $quantidade_nova), PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar quantidade da intermedição: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Converte dados da intermedição para exibição no formulário
     * - Datas: AAAA-MM-DD → DD/MM/AAAA
     * - Valores: centavos → R$ formatado
     * - Mantém valores originais em campos _centavos para uso em cálculos
     */
    private function converterNegociacaoParaExibicao(array $data): array {
        // Converte datas
        if (!empty($data['vencimento'])) {
            $data['vencimento_original'] = $data['vencimento'];
            $data['vencimento'] = $this->formatarData($data['vencimento']);
        }

        if (!empty($data['data_compra'])) {
            $data['data_compra_original'] = $data['data_compra'];
            $data['data_compra'] = $this->formatarData($data['data_compra']);
        }

        // Converte valores (centavos para reais formatado)
        if (isset($data['valor_bruto_centavos'])) {
            $data['valor_bruto'] = $this->formatarMoeda((int)$data['valor_bruto_centavos']);
        }

        if (isset($data['valor_liquido_centavos'])) {
            $data['valor_liquido'] = $this->formatarMoeda((int)$data['valor_liquido_centavos']);
        }

        if (isset($data['ir_centavos'])) {
            $data['ir'] = $this->formatarMoeda((int)$data['ir_centavos']);
        }

        if (isset($data['taxa'])) {
            $data['taxa'] = $this->formatarPorcentagem((float)$data['taxa']);
        }

        return $data;
    }

    /**
     * Formata data AAAA-MM-DD para DD/MM/AAAA
     */
    private function formatarData(string $data): string {
        if (empty($data)) {
            return '---';
        }

        try {
            $timestamp = strtotime($data);
            if ($timestamp === false) {
                return $data;
            }
            return date('d/m/Y', $timestamp);
        } catch (Exception $e) {
            return $data;
        }
    }

    /**
     * Formata valor em centavos como moeda brasileira
     * Exemplo: 51673367 → R$ 516.733,67
     */
    private function formatarMoeda(int $centavos): string {
        $reais = $centavos / 100;
        return number_format($reais, 2, ',', '.');
    }

    /**
     * Formata porcentagem
     * Exemplo: 250 → 2,50%
     */
    private function formatarPorcentagem(float $valor): string {
        if ($valor == 0) return '0,00%';
        $formatado = number_format($valor, 2, ',', '.');
        return $formatado . '%';
    }

    /**
     * Converte valores numéricos do banco para float em reais (tratando centavos ou reais)
     * Aceita: integer centavos (51673367), float em reais (51673.367), string numérica
     */
    public function toReaisFloat($valor): float {
        if ($valor === null || $valor === '') return 0.0;
        // Se for string com vírgula, substituir
        if (is_string($valor)) {
            $v = str_replace(['.', ','], ['', '.'], $valor);
            if (is_numeric($v)) $valor = $v;
        }

        $f = (float)$valor;
        // Heurística: valores inteiros grandes provavelmente estão em centavos
        if (floor($f) == $f && abs($f) > 1000) {
            return $f / 100.0;
        }
        return $f;
    }

    /**
     * Calcula preço unitário (vendedor) a partir do valor líquido total e quantidade
     */
    public function calcularPrecoUnitarioSaida(float $valor_liquido_saida, int $quantidade): float {
        if ($quantidade <= 0) return 0.0;
        return $valor_liquido_saida / $quantidade;
    }

    /**
     * Calcula ganho do vendedor: diferença entre valor líquido recebido e custo importado
     */
    public function calcularGanhoSaida(float $valor_liquido_saida, float $custo_importado_total): float {
        return $valor_liquido_saida - $custo_importado_total;
    }

    /**
     * Calcula rentabilidade em porcentagem
     */
    public function calcularRentabilidade(float $ganho, float $custo_importado_total): float {
        if ($custo_importado_total <= 0) return 0.0;
        return ($ganho / $custo_importado_total) * 100.0;
    }

    /**
     * Corretagem do assessor (por enquanto, valor informado como "valor plataforma")
     */
    public function calcularCorretagem(float $valor_plataforma): float {
        return $valor_plataforma;
    }

    /**
     * Calcula ROA (%) do assessor: corretagem / valor de entrada
     */
    public function calcularRoa(float $corretagem, float $valor_entrada): float {
        if ($valor_entrada <= 0) return 0.0;
        return ($corretagem / $valor_entrada) * 100.0;
    }

    /**
     * Transfere a quantidade negociada para INTERMEDIACOES_TABLE_NEGOCIADA
     * com proporção dos valores (Valor_Bruto, IR, IOF, Valor_Liquido).
     * Este método é chamado APÓS um INSERT bem-sucedido em NEGOCIACOES.
     * 
     * @param int $negociacao_id ID da negociação inserida em NEGOCIACOES
     * @param string|int $id_registro_source ID_Registro da origem em INTERMEDIACOES_TABLE
     * @param float $quantidade_negociada Quantidade que foi negociada
     * @return bool true se transferência bem-sucedida
     */
    public function transferirParaNegociada(int $negociacao_id, $id_registro_source, float $quantidade_negociada): bool {
        if (!$id_registro_source || $quantidade_negociada <= 0) {
            error_log("transferirParaNegociada: ID_Registro_Source vazio ou quantidade inválida. source={$id_registro_source}, qty={$quantidade_negociada}");
            return false;
        }

        try {
            // 1) Busca o registro de origem
            $sqlSource = "SELECT Conta, Nome, Mercado, Sub_Mercado, Ativo, Produto, CNPJ, Emissor,
                            Data_Compra, Taxa_Compra, Taxa_Emissao, Vencimento, Quantidade,
                            Valor_Bruto, IR, IOF, Valor_Liquido, Estrategia, Escritorio,
                            Data_Registro, Data_Cotizacao_Prev, Tipo_Plano
                         FROM {$this->tableIntermediacao}
                         WHERE ID_Registro = :id_registro_source
                         LIMIT 1";

            $stmtSource = $this->pdo->prepare($sqlSource);
            $stmtSource->execute([':id_registro_source' => $id_registro_source]);
            $source = $stmtSource->fetch(\PDO::FETCH_ASSOC);

            if (!$source) {
                error_log("transferirParaNegociada: Registro origem não encontrado. ID_Registro={$id_registro_source}");
                return false;
            }

            // 2) Calcula proporção: quanto negociar / quantidade original
            $src_quantidade = (float)($source['Quantidade'] ?? 0);
            if ($src_quantidade <= 0) {
                error_log("transferirParaNegociada: Quantidade origem é zero ou negativa. ID_Registro={$id_registro_source}");
                return false;
            }

            $ratio = $quantidade_negociada / $src_quantidade;
            // Garante que não copiamos mais que a quantidade original
            $qtd_a_copiar = min($quantidade_negociada, $src_quantidade);

            // 3) Insere proporcional em INTERMEDIACOES_TABLE_NEGOCIADA
            $sqlInsert = "INSERT INTO INTERMEDIACOES_TABLE_NEGOCIADA
                         (Conta, Nome, Mercado, Sub_Mercado, Ativo, Produto, CNPJ, Emissor,
                          Data_Compra, Taxa_Compra, Taxa_Emissao, Vencimento, Quantidade,
                          Valor_Bruto, IR, IOF, Valor_Liquido, Estrategia, Escritorio,
                          Data_Registro, Data_Cotizacao_Prev, Tipo_Plano, ID_Registro, Data_Importacao)
                         VALUES
                         (:conta, :nome, :mercado, :sub_mercado, :ativo, :produto, :cnpj, :emissor,
                          :data_compra, :taxa_compra, :taxa_emissao, :vencimento, :quantidade,
                          :valor_bruto, :ir, :iof, :valor_liquido, :estrategia, :escritorio,
                          :data_registro, :data_cotizacao_prev, :tipo_plano, :id_registro_new, NOW())";

            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':conta' => $source['Conta'],
                ':nome' => $source['Nome'],
                ':mercado' => $source['Mercado'],
                ':sub_mercado' => $source['Sub_Mercado'],
                ':ativo' => $source['Ativo'],
                ':produto' => $source['Produto'],
                ':cnpj' => $source['CNPJ'],
                ':emissor' => $source['Emissor'],
                ':data_compra' => $source['Data_Compra'],
                ':taxa_compra' => $source['Taxa_Compra'],
                ':taxa_emissao' => $source['Taxa_Emissao'],
                ':vencimento' => $source['Vencimento'],
                ':quantidade' => $qtd_a_copiar,
                ':valor_bruto' => round((float)($source['Valor_Bruto'] ?? 0) * $ratio, 2),
                ':ir' => round((float)($source['IR'] ?? 0) * $ratio, 2),
                ':iof' => round((float)($source['IOF'] ?? 0) * $ratio, 2),
                ':valor_liquido' => round((float)($source['Valor_Liquido'] ?? 0) * $ratio, 2),
                ':estrategia' => $source['Estrategia'],
                ':escritorio' => $source['Escritorio'],
                ':data_registro' => $source['Data_Registro'],
                ':data_cotizacao_prev' => $source['Data_Cotizacao_Prev'],
                ':tipo_plano' => $source['Tipo_Plano'],
                ':id_registro_new' => $negociacao_id, // usa o ID da negociação como ID_Registro na tabela negociada
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Erro ao transferir para NEGOCIADA: " . $e->getMessage());
            return false;
        }
    }
}
