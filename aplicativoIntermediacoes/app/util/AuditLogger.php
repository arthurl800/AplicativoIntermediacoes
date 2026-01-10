<?php
// app/util/AuditLogger.php

require_once dirname(__DIR__) . '/util/Database.php';
require_once dirname(__DIR__) . '/util/AuthManager.php';

/**
 * Classe responsável por registrar todas as ações dos usuários no sistema
 */
class AuditLogger {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retorna a instância única do AuditLogger (Singleton)
     */
    public static function getInstance(): AuditLogger {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registra uma ação no sistema
     * 
     * @param string $acao Ação realizada (ex: LOGIN, LOGOUT, CREATE, UPDATE, DELETE, VIEW)
     * @param string $modulo Módulo do sistema (ex: AUTENTICACAO, USUARIOS, UPLOAD, NEGOCIACOES, DADOS)
     * @param string $descricao Descrição detalhada da ação
     * @param array|null $dadosAntes Dados antes da ação (para UPDATE/DELETE)
     * @param array|null $dadosDepois Dados depois da ação (para CREATE/UPDATE)
     * @param int|null $usuarioId ID do usuário (se null, pega da sessão)
     * @param string|null $usuarioNome Nome do usuário (se null, pega da sessão)
     * @return bool
     */
    public function log(
        string $acao,
        string $modulo,
        string $descricao,
        ?array $dadosAntes = null,
        ?array $dadosDepois = null,
        ?int $usuarioId = null,
        ?string $usuarioNome = null
    ): bool {
        try {
            // Obtém informações do usuário da sessão se não fornecidas
            if ($usuarioId === null || $usuarioNome === null) {
                $authManager = new AuthManager();
                $currentUser = $authManager->getCurrentUser();
                $usuarioId = $currentUser['id'] ?? null;
                $usuarioNome = $currentUser['username'] ?? 'Sistema';
            }

            // Obtém informações da requisição
            $ipAddress = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Prepara dados JSON
            $dadosAntesJson = $dadosAntes ? json_encode($dadosAntes, JSON_UNESCAPED_UNICODE) : null;
            $dadosDepoisJson = $dadosDepois ? json_encode($dadosDepois, JSON_UNESCAPED_UNICODE) : null;

            // Insere no banco de dados
            $sql = "INSERT INTO AUDITORIA_SISTEMA 
                    (usuario_id, usuario_nome, acao, modulo, descricao, dados_antes, dados_depois, ip_address, user_agent) 
                    VALUES 
                    (:usuario_id, :usuario_nome, :acao, :modulo, :descricao, :dados_antes, :dados_depois, :ip_address, :user_agent)";

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':usuario_nome' => $usuarioNome,
                ':acao' => $acao,
                ':modulo' => $modulo,
                ':descricao' => $descricao,
                ':dados_antes' => $dadosAntesJson,
                ':dados_depois' => $dadosDepoisJson,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao registrar auditoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém o IP do cliente de forma segura
     */
    private function getClientIp(): ?string {
        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Remove portas se existirem
                $ip = explode(',', $ip)[0];
                $ip = trim($ip);
                
                // Valida o IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Registra login de usuário
     */
    public function logLogin(int $usuarioId, string $usuarioNome, bool $sucesso = true): bool {
        $acao = $sucesso ? 'LOGIN_SUCESSO' : 'LOGIN_FALHA';
        $descricao = $sucesso 
            ? "Usuário '{$usuarioNome}' realizou login com sucesso"
            : "Tentativa de login falhou para usuário '{$usuarioNome}'";
        
        return $this->log($acao, 'AUTENTICACAO', $descricao, null, null, $usuarioId, $usuarioNome);
    }

    /**
     * Registra logout de usuário
     */
    public function logLogout(int $usuarioId, string $usuarioNome): bool {
        $descricao = "Usuário '{$usuarioNome}' realizou logout";
        return $this->log('LOGOUT', 'AUTENTICACAO', $descricao, null, null, $usuarioId, $usuarioNome);
    }

    /**
     * Registra criação de registro
     */
    public function logCreate(string $modulo, string $descricao, array $dadosDepois): bool {
        return $this->log('CREATE', $modulo, $descricao, null, $dadosDepois);
    }

    /**
     * Registra atualização de registro
     */
    public function logUpdate(string $modulo, string $descricao, array $dadosAntes, array $dadosDepois): bool {
        return $this->log('UPDATE', $modulo, $descricao, $dadosAntes, $dadosDepois);
    }

    /**
     * Registra exclusão de registro
     */
    public function logDelete(string $modulo, string $descricao, array $dadosAntes): bool {
        return $this->log('DELETE', $modulo, $descricao, $dadosAntes, null);
    }

    /**
     * Registra visualização de dados
     */
    public function logView(string $modulo, string $descricao): bool {
        return $this->log('VIEW', $modulo, $descricao);
    }

    /**
     * Registra upload de arquivo
     */
    public function logUpload(string $nomeArquivo, int $totalRegistros): bool {
        $descricao = "Upload do arquivo '{$nomeArquivo}' com {$totalRegistros} registros";
        $dados = [
            'nome_arquivo' => $nomeArquivo,
            'total_registros' => $totalRegistros
        ];
        return $this->log('UPLOAD', 'UPLOAD', $descricao, null, $dados);
    }

    /**
     * Registra negociação realizada
     */
    public function logNegociacao(int $negociacaoId, array $dadosNegociacao): bool {
        $descricao = "Negociação realizada para intermediação ID {$negociacaoId}";
        return $this->log('NEGOCIACAO', 'NEGOCIACOES', $descricao, null, $dadosNegociacao);
    }

    /**
     * Busca logs de auditoria com filtros
     * 
     * @param array $filtros Filtros opcionais (usuario_id, modulo, acao, data_inicio, data_fim)
     * @param int $limit Limite de registros
     * @param int $offset Offset para paginação
     * @return array
     */
    public function buscarLogs(array $filtros = [], int $limit = 100, int $offset = 0): array {
        try {
            $where = [];
            $params = [];

            if (!empty($filtros['usuario_id'])) {
                $where[] = "usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtros['usuario_id'];
            }

            if (!empty($filtros['modulo'])) {
                $where[] = "modulo = :modulo";
                $params[':modulo'] = $filtros['modulo'];
            }

            if (!empty($filtros['acao'])) {
                $where[] = "acao = :acao";
                $params[':acao'] = $filtros['acao'];
            }

            if (!empty($filtros['data_inicio'])) {
                $where[] = "data_acao >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }

            if (!empty($filtros['data_fim'])) {
                $where[] = "data_acao <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $sql = "SELECT * FROM AUDITORIA_SISTEMA 
                    {$whereClause}
                    ORDER BY data_acao DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta total de logs com filtros
     */
    public function contarLogs(array $filtros = []): int {
        try {
            $where = [];
            $params = [];

            if (!empty($filtros['usuario_id'])) {
                $where[] = "usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtros['usuario_id'];
            }

            if (!empty($filtros['modulo'])) {
                $where[] = "modulo = :modulo";
                $params[':modulo'] = $filtros['modulo'];
            }

            if (!empty($filtros['acao'])) {
                $where[] = "acao = :acao";
                $params[':acao'] = $filtros['acao'];
            }

            if (!empty($filtros['data_inicio'])) {
                $where[] = "data_acao >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }

            if (!empty($filtros['data_fim'])) {
                $where[] = "data_acao <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $sql = "SELECT COUNT(*) as total FROM AUDITORIA_SISTEMA {$whereClause}";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Erro ao contar logs de auditoria: " . $e->getMessage());
            return 0;
        }
    }
}
