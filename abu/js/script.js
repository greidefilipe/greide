$(document).ready(function() {

    // Lógica do Dashboard (Tema e Sidebar)
    const themeToggle = $('#theme-toggle');
    const sidebarToggle = $('#sidebar-toggle');
    const sidebarCloseBtn = $('.sidebar-close-btn');
    const sidebarOverlay = $('.sidebar-overlay');
    const body = $('body');
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme) {
        body.attr('data-theme', currentTheme);
        if (currentTheme === 'dark') {
            themeToggle.find('i').removeClass('fa-moon').addClass('fa-sun');
        }
    }
    themeToggle.on('click', function() {
        let newTheme = body.attr('data-theme') === 'dark' ? 'light' : 'dark';
        body.attr('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        themeToggle.find('i').toggleClass('fa-moon fa-sun');
    });
    sidebarToggle.on('click', function() {
        if (window.innerWidth <= 992) { body.addClass('sidebar-open'); } 
        else { body.toggleClass('sidebar-collapsed'); }
    });
    function closeMobileSidebar() { body.removeClass('sidebar-open'); }
    sidebarCloseBtn.on('click', closeMobileSidebar);
    sidebarOverlay.on('click', closeMobileSidebar);

    // Lógica de Busca e Formulário de Novo Auto
    function buscarDados(endpoint, query, targetElement) {
        if (query.length < 2) { targetElement.empty().hide(); return; }
        $.ajax({
            url: `php/${endpoint}.php`, type: 'GET', data: { query: query }, dataType: 'json',
            success: function(data) {
                targetElement.empty().show(); 
                if (data && data.length > 0) {
                    data.forEach(item => {
                        let displayText, dataAttrs;
                        if (endpoint === 'buscar_veiculos') { displayText = `<strong>${item.placa}</strong> - ${item.veiculo}`; dataAttrs = `data-id="${item.id}" data-placa="${item.placa}" data-display-text="${item.placa} - ${item.veiculo}"`; }
                        else if (endpoint === 'buscar_usuarios') { displayText = `<strong>${item.name}</strong> - CPF: ${item.cpf || 'Não informado'}`; dataAttrs = `data-id="${item.id}" data-nome="${item.name}"`; }
                        else if (endpoint === 'buscar_autos') { let descAbreviada = item.descricao.length > 50 ? item.descricao.substring(0, 50) + '...' : item.descricao; displayText = `<strong>${item.codigo_auto}</strong> - ${descAbreviada}`; dataAttrs = `data-id="${item.id}" data-codigo="${item.codigo_auto}" data-descricao="${item.descricao}"`; }
                        targetElement.append(`<div class="sugestao-item" ${dataAttrs}>${displayText}</div>`);
                    });
                } else { targetElement.append('<div class="sugestao-item-none">Nenhum resultado encontrado.</div>'); }
            },
            error: function() { targetElement.empty().show(); targetElement.append('<div class="sugestao-item-error">Erro ao buscar dados.</div>'); }
        });
    }
    $('#busca-veiculo').on('keyup', function() { buscarDados('buscar_veiculos', $(this).val(), $('#veiculo-sugestoes')); });
    $('#busca-usuario').on('keyup', function() { $('#usuario_id').val(''); $('#usuario-selecionado').hide(); buscarDados('buscar_usuarios', $(this).val(), $('#usuario-sugestoes')); });
    $('#busca-auto').on('keyup', function() { $('#auto_infracao_id').val(''); $('#descricao-auto').text('A descrição aparecerá aqui.'); buscarDados('buscar_autos', $(this).val(), $('#auto-sugestoes')); });
    $(document).on('click', '#veiculo-sugestoes .sugestao-item', function() { $('#veiculo_id').val($(this).data('id')); $('#busca-veiculo').val($(this).data('placa')); $('#veiculo-selecionado').html(`<i class="fas fa-check-circle"></i> ${$(this).data('display-text')}`).show(); $('#veiculo-sugestoes').empty().hide(); });
    $(document).on('click', '#usuario-sugestoes .sugestao-item', function() { $('#usuario_id').val($(this).data('id')); $('#busca-usuario').val($(this).data('nome')); $('#usuario-selecionado').html(`<i class="fas fa-check-circle"></i> ${$(this).data('nome')}`).show(); $('#usuario-sugestoes').empty().hide(); });
    $(document).on('click', '#auto-sugestoes .sugestao-item', function() { $('#auto_infracao_id').val($(this).data('id')); $('#busca-auto').val($(this).data('codigo')); $('#descricao-auto').text($(this).data('descricao')); $('#auto-sugestoes').empty().hide(); });
    const modal = $('#modal-novo-auto');
    $('#btn-novo-auto').on('click', () => modal.show());
    $('.modal-close').on('click', () => modal.hide());
    $(window).on('click', (e) => { if ($(e.target).is(modal)) { modal.hide(); } });
    $('#form-novo-auto').on('submit', function(e) { e.preventDefault(); const btn = $(this).find('button[type="submit"]'); const autoData = { codigo_auto: $('#novo-codigo-auto').val(), descricao: $('#nova-descricao-auto').val() }; btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>'); $.ajax({ url: 'php/cadastrar_auto.php', type: 'POST', contentType: 'application/json', data: JSON.stringify(autoData), success: function(response) { if (response.status === 'success') { modal.hide(); $('#auto_infracao_id').val(response.id); $('#busca-auto').val(autoData.codigo_auto); $('#descricao-auto').text(autoData.descricao); } else { Swal.fire('Erro', response.message || 'Falha ao cadastrar.', 'error'); } }, error: function() { Swal.fire('Erro', 'Erro de comunicação.', 'error'); }, complete: function() { btn.prop('disabled', false).html('Salvar'); $('#form-novo-auto')[0].reset(); } }); });


    // --- LÓGICA DE UPLOAD DE ARQUIVOS (ANEXOS E BOLETO) ---
    const anexoInput = $('#anexos-input');
    const anexoPreviewArea = $('#preview-area');
    const anexoUploadArea = $('.file-upload-area').first();
    const maxFiles = 5;
    let anexoFileList = [];

    const boletoInput = $('#boleto-input');
    const boletoPreviewArea = $('#boleto-preview-area');
    const boletoUploadArea = $('#boleto-upload-area');
    let boletoFile = null;

    // Funções para Anexos Múltiplos
    anexoUploadArea.on('click', () => anexoInput.click());
    anexoUploadArea.on('dragover', (e) => { e.preventDefault(); e.stopPropagation(); anexoUploadArea.addClass('drag-over'); });
    anexoUploadArea.on('dragleave', (e) => { e.preventDefault(); e.stopPropagation(); anexoUploadArea.removeClass('drag-over'); });
    anexoUploadArea.on('drop', (e) => { e.preventDefault(); e.stopPropagation(); anexoUploadArea.removeClass('drag-over'); handleAnexoFiles(e.originalEvent.dataTransfer.files); });
    anexoInput.on('change', () => handleAnexoFiles(anexoInput[0].files));

    function handleAnexoFiles(files) {
        if (anexoFileList.length + files.length > maxFiles) {
            Swal.fire('Atenção', `Você pode anexar no máximo ${maxFiles} arquivos.`, 'warning');
            return;
        }
        for (const file of files) {
            file.id = 'file_' + Date.now() + Math.random();
            anexoFileList.push(file);
            renderPreview(file, anexoPreviewArea);
        }
    }

    $(document).on('click', '#preview-area .remove-preview', function() {
        const fileId = $(this).data('file-id');
        anexoFileList = anexoFileList.filter(file => file.id !== fileId);
        $(`#${fileId}`).remove();
    });

    // Funções para Boleto Único
    boletoUploadArea.on('click', () => boletoInput.click());
    boletoUploadArea.on('dragover', (e) => { e.preventDefault(); e.stopPropagation(); boletoUploadArea.addClass('drag-over'); });
    boletoUploadArea.on('dragleave', (e) => { e.preventDefault(); e.stopPropagation(); boletoUploadArea.removeClass('drag-over'); });
    boletoUploadArea.on('drop', (e) => { e.preventDefault(); e.stopPropagation(); boletoUploadArea.removeClass('drag-over'); handleBoletoFile(e.originalEvent.dataTransfer.files); });
    boletoInput.on('change', () => handleBoletoFile(boletoInput[0].files));

    function handleBoletoFile(files) {
        if (files.length > 0) {
            boletoFile = files[0];
            boletoFile.id = 'boleto_' + Date.now();
            boletoPreviewArea.empty(); // Limpa o preview antigo
            renderPreview(boletoFile, boletoPreviewArea);
        }
    }

    $(document).on('click', '#boleto-preview-area .remove-preview', function() {
        boletoFile = null;
        boletoPreviewArea.empty();
    });

    // Função genérica de renderização de preview
    function renderPreview(file, previewContainer) {
        const reader = new FileReader();
        reader.onload = (e) => {
            let preview;
            if (file.type.startsWith('image/')) {
                preview = `<img src="${e.target.result}" alt="${file.name}">`;
            } else if (file.type === 'application/pdf') {
                preview = `<div class="file-icon"><i class="fas fa-file-pdf"></i><span>${file.name}</span></div>`;
            } else {
                 preview = `<div class="file-icon"><i class="fas fa-file-alt"></i><span>${file.name}</span></div>`;
            }
            previewContainer.append(`<div class="preview-item" id="${file.id}">${preview}<div class="remove-preview" data-file-id="${file.id}">&times;</div></div>`);
        };
        reader.readAsDataURL(file);
    }

    // --- LÓGICA DE ENVIO DO FORMULÁRIO COMPLETO ---
    $('#form-multa').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('.btn-submit');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cadastrando...');

        const formData = {
            veiculo_id: $('#veiculo_id').val(),
            usuario_id: $('#usuario_id').val() || null,
            usuario_nome: $('#busca-usuario').val(),
            auto_infracao_id: $('#auto_infracao_id').val(),
            data_hora: $('#data-hora').val(),
            local_infracao: $('#local-infracao').val(),
            valor: $('#valor-multa').val(),
            anexos: [],
            boleto: null
        };

        const readFileAsBase64 = (file) => {
            return new Promise((resolve, reject) => {
                if (!file) {
                    resolve(null);
                    return;
                }
                const reader = new FileReader();
                reader.onload = () => resolve({ name: file.name, type: file.type, content: reader.result });
                reader.onerror = (error) => reject(error);
                reader.readAsDataURL(file);
            });
        };
        
        const anexoPromises = anexoFileList.map(file => readFileAsBase64(file));
        const boletoPromise = readFileAsBase64(boletoFile);

        Promise.all([Promise.all(anexoPromises), boletoPromise])
            .then(([anexosAsBase64, boletoAsBase64]) => {
                formData.anexos = anexosAsBase64;
                formData.boleto = boletoAsBase64;

                $.ajax({
                    url: 'php/cadastrar_multa.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        if (response.status === 'success') {
                            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
                            Swal.fire({
                                title: 'Excelente!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'Concluir',
                                confirmButtonColor: primaryColor
                            });

                            $('#form-multa')[0].reset();
                            anexoFileList = []; 
                            boletoFile = null;
                            anexoPreviewArea.empty(); 
                            boletoPreviewArea.empty();
                            $('.selecionado-info').hide().text(''); 
                            $('#descricao-auto').text('A descrição aparecerá aqui.');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Falha no cadastro.' });
                        }
                    },
                    error: function(jqXHR) {
                        let errorMessage = 'Erro de comunicação.';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMessage = jqXHR.responseJSON.message;
                        }
                        Swal.fire({ icon: 'error', title: 'Erro de Conexão', text: errorMessage });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Cadastrar Multa');
                    }
                });
            })
            .catch(error => {
                console.error("Erro ao ler arquivos:", error);
                Swal.fire('Erro nos Anexos', 'Ocorreu um erro ao processar os arquivos. Tente novamente.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Cadastrar Multa');
            });
    });

    $(document).on('click', function(e) { if (!$(e.target).closest('.form-group').length) { $('.sugestoes-box').empty().hide(); } });
});