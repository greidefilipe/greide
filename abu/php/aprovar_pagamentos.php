<?php
// Inclui a verificação de sessão e a conexão com o banco.
require_once 'verificar_sessao.php';

// --- BUSCA DADOS DO USUÁRIO LOGADO PARA O CABEÇALHO ---
$user_id = $_SESSION['user_id'];
$user_name = 'Usuário';
$user_profile_photo_path = '../img/avatar_padrao.png'; 

try {
    // Busca nome e foto do usuário no banco de dados
    $stmt = $conn->prepare("SELECT name, profile_photo FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = htmlspecialchars($user['name']);
        if (!empty($user['profile_photo'])) {
            $user_photo_db_path = '../../' . ltrim($user['profile_photo'], '/');
            if (file_exists($user_photo_db_path)) {
                $user_profile_photo_path = htmlspecialchars($user_photo_db_path);
            }
        }
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovar Pagamentos - Painel</title>
    
    <link rel="stylesheet" href="../css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../img/logo-sorriso.png" alt="Logo" class="sidebar-logo">
            <button class="sidebar-close-btn"><i class="fas fa-times"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-edit fa-fw"></i>
                <span>Cadastrar Multa</span>
            </a>
            <a href="consultar_multas.php" class="nav-link">
                <i class="fas fa-search fa-fw"></i>
                <span>Consultar Multas</span>
            </a>
            <a href="#" class="nav-link active">
                <i class="fas fa-check-circle fa-fw"></i>
                <span>Aprovar Pagamentos</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="nav-link">
                <i class="fas fa-sign-out-alt fa-fw"></i>
                <span>Sair</span>
            </a>
        </div>
    </aside>

    <div class="main-content">
        <header class="main-header">
            <div class="header-left">
                <button id="sidebar-toggle" class="header-button">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="header-title">Aprovar Pagamentos</h2>
            </div>
            <div class="header-right">
                <button id="theme-toggle" class="header-button" title="Alterar Tema">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-profile">
                    <span class="user-name">Olá, <?php echo $user_name; ?></span>
                    <img src="<?php echo $user_profile_photo_path; ?>" alt="Avatar" class="user-avatar">
                </div>
            </div>
        </header>

        <main class="page-content">
            <div class="card">
                <div class="form-section">
                    <h3 class="form-section-title"><i class="fas fa-filter icon"></i>Filtro de Busca</h3>
                    <div class="form-group">
                        <label for="busca-multa-aprovacao">Buscar por Placa, Condutor ou Código da Infração</label>
                        <input type="text" id="busca-multa-aprovacao" class="form-input" placeholder="Digite para buscar..." autocomplete="off">
                    </div>
                </div>

                <div id="loading-spinner" style="display: none; text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin fa-3x" style="color: var(--primary-color);"></i>
                    <p style="margin-top: 1rem; font-weight: 500;">Carregando dados...</p>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Placa</th>
                                <th>Condutor</th>
                                <th>Código Infração</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-aprovacao-body"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para visualizar detalhes da multa -->
    <div id="modal-detalhes-multa" class="modal">
        <div class="modal-content modal-large">
            <span class="modal-close">&times;</span>
            <h2 class="modal-title"><i class="fas fa-info-circle"></i> Detalhes da Multa</h2>
            <div id="detalhes-multa-content">
                <!-- Conteúdo será carregado dinamicamente -->
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/script.js"></script> 
    <script src="../js/aprovacao.js"></script> 
</body>
</html>