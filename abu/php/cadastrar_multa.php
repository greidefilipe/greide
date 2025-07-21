<?php
header('Content-Type: application/json');

require_once 'verificar_sessao.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nenhum dado recebido.']);
    exit;
}

// Recebendo todos os dados, incluindo o novo campo de boleto
$veiculo_id = $data['veiculo_id'] ?? null;
$usuario_id = !empty($data['usuario_id']) ? $data['usuario_id'] : null;
$usuario_nome = $data['usuario_nome'] ?? null;
$auto_infracao_id = $data['auto_infracao_id'] ?? null;
$data_hora = $data['data_hora'] ?? null;
$local_infracao = $data['local_infracao'] ?? null;
$valor = $data['valor'] ?? null;
$anexos = $data['anexos'] ?? [];
$boleto_data = $data['boleto'] ?? null;

if (!$veiculo_id || !$usuario_nome || !$auto_infracao_id || !$data_hora || !$valor || !$local_infracao) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

$valor_formatado = str_replace(['.', ','], ['', '.'], $valor);

try {
    $conn->beginTransaction();

    $sql = "INSERT INTO multas (veiculo_id, usuario_id, usuario_nome_digitado, auto_infracao_id, data_hora_infracao, local_infracao, valor_multa, cadastrado_por_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([
        $veiculo_id,
        $usuario_id,
        $usuario_nome,
        $auto_infracao_id,
        $data_hora,
        $local_infracao,
        $valor_formatado,
        $usuario_logado_id 
    ]);

    $multa_id = $conn->lastInsertId();

    // Processamento dos anexos múltiplos
    $anexos_paths = [];
    if (!empty($anexos)) {
        $upload_dir = '../../uploads/anexos_multas/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                 throw new Exception("Falha ao criar o diretório de anexos.");
            }
        }
        foreach ($anexos as $anexo) {
            list(, $file_data) = explode(',', $anexo['content']);
            $decoded_file = base64_decode($file_data);
            $file_extension = pathinfo($anexo['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('multa_' . $multa_id . '_', true) . '.' . $file_extension;
            $upload_file_path = $upload_dir . $new_filename;
            if (file_put_contents($upload_file_path, $decoded_file)) {
                $anexos_paths[] = 'uploads/anexos_multas/' . $new_filename;
            }
        }
    }

    // Processamento do boleto único
    $boleto_db_path = null;
    if (!empty($boleto_data)) {
        $boleto_upload_dir = '../../uploads/boletos/';
        if (!is_dir($boleto_upload_dir)) {
            if (!mkdir($boleto_upload_dir, 0777, true)) {
                throw new Exception("Falha ao criar o diretório de boletos.");
            }
        }
        list(, $file_data) = explode(',', $boleto_data['content']);
        $decoded_file = base64_decode($file_data);
        $file_extension = pathinfo($boleto_data['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('boleto_' . $multa_id . '_', true) . '.' . $file_extension;
        $upload_file_path = $boleto_upload_dir . $new_filename;
        if (file_put_contents($upload_file_path, $decoded_file)) {
            $boleto_db_path = 'uploads/boletos/' . $new_filename;
        }
    }

    // Atualiza a multa com os caminhos dos arquivos, se houver
    if (!empty($anexos_paths) || !empty($boleto_db_path)) {
        $anexos_json = !empty($anexos_paths) ? json_encode($anexos_paths) : null;
        
        $sql_update = "UPDATE multas SET anexos_json = ?, caminho_boleto = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([$anexos_json, $boleto_db_path, $multa_id]);
    }
    
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Multa cadastrada com sucesso!']);

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    error_log("Erro ao cadastrar multa: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ocorreu um erro no servidor: ' . $e->getMessage()]);
}
?>