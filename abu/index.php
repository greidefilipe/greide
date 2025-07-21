<?php include 'php/verificar_sessao.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Multas - Prefeitura de Sorriso</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="img/logo-sorriso.png" alt="Logo" class="sidebar-logo">
            <button class="sidebar-close-btn"><i class="fas fa-times"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a href="../index.php" class="nav-link">
                <i class="fas fa-edit fa-fw"></i>
                <span>Cadastrar Multa</span>
            </a>
            <a href="php/consultar_multas.php" class="nav-link">
                <i class="fas fa-search fa-fw"></i>
                <span>Consultar Multas</span>
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
                <h2 class="header-title">Cadastrar Nova Multa</h2>
            </div>
            <div class="header-right">
                <button id="theme-toggle" class="header-button" title="Alterar Tema">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-profile">
                    <span class="user-name">Olá, Usuário</span>
                    <img src="https://i.pravatar.cc/40" alt="Avatar" class="user-avatar">
                </div>
            </div>
        </header>

        <main class="page-content">
            <div class="card">
                <form id="form-multa">
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-car-side icon"></i>1. Identificação do Veículo</h3>
                        <div class="form-group">
                            <label for="busca-veiculo">Buscar por Placa ou Modelo</label>
                            <input type="text" id="busca-veiculo" class="form-input" name="busca_veiculo_text" placeholder="Digite para buscar..." autocomplete="off">
                            <div id="veiculo-sugestoes" class="sugestoes-box"></div>
                            <div id="veiculo-selecionado" class="selecionado-info"></div>
                            <input type="hidden" id="veiculo_id" name="veiculo_id" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-user icon"></i>2. Identificação do Condutor</h3>
                        <div class="form-group">
                            <label for="busca-usuario">Buscar por Nome/CPF ou Digitar Nome</label>
                            <input type="text" id="busca-usuario" class="form-input" name="usuario_nome" placeholder="Selecione um condutor ou digite um novo nome..." autocomplete="off" required>
                            <div id="usuario-sugestoes" class="sugestoes-box"></div>
                            <div id="usuario-selecionado" class="selecionado-info"></div>
                            <input type="hidden" id="usuario_id" name="usuario_id">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-file-alt icon"></i>3. Detalhes da Infração</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="busca-auto">Auto de Infração</label>
                                <div class="input-group">
                                    <input type="text" id="busca-auto" class="form-input" name="busca_auto_text" placeholder="Código do auto" required>
                                    <button type="button" id="btn-novo-auto" class="btn btn-secondary"><i class="fas fa-plus"></i> Novo</button>
                                </div>
                                <div id="auto-sugestoes" class="sugestoes-box"></div>
                            </div>
                            <div class="form-group">
                                <label for="data-hora">Data e Hora</label>
                                <input type="datetime-local" id="data-hora" name="data_hora" class="form-input" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="local-infracao">Local da Infração</label>
                            <input type="text" id="local-infracao" name="local_infracao" class="form-input" placeholder="Ex: Av. Brasil, em frente ao nº 123" required>
                        </div>
                        <div class="form-group">
                            <label>Descrição da Infração</label>
                            <p id="descricao-auto" class="descricao-box">A descrição aparecerá aqui.</p>
                             <input type="hidden" id="auto_infracao_id" name="auto_infracao_id" required>
                        </div>
                        <div class="form-group">
                            <label for="valor-multa">Valor da Multa (R$)</label>
                             <input type="text" id="valor-multa" name="valor" class="form-input" placeholder="Ex: 195,23" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-paperclip icon"></i>4. Anexos da Multa</h3>
                        <div class="form-group file-upload-area">
                            <label for="anexos-input" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Clique para selecionar ou arraste até 5 arquivos</span>
                            </label>
                            <input type="file" id="anexos-input" name="anexos[]" multiple>
                            <small>Formatos permitidos: JPG, PNG, PDF.</small>
                        </div>
                        <div id="preview-area" class="preview-area"></div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-barcode icon"></i>5. Boleto de Pagamento</h3>
                        <div id="boleto-upload-area" class="form-group file-upload-area">
                            <label for="boleto-input" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Clique para selecionar ou arraste o boleto</span>
                            </label>
                            <input type="file" id="boleto-input" accept=".pdf" style="display: none;">
                            <small>Formato recomendado: PDF</small>
                        </div>
                        <div id="boleto-preview-area" class="preview-area"></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-check-circle"></i> Cadastrar Multa
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div id="modal-novo-auto" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2 class="modal-title"><i class="fas fa-plus-circle"></i> Novo Auto de Infração</h2>
            <form id="form-novo-auto">
                <div class="form-group">
                    <label for="novo-codigo-auto">Código da Infração</label>
                    <input type="text" id="novo-codigo-auto" class="form-input" placeholder="Ex: 581-9-0" required>
                </div>
                <div class="form-group">
                    <label for="nova-descricao-auto">Descrição Completa</label>
                    <textarea id="nova-descricao-auto" class="form-input" rows="4" placeholder="Descreva a infração..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/script.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>