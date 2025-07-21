<?php
// Inicia a sessão
session_start();

// Inclui o arquivo de conexão
require_once __DIR__ . '/../../conexao.php';

// Verifica se o usuário está logado e se tem a role 'multas'
// IMPORTANTE: Adicione 'multas' à lista de ENUM da sua coluna 'role' na tabela 'usuarios'.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'multas') {
    // Se não tiver, redireciona para a página de login
    header('Location: ../login.php'); // Ajuste o caminho para sua página de login
    exit();
}

// Guarda o ID do usuário logado para usar no cadastro da multa
$usuario_logado_id = $_SESSION['user_id'];
?>