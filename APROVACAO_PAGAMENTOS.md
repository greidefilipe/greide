# Funcionalidade de Aprovação de Pagamentos

Este documento descreve as novas funcionalidades implementadas para aprovação de pagamentos de multas.

## Funcionalidades Adicionadas

### 1. Novo Menu "Aprovar Pagamentos"
- Adicionado novo item no menu lateral "Aprovar Pagamentos" 
- Página acessível através de `php/aprovar_pagamentos.php`
- Lista todas as multas com status "Aguardando Aprovação"

### 2. Modificação da Página "Consultar Multas"
- Botão "Editar" foi substituído por botão "Informação"
- Ao clicar em "Informação", exibe todos os detalhes da multa em modo visualização (somente leitura)
- Inclui status atual da multa

### 3. Sistema de Status de Pagamento
- **Pendente**: Status inicial quando a multa é criada
- **Aguardando Aprovação**: Quando comprovante de pagamento é enviado
- **Pagamento Aprovado**: Quando gestor aprova o pagamento
- **Pagamento Recusado**: Quando gestor recusa o pagamento

### 4. Página de Aprovação
- Lista todas as multas aguardando aprovação
- Mostra detalhes completos incluindo comprovante de pagamento
- Ações disponíveis: Aprovar ou Recusar pagamento
- Busca por placa, condutor ou código da infração

## Arquivos Criados/Modificados

### Novos Arquivos:
- `php/aprovar_pagamentos.php` - Página principal de aprovação
- `php/listar_multas_aprovacao.php` - API para listar multas aguardando aprovação
- `php/processar_aprovacao.php` - API para processar aprovação/recusa
- `js/aprovacao.js` - JavaScript para página de aprovação
- `database_update.sql` - Script SQL para atualizar banco de dados

### Arquivos Modificados:
- `index.php` - Adicionado novo item de menu
- `php/consultar_multas.php` - Adicionado novo item de menu e modal de informações
- `php/listar_multas.php` - Incluídas colunas de status e tratamento de erros
- `js/consulta.js` - Modificado botão "Editar" para "Informação"
- `css/style.css` - Adicionados estilos para status badges e modais

## Requisitos do Banco de Dados

Para funcionamento completo, execute o script `database_update.sql`:

```sql
ALTER TABLE multas ADD COLUMN status_pagamento VARCHAR(50) DEFAULT 'Pendente';
ALTER TABLE multas ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL;
```

## Compatibilidade

O sistema foi desenvolvido para ser compatível com bancos de dados que não possuem as novas colunas:
- Se as colunas não existirem, o sistema funciona em modo limitado
- A página de aprovação mostrará mensagem informativa sobre a necessidade de atualização
- A funcionalidade de consulta continua funcionando normalmente

## Como Usar

1. **Consultar Multas**: Use o botão "Informação" para ver detalhes completos
2. **Aprovar Pagamentos**: Acesse o menu "Aprovar Pagamentos" para ver multas aguardando aprovação
3. **Aprovar/Recusar**: Use os botões na página de aprovação para processar os pagamentos

## Estilos Visuais

- **Status Pendente**: Badge amarelo
- **Aguardando Aprovação**: Badge amarelo
- **Pagamento Aprovado**: Badge verde  
- **Pagamento Recusado**: Badge vermelho