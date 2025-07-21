<?php
header('Content-Type: application/json');

// CORREÇÃO: O caminho foi ajustado para encontrar o arquivo de conexão corretamente.
require_once '../../conexao.php'; 

$query = $_GET['query'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$searchTerm = "%{$query}%";

try {
    // Usando a consulta que ignora o status 'is_active' para fins de teste
    $stmt = $conn->prepare("SELECT id, name, cpf FROM usuarios WHERE (name LIKE ? OR cpf LIKE ?) LIMIT 10");
    $stmt->execute([$searchTerm, $searchTerm]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($usuarios);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro em buscar_usuarios.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Erro interno no servidor."]);
}
?>