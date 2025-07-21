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
                                    <button class="btn-acao btn-editar" title="Editar"><i class="fas fa-edit"></i></button>
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

    carregarMultas();
});