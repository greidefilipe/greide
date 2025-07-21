<?php
header('Content-Type: application/json');
require_once '../../conexao.php'; // Ajuste o caminho se necessário

// Opcional: receber um termo de busca para filtrar
$query = $_GET['query'] ?? '';
$searchTerm = "%{$query}%";

try {
    // Consulta SQL que junta as tabelas para obter nomes em vez de IDs
    // Usa COALESCE para campos que podem não existir ainda
    $sql = "SELECT 
                m.id,
                m.data_hora_infracao,
                m.valor_multa,
                COALESCE(m.status_pagamento, 'Pendente') as status_pagamento,
                COALESCE(m.local_infracao, '') as local_infracao,
                COALESCE(m.comprovante_pagamento, '') as comprovante_pagamento,
                v.placa AS veiculo_placa,
                COALESCE(u.name, m.usuario_nome_digitado) AS condutor_nome,
                ai.codigo_auto AS infracao_codigo,
                COALESCE(ai.descricao, '') AS infracao_descricao
            FROM multas AS m
            LEFT JOIN veiculos AS v ON m.veiculo_id = v.id
            LEFT JOIN usuarios AS u ON m.usuario_id = u.id
            LEFT JOIN autos_infracao AS ai ON m.auto_infracao_id = ai.id
            WHERE 
                (v.placa LIKE ? OR u.name LIKE ? OR m.usuario_nome_digitado LIKE ?)
            ORDER BY m.data_hora_infracao DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $multas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $multas]);

} catch (PDOException $e) {
    // Se der erro (ex: coluna não existe), tenta query mais simples
    try {
        $sql_simples = "SELECT 
                    m.id,
                    m.data_hora_infracao,
                    m.valor_multa,
                    '' as status_pagamento,
                    COALESCE(m.local_infracao, '') as local_infracao,
                    '' as comprovante_pagamento,
                    v.placa AS veiculo_placa,
                    COALESCE(u.name, m.usuario_nome_digitado) AS condutor_nome,
                    ai.codigo_auto AS infracao_codigo,
                    COALESCE(ai.descricao, '') AS infracao_descricao
                FROM multas AS m
                LEFT JOIN veiculos AS v ON m.veiculo_id = v.id
                LEFT JOIN usuarios AS u ON m.usuario_id = u.id
                LEFT JOIN autos_infracao AS ai ON m.auto_infracao_id = ai.id
                WHERE 
                    (v.placa LIKE ? OR u.name LIKE ? OR m.usuario_nome_digitado LIKE ?)
                ORDER BY m.data_hora_infracao DESC";
                
        $stmt = $conn->prepare($sql_simples);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $multas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $multas]);
        
    } catch (PDOException $e2) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar multas: ' . $e2->getMessage()]);
    }
}

?>