<?php
// --- ARQUIVO DE TESTE ---

// Teste 1: Verifica se o script é acessado
// die("TESTE 1: O arquivo buscar_veiculos.php foi alcançado com sucesso.");

// Inclui a conexão
require_once '../../conexao.php';

// Teste 2: Se você vê esta mensagem, significa que a conexão não gerou erro fatal.
// die("TESTE 2: O arquivo de conexão foi incluído sem erros.");

$termo = $_GET['query'] ?? '';

// Teste 3: Verifica se o termo de busca está chegando
// die("TESTE 3: O termo recebido foi: " . $termo);

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

$searchTerm = "%{$termo}%";

// Teste 4: Mostra a consulta SQL que será executada.
// die("TESTE 4: A consulta SQL é: SELECT id, veiculo, placa FROM veiculos WHERE veiculo LIKE '$searchTerm' OR placa LIKE '$searchTerm' LIMIT 10");

try {
    $stmt = $conn->prepare("SELECT id, veiculo, placa FROM veiculos WHERE veiculo LIKE ? OR placa LIKE ? LIMIT 10");
    $stmt->execute([$searchTerm, $searchTerm]);
    $veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os dados em formato JSON
    echo json_encode($veiculos);

} catch (PDOException $e) {
    // Se houver um erro na consulta, ele será capturado aqui.
    http_response_code(500);
    // Retorna a mensagem de erro específica para depuração
    echo json_encode(['status' => 'error', 'message' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>