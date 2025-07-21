<?php
header('Content-Type: application/json');
require_once 'verificar_sessao.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido.']);
    exit;
}

$multa_id = $data['multa_id'] ?? null;
$acao = $data['acao'] ?? null; // 'aprovar' ou 'recusar'

if (!$multa_id || !$acao) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID da multa e ação são obrigatórios.']);
    exit;
}

// Define o novo status baseado na ação
$novo_status = '';
if ($acao === 'aprovar') {
    $novo_status = 'Pagamento Aprovado';
} elseif ($acao === 'recusar') {
    $novo_status = 'Pagamento Recusado';
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ação inválida. Use "aprovar" ou "recusar".']);
    exit;
}

try {
    $conn->beginTransaction();

    // Primeiro verifica se a coluna status_pagamento existe
    try {
        // Tenta fazer um UPDATE simples para verificar se a coluna existe
        $sql_test = "SELECT COUNT(*) FROM multas WHERE 1=0 AND status_pagamento IS NULL";
        $conn->query($sql_test);
        
        // Se chegou aqui, a coluna existe
        $sql = "UPDATE multas SET status_pagamento = ? WHERE id = ? AND COALESCE(status_pagamento, '') IN ('', 'Aguardando Aprovação')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$novo_status, $multa_id]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Multa não encontrada ou não está aguardando aprovação.');
        }

        $conn->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => ($acao === 'aprovar') ? 'Pagamento aprovado com sucesso!' : 'Pagamento recusado com sucesso!'
        ]);
        
    } catch (PDOException $e_coluna) {
        // A coluna não existe ainda
        $conn->rollback();
        throw new Exception('Funcionalidade de aprovação requer atualização do banco de dados. Coluna status_pagamento não encontrada.');
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>