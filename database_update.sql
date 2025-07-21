-- Script SQL para adicionar as colunas necessárias para a funcionalidade de aprovação de pagamentos
-- Execute este script no seu banco de dados para habilitar a funcionalidade completa

-- Adiciona coluna status_pagamento à tabela multas
ALTER TABLE multas ADD COLUMN status_pagamento VARCHAR(50) DEFAULT 'Pendente';

-- Adiciona coluna comprovante_pagamento à tabela multas
ALTER TABLE multas ADD COLUMN comprovante_pagamento VARCHAR(255) DEFAULT NULL;

-- Caso a coluna local_infracao não exista, descomente a linha abaixo:
-- ALTER TABLE multas ADD COLUMN local_infracao TEXT DEFAULT NULL;

-- Define os possíveis valores para status_pagamento (opcional, dependendo do seu SGBD)
-- ALTER TABLE multas ADD CONSTRAINT chk_status_pagamento 
-- CHECK (status_pagamento IN ('Pendente', 'Aguardando Aprovação', 'Pagamento Aprovado', 'Pagamento Recusado'));

-- Comentários sobre os status:
-- 'Pendente' - Status inicial quando a multa é criada
-- 'Aguardando Aprovação' - Quando o usuário envia comprovante de pagamento
-- 'Pagamento Aprovado' - Quando o gestor aprova o pagamento
-- 'Pagamento Recusado' - Quando o gestor recusa o pagamento