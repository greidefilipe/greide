$(document).ready(function() {
    const tabelaBody = $('#tabela-multas-body');
    const inputBusca = $('#busca-multa');
    const loadingSpinner = $('#loading-spinner');
    let debounceTimer;

    function carregarMultas(query = '') {
        loadingSpinner.show();
        tabelaBody.hide();

        $.ajax({
            // CAMINHO CORRIGIDO: Aponta diretamente para o arquivo, pois estão na mesma pasta.
            url: 'listar_multas.php',
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
                            <tr>
                                <td data-label="Placa">${multa.veiculo_placa || 'N/A'}</td>
                                <td data-label="Condutor">${multa.condutor_nome || 'N/A'}</td>
                                <td data-label="Código Infração">${multa.infracao_codigo || 'N/A'}</td>
                                <td data-label="Data">${dataFormatada}</td>
                                <td data-label="Valor">${valorFormatado}</td>
                                <td data-label="Ações">
                                    <button class="btn-acao btn-informacao" title="Informações" data-multa='${JSON.stringify(multa)}'>
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <button class="btn-acao btn-excluir" title="Excluir"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                        tabelaBody.append(linha);
                    });
                } else {
                    tabelaBody.append('<tr><td colspan="6" style="text-align:center;">Nenhuma multa encontrada.</td></tr>');
                }
            },
            error: function() {
                tabelaBody.append('<tr><td colspan="6" style="text-align:center; color: red;">Erro ao carregar os dados.</td></tr>');
            },
            complete: function() {
                loadingSpinner.hide();
                tabelaBody.show();
            }
        });
    }

    inputBusca.on('keyup', function() {
        clearTimeout(debounceTimer);
        const query = $(this).val();
        debounceTimer = setTimeout(function() {
            carregarMultas(query);
        }, 500);
    });

    // Event handler para o botão de informações
    $(document).on('click', '.btn-informacao', function() {
        const multa = JSON.parse($(this).attr('data-multa'));
        mostrarInformacoesMulta(multa);
    });

    function mostrarInformacoesMulta(multa) {
        const dataFormatada = new Date(multa.data_hora_infracao).toLocaleString('pt-BR');
        const valorFormatado = parseFloat(multa.valor_multa).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        
        const statusBadge = multa.status_pagamento ? 
            `<span class="status-badge status-${multa.status_pagamento.toLowerCase().replace(' ', '-')}">${multa.status_pagamento}</span>` : 
            '<span class="status-badge">N/A</span>';

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
                    <p>${statusBadge}</p>
                </div>
            </div>
        `;
        
        // Assumindo que existe um modal similar na página consultar_multas
        $('#modal-info-content').html(content);
        $('#modal-info-multa').show();
    }

    // Fecha modal ao clicar no X ou fora dela
    $('.modal-close').on('click', function() {
        $('#modal-info-multa').hide();
    });

    $(window).on('click', function(event) {
        if (event.target == $('#modal-info-multa')[0]) {
            $('#modal-info-multa').hide();
        }
    });

    carregarMultas();
});