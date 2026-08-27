<?php

/**
 * Model de Serviço — camada de acesso a dados (DAO).
 *
 * Objetivo: isolar toda a persistência em prepared statements (PDO), evitando
 * SQL injection e centralizando as regras de comissão/status neste único ponto.
 * Optou-se por métodos estáticos pela simplicidade — não há estado interno
 * relevante entre requisições.
 */
require_once(__DIR__ . '/../core/Database.php');

class Servico {

    /**
     * Lista serviços com filtros opcionais e JOIN no usuário.
     *
     * POR QUE "1=1"? Montamos o WHERE dinamicamente, concatenando cláusulas
     * conforme os filtros recebidos. O "1=1" evita rastrear se já adicionamos a
     * primeira condição, mantendo o SQL sempre válido. Todos os valores são
     * injetados como parâmetros (nunca concatenados), tornando a consulta imune
     * a SQL injection.
     *
     * @param array<string,string> $filtros Chaves: descricao, status, usuario,
     *                                       data_inicio, data_fim.
     * @return array<int,array<string,mixed>> Serviços com dados do usuário
     *                                        (nome/email) já resolvidos.
     */
    public static function listar(array $filtros = []) {
        $db = Database::getConnection();
        $sql = "SELECT s.*,
                       CASE WHEN s.finished_at IS NOT NULL THEN 'Finalizado' ELSE 'Pendente' END AS status,
                       u.name AS nome, u.email AS usuario_email
                FROM servicos s
                JOIN usuarios u ON s.user_id_user = u.id_user
                WHERE 1=1";
        $params = [];

        // Filtro por nome do serviço: busca parcial (LIKE).
        if (!empty($filtros['descricao'])) {
            $sql .= " AND s.description LIKE ?";
            $params[] = '%' . $filtros['descricao'] . '%';
        }
        // Filtro por status: o status é DERIVADO da data de finalização
        // (regra de negócio: tem finished_at => Finalizado; não tem => Pendente).
        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'Pendente') {
                $sql .= " AND s.finished_at IS NULL";
            } elseif ($filtros['status'] === 'Finalizado') {
                $sql .= " AND s.finished_at IS NOT NULL";
            }
        }
        // Filtro por usuário: busca parcial no nome, no JOIN.
        if (!empty($filtros['usuario'])) {
            $sql .= " AND u.name LIKE ?";
            $params[] = '%' . $filtros['usuario'] . '%';
        }
        // Período: compara apenas a DATA (de criação), ignorando a hora.
        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(s.created_at) >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(s.created_at) <= ?";
            $params[] = $filtros['data_fim'];
        }

        // Mais recentes primeiro — ordem esperada pelo dashboard.
        $sql .= " ORDER BY s.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Últimos serviços ainda "Pendente" (caixa destacada do dashboard).
     *
     * PONTO SÊNIOR (LIMIT): o parâmetro do LIMIT precisa ser vinculado
     * explicitamente como inteiro (PDO::PARAM_INT). Se passá-lo dentro do array
     * de execute(), o PDO o envia como string ('5') e o MySQL lança erro de
     * sintaxe — bug real que já ocorreu aqui. Por isso o bindValue em separado.
     */
    public static function listarPendentes($limite = 5) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT s.*,
                                     CASE WHEN s.finished_at IS NOT NULL THEN 'Finalizado' ELSE 'Pendente' END AS status,
                                     u.name AS nome
                              FROM servicos s
                              JOIN usuarios u ON s.user_id_user = u.id_user
                              WHERE s.finished_at IS NULL
                              ORDER BY s.created_at DESC
                              LIMIT ?");
        $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um serviço pelo id, já com o e-mail do usuário (usado para disparar
     * o e-mail de finalização sem uma segunda consulta).
     */
    public static function buscarPorId($idService) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT s.*,
                                     CASE WHEN s.finished_at IS NOT NULL THEN 'Finalizado' ELSE 'Pendente' END AS status,
                                     u.name AS nome, u.email AS usuario_email
                              FROM servicos s
                              JOIN usuarios u ON s.user_id_user = u.id_user
                              WHERE s.id_service = ?");
        $stmt->execute([$idService]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um serviço. Regra de negócio: todo serviço novo nasce com status
     * "Pendente"; só o fluxo de finalização altera esse status.
     */
    public static function adicionar($descricao, $valor, $usuarioId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO servicos (description, price, status, user_id_user)
                              VALUES (?, ?, 'Pendente', ?)");
        return $stmt->execute([$descricao, $valor, $usuarioId]);
    }

    public static function excluir($idService) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM servicos WHERE id_service = ?");
        return $stmt->execute([$idService]);
    }

    /**
     * Atualiza descrição e valor, marcando update_at (auditoria). Não mexe em
     * status/comissão — esses campos têm fluxo próprio (ver finalizar()).
     */
    public static function atualizar($idService, $descricao, $valor) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE servicos
                              SET description = ?, price = ?, update_at = NOW()
                              WHERE id_service = ?");
        return $stmt->execute([$descricao, $valor, $idService]);
    }

    /**
     * Regra de comissão progressiva por faixa de valor.
     *   ≤ R$1.000,00  → 5%
     *   ≤ R$10.000,00 → 10%
     *   acima de R$10.000 → 20%
     *
     * Decisão sênior: as condições são checadas do maior para o menor valor.
     * Se começássemos pelo menor, um R$50.000 não cairia nas faixas seguintes.
     * O round(…, 2) evita dízimas de ponto flutuante em valores monetários.
     */
    public static function calcularComissao($valor) {
        $valor = (float)$valor;
        if ($valor > 10000) {
            return round($valor * 0.20, 2);
        }
        if ($valor > 1000) {
            return round($valor * 0.10, 2);
        }
        return round($valor * 0.05, 2);
    }

    /**
     * Finaliza um serviço: grava data de finalização (finished_at), status e comissão.
     *
     * Retorna os dados do serviço finalizado (com comissão) e `null` se já estiver
     * finalizado ou inexistente — o controller usa esse retorno para decidir se
     * envia e-mail e o que exibir ao usuário.
     *
     */
    public static function finalizar($idService) {
        $servico = self::buscarPorId($idService);
        // Se já tem data de finalização, já está finalizado (regra do negócio).
        if (!$servico || $servico['finished_at'] !== null) {
            return null;
        }
        $comissao = self::calcularComissao($servico['price']);

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE servicos
                              SET status = 'Finalizado', finished_at = NOW(),
                                  commission_user = ?, update_at = NOW()
                              WHERE id_service = ? AND finished_at IS NULL");
        $ok = $stmt->execute([$comissao, $idService]);
        if (!$ok) {
            return null;
        }
        $servico['status'] = 'Finalizado';
        $servico['commission_user'] = $comissao;
        return $servico;
    }
}
