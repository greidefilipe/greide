$(document).ready(function() {
    const tabelaBody = $('#tabela-aprovacao-body');
    const inputBusca = $('#busca-multa-aprovacao');
    const loadingSpinner = $('#loading-spinner');
    const modalDetalhes = $('#modal-detalhes-multa');
    const modalContent = $('#detalhes-multa-content');
    let debounceTimer;

    function carregarMultasAprovacao(query = '') {
        loadingSpinner.show();
        tabelaBody.hide();

        $.ajax({
            url: 'listar_multas_aprovacao.php',
            type: 'GET',
            data: { query: query },
            dataType: 'json',
            success: function(response) {
                tabelaBody.empty(); 
                if (response.status === 'success' && response.data.length > 0) {
                    response.data.forEach(multa => {
                        const dataFormatada = new Date(multa.data_hora_infracao).toLocaleString('pt-BR');
                        const valorFormatado = parseFloat(multa.valor_multa).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                        const linha = `
                            <tr data-multa-id="${multa.id}">
                                <td data-label="Placa">${multa.veiculo_placa || 'N/A'}</td>
                                <td data-label="Condutor">${multa.condutor_nome || 'N/A'}</td>
                                <td data-label="Código Infração">${multa.infracao_codigo || 'N/A'}</td>
                                <td data-label="Data">${dataFormatada}</td>
                                <td data-label="Valor">${valorFormatado}</td>
                                <td data-label="Status"><span class="status-badge status-aguardando">${multa.status_pagamento || 'N/A'}</span></td>
                                <td data-label="Ações">
                                    <button class="btn-acao btn-detalhes" title="Ver Detalhes" data-multa='${JSON.stringify(multa)}'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-acao btn-aprovar" title="Aprovar Pagamento" data-multa-id="${multa.id}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn-acao btn-recusar" title="Recusar Pagamento" data-multa-id="${multa.id}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tabelaBody.append(linha);
                    });
                } else {
                    const mensagem = response.message || 'Nenhuma multa aguardando aprovação encontrada.';
                    tabelaBody.append(`<tr><td colspan="7" style="text-align:center;">${mensagem}</td></tr>`);
                }
            },
            error: function() {
                tabelaBody.append('<tr><td colspan="7" style="text-align:center; color: red;">Erro ao carregar os dados.</td></tr>');
            },
            complete: function() {
                loadingSpinner.hide();
                tabelaBody.show();
            }
        });
    }

    // Busca com debounce
    inputBusca.on('keyup', function() {
        clearTimeout(debounceTimer);
        const query = $(this).val();
        debounceTimer = setTimeout(function() {
            carregarMultasAprovacao(query);
        }, 500);
    });

    // Event handlers para os botões de ação
    $(document).on('click', '.btn-detalhes', function() {
        const multa = JSON.parse($(this).attr('data-multa'));
        mostrarDetalhesMulta(multa);
    });

    $(document).on('click', '.btn-aprovar', function() {
        const multaId = $(this).attr('data-multa-id');
        if (confirm('Tem certeza que deseja aprovar este pagamento?')) {
            processarAprovacao(multaId, 'aprovar');
        }
    });

    $(document).on('click', '.btn-recusar', function() {
        const multaId = $(this).attr('data-multa-id');
        if (confirm('Tem certeza que deseja recusar este pagamento?')) {
            processarAprovacao(multaId, 'recusar');
        }
    });

    function mostrarDetalhesMulta(multa) {
        const dataFormatada = new Date(multa.data_hora_infracao).toLocaleString('pt-BR');
        const valorFormatado = parseFloat(multa.valor_multa).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        const comprovanteHtml = multa.comprovante_pagamento 
            ? `<div class="form-group">
                 <label>Comprovante de Pagamento:</label>
                 <div class="comprovante-container">
                     <a href="../../${multa.comprovante_pagamento}" target="_blank" class="btn btn-secondary">
                         <i class="fas fa-file-alt"></i> Ver Comprovante
                     </a>
                 </div>
               </div>` 
            : '<div class="form-group"><label>Comprovante de Pagamento:</label><p>Nenhum comprovante anexado</p></div>';

        const content = `
            <div class="detalhes-grid">
                <div class="form-group">
                    <label>ID da Multa:</label>
                    <p>${multa.id}</p>
                </div>
                <div class="form-group">
                    <label>Placa do Veículo:</label>
                    <p>${multa.veiculo_placa || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Condutor:</label>
                    <p>${multa.condutor_nome || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Código da Infração:</label>
                    <p>${multa.infracao_codigo || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Descrição da Infração:</label>
                    <p>${multa.infracao_descricao || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Data e Hora da Infração:</label>
                    <p>${dataFormatada}</p>
                </div>
                <div class="form-group">
                    <label>Local da Infração:</label>
                    <p>${multa.local_infracao || 'N/A'}</p>
                </div>
                <div class="form-group">
                    <label>Valor da Multa:</label>
                    <p>${valorFormatado}</p>
                </div>
                <div class="form-group">
                    <label>Status do Pagamento:</label>
                    <p><span class="status-badge status-aguardando">${multa.status_pagamento}</span></p>
                </div>
                ${comprovanteHtml}
            </div>
            <div class="modal-actions">
                <button class="btn btn-success" onclick="processarAprovacao(${multa.id}, 'aprovar')">
                    <i class="fas fa-check"></i> Aprovar Pagamento
                </button>
                <button class="btn btn-danger" onclick="processarAprovacao(${multa.id}, 'recusar')">
                    <i class="fas fa-times"></i> Recusar Pagamento
                </button>
            </div>
        `;
        
        modalContent.html(content);
        modalDetalhes.show();
    }

    function processarAprovacao(multaId, acao) {
        $.ajax({
            url: 'processar_aprovacao.php',
            type: 'POST',
            data: JSON.stringify({
                multa_id: multaId,
                acao: acao
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    modalDetalhes.hide();
                    carregarMultasAprovacao(); // Recarrega a tabela
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function() {
                alert('Erro ao processar a solicitação.');
            }
        });
    }

    // Fecha modal ao clicar no X ou fora dela
    $('.modal-close').on('click', function() {
        modalDetalhes.hide();
    });

    $(window).on('click', function(event) {
        if (event.target == modalDetalhes[0]) {
            modalDetalhes.hide();
        }
    });

    // Carrega dados iniciais
    carregarMultasAprovacao();
});