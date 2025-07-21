<?php
header('Content-Type: application/json');
require_once '../../conexao.php'; // Conexão com o banco de dados

$query = $_GET['query'] ?? '';
$searchTerm = "%{$query}%";

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // CORREÇÃO: Alterada a variável de $pdo para $conn para padronização.
    $stmt = $conn->prepare("SELECT id, codigo_auto, descricao FROM autos_infracao WHERE codigo_auto LIKE ? OR descricao LIKE ? LIMIT 10");
    $stmt->execute([$searchTerm, $searchTerm]);
    $autos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($autos);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro em buscar_autos.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Erro interno no servidor."]);
}
?>