<?php
header('Content-Type: application/json');

// Inclui a verificação de sessão e conexão
require_once '../../conexao.php'; // Garante que apenas usuários logados possam cadastrar

// Recebe os dados JSON enviados pelo frontend
$data = json_decode(file_get_contents('php://input'), true);

// Validação dos dados recebidos
if (!$data || !isset($data['codigo_auto']) || empty(trim($data['codigo_auto'])) || !isset($data['descricao']) || empty(trim($data['descricao']))) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Código e descrição são obrigatórios.']);
    exit;
}

// Atribuição de variáveis
$codigo_auto = trim($data['codigo_auto']);
$descricao = trim($data['descricao']);

try {
    // A conexão PDO é referenciada como $conn pelo arquivo conexao.php
    
    // Verifica se o código já existe para evitar duplicatas
    $sql_check = "SELECT id FROM autos_infracao WHERE codigo_auto = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$codigo_auto]);
    if ($stmt_check->fetch()) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Este código de infração já existe.']);
        exit;
    }

    // Insere o novo auto de infração
    $sql = "INSERT INTO autos_infracao (codigo_auto, descricao) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$codigo_auto, $descricao]);

    // Retorna o ID do item recém-criado para o JavaScript
    $lastId = $conn->lastInsertId();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Auto de Infração cadastrado com sucesso!',
        'id' => $lastId
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log("Erro ao cadastrar auto: " . $e->getMessage()); // Log do erro
    echo json_encode(['status' => 'error', 'message' => 'Falha ao cadastrar o auto de infração no banco de dados.']);
}
?>