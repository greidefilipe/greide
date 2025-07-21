<?php
header('Content-Type: application/json');
require_once '../../conexao.php'; // Ajuste o caminho se necessário

// Opcional: receber um termo de busca para filtrar
$query = $_GET['query'] ?? '';
$searchTerm = "%{$query}%";

try {
    // Consulta SQL para buscar apenas multas aguardando aprovação
    $sql = "SELECT 
                m.id,
                m.data_hora_infracao,
                m.valor_multa,
                m.status_pagamento,
                m.local_infracao,
                m.comprovante_pagamento,
                v.placa AS veiculo_placa,
                COALESCE(u.name, m.usuario_nome_digitado) AS condutor_nome,
                ai.codigo_auto AS infracao_codigo,
                ai.descricao AS infracao_descricao
            FROM multas AS m
            LEFT JOIN veiculos AS v ON m.veiculo_id = v.id
            LEFT JOIN usuarios AS u ON m.usuario_id = u.id
            LEFT JOIN autos_infracao AS ai ON m.auto_infracao_id = ai.id
            WHERE 
                m.status_pagamento = 'Aguardando Aprovação'
                AND (v.placa LIKE ? OR u.name LIKE ? OR m.usuario_nome_digitado LIKE ?)
            ORDER BY m.data_hora_infracao DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $multas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $multas]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar multas: ' . $e->getMessage()]);
}

?>