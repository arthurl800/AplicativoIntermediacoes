-- ============================================================================
-- AUDITORIA E TRIGGERS PARA RASTREAMENTO DE NEGOCIAÇÕES
-- ============================================================================

-- Tabela de Auditoria: Rastreia cada inserção/atualização/exclusão em NEGOCIACOES
CREATE TABLE IF NOT EXISTS NEGOCIACOES_AUDITORIA (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    negociacao_id INT NOT NULL,
    acao VARCHAR(10) NOT NULL COMMENT 'INSERT, UPDATE, DELETE',
    usuario_ip VARCHAR(45) DEFAULT NULL COMMENT 'IP do usuário que realizou a ação',
    usuario_name VARCHAR(255) DEFAULT NULL COMMENT 'Usuário que realizou a ação',
    data_acao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dados_antes JSON DEFAULT NULL COMMENT 'Valores antes da alteração (para UPDATE)',
    dados_depois JSON DEFAULT NULL COMMENT 'Valores depois da alteração',
    descricao_mudanca TEXT DEFAULT NULL COMMENT 'Resumo da mudança realizada',
    INDEX idx_negociacao_id (negociacao_id),
    INDEX idx_data_acao (data_acao),
    INDEX idx_acao (acao),
    FOREIGN KEY (negociacao_id) REFERENCES NEGOCIACOES(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger: Registra inserção em NEGOCIACOES
DELIMITER //

CREATE TRIGGER NEGOCIACOES_AUDIT_INSERT
AFTER INSERT ON NEGOCIACOES
FOR EACH ROW
BEGIN
    INSERT INTO NEGOCIACOES_AUDITORIA (
        negociacao_id,
        acao,
        usuario_name,
        data_acao,
        dados_depois,
        descricao_mudanca
    ) VALUES (
        NEW.id,
        'INSERT',
        SESSION_USER(),
        NOW(),
        JSON_OBJECT(
            'Data_Registro', NEW.Data_Registro,
            'Conta_Vendedor', NEW.Conta_Vendedor,
            'Nome_Vendedor', NEW.Nome_Vendedor,
            'Produto', NEW.Produto,
            'Estrategia', NEW.Estrategia,
            'Quantidade_negociada', NEW.Quantidade_negociada,
            'Valor_Bruto_Saida', NEW.Valor_Bruto_Saida,
            'Valor_Liquido_Saida', NEW.Valor_Liquido_Saida,
            'Conta_Comprador', NEW.Conta_Comprador,
            'Nome_Comprador', NEW.Nome_Comprador,
            'Valor_Bruto_Entrada', NEW.Valor_Bruto_Entrada,
            'Corretagem_Assessor', NEW.Corretagem_Assessor
        ),
        CONCAT(
            'Nova negociação registrada: ',
            NEW.Quantidade_negociada,
            ' unidades de ',
            NEW.Produto,
            ' vendidas por R$ ',
            FORMAT(NEW.Valor_Bruto_Saida, 2),
            ' para conta ',
            NEW.Conta_Comprador
        )
    );
END //

-- Trigger: Registra atualização em NEGOCIACOES
CREATE TRIGGER NEGOCIACOES_AUDIT_UPDATE
AFTER UPDATE ON NEGOCIACOES
FOR EACH ROW
BEGIN
    INSERT INTO NEGOCIACOES_AUDITORIA (
        negociacao_id,
        acao,
        usuario_name,
        data_acao,
        dados_antes,
        dados_depois,
        descricao_mudanca
    ) VALUES (
        NEW.id,
        'UPDATE',
        SESSION_USER(),
        NOW(),
        JSON_OBJECT(
            'Quantidade_negociada', OLD.Quantidade_negociada,
            'Valor_Bruto_Saida', OLD.Valor_Bruto_Saida,
            'Valor_Liquido_Saida', OLD.Valor_Liquido_Saida,
            'Valor_Bruto_Entrada', OLD.Valor_Bruto_Entrada,
            'Corretagem_Assessor', OLD.Corretagem_Assessor
        ),
        JSON_OBJECT(
            'Quantidade_negociada', NEW.Quantidade_negociada,
            'Valor_Bruto_Saida', NEW.Valor_Bruto_Saida,
            'Valor_Liquido_Saida', NEW.Valor_Liquido_Saida,
            'Valor_Bruto_Entrada', NEW.Valor_Bruto_Entrada,
            'Corretagem_Assessor', NEW.Corretagem_Assessor
        ),
        'Negociação atualizada'
    );
END //

-- Trigger: Registra exclusão em NEGOCIACOES
CREATE TRIGGER NEGOCIACOES_AUDIT_DELETE
BEFORE DELETE ON NEGOCIACOES
FOR EACH ROW
BEGIN
    INSERT INTO NEGOCIACOES_AUDITORIA (
        negociacao_id,
        acao,
        usuario_name,
        data_acao,
        dados_antes,
        descricao_mudanca
    ) VALUES (
        OLD.id,
        'DELETE',
        SESSION_USER(),
        NOW(),
        JSON_OBJECT(
            'Data_Registro', OLD.Data_Registro,
            'Conta_Vendedor', OLD.Conta_Vendedor,
            'Nome_Vendedor', OLD.Nome_Vendedor,
            'Produto', OLD.Produto,
            'Quantidade_negociada', OLD.Quantidade_negociada,
            'Valor_Bruto_Saida', OLD.Valor_Bruto_Saida
        ),
        'Negociação deletada'
    );
END //

DELIMITER ;

-- ============================================================================
-- VIEWS ANALÍTICAS PARA DASHBOARD
-- ============================================================================

-- View: Estatísticas por Operador
CREATE OR REPLACE VIEW VW_NEGOCIACOES_POR_OPERADOR AS
SELECT 
    na.usuario_name AS operador,
    COUNT(DISTINCT n.id) AS total_negociacoes,
    SUM(n.Quantidade_negociada) AS quantidade_total,
    SUM(n.Valor_Bruto_Saida) AS valor_saida_total,
    SUM(n.Valor_Liquido_Saida) AS valor_liquido_total,
    SUM(n.Corretagem_Assessor) AS corretagem_total,
    AVG(n.Roa_Assessor) AS roa_medio,
    MIN(n.Data_Registro) AS primeira_negociacao,
    MAX(n.Data_Registro) AS ultima_negociacao
FROM NEGOCIACOES_AUDITORIA na
LEFT JOIN NEGOCIACOES n ON na.negociacao_id = n.id
WHERE na.acao = 'INSERT'
GROUP BY na.usuario_name
ORDER BY total_negociacoes DESC;

-- View: Estatísticas por Produto
CREATE OR REPLACE VIEW VW_NEGOCIACOES_POR_PRODUTO AS
SELECT 
    n.Produto,
    COUNT(*) AS total_negociacoes,
    SUM(n.Quantidade_negociada) AS quantidade_total,
    SUM(n.Valor_Bruto_Saida) AS valor_saida_total,
    SUM(n.Corretagem_Assessor) AS corretagem_total,
    AVG(n.Roa_Assessor) AS roa_medio,
    AVG(n.Rentabilidade_Saida) AS rentabilidade_media
FROM NEGOCIACOES n
GROUP BY n.Produto
ORDER BY valor_saida_total DESC;

-- View: Estatísticas por Data (Diário)
CREATE OR REPLACE VIEW VW_NEGOCIACOES_POR_DATA AS
SELECT 
    DATE(n.Data_Registro) AS data_negociacao,
    COUNT(*) AS total_negociacoes,
    SUM(n.Quantidade_negociada) AS quantidade_total,
    SUM(n.Valor_Bruto_Saida) AS valor_saida_total,
    SUM(n.Corretagem_Assessor) AS corretagem_total,
    AVG(n.Roa_Assessor) AS roa_medio
FROM NEGOCIACOES n
GROUP BY DATE(n.Data_Registro)
ORDER BY data_negociacao DESC;

-- View: Resumo Executivo
CREATE OR REPLACE VIEW VW_RESUMO_EXECUTIVO_NEGOCIACOES AS
SELECT 
    COUNT(*) AS total_negociacoes,
    SUM(n.Quantidade_negociada) AS quantidade_total,
    SUM(n.Valor_Bruto_Saida) AS valor_saida_total,
    SUM(n.Valor_Liquido_Saida) AS valor_liquido_total,
    SUM(n.Corretagem_Assessor) AS corretagem_total,
    AVG(n.Roa_Assessor) AS roa_medio,
    AVG(n.Rentabilidade_Saida) AS rentabilidade_media,
    MAX(n.Data_Registro) AS ultima_negociacao,
    COUNT(DISTINCT n.Conta_Vendedor) AS clientes_unicos,
    COUNT(DISTINCT n.Produto) AS produtos_unicos
FROM NEGOCIACOES n;
