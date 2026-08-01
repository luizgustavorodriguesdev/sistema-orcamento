# Plano de Implementação - Sistema de Controle de Estoque

Este documento descreve o plano detalhado para implementar o controle de estoque integrado no sistema de orçamentos.

## 1. Objetivos
- Permitir o gerenciamento do inventário de cada produto de forma manual e automática.
- Manter o histórico de todas as entradas, saídas e ajustes no estoque (rastreabilidade).
- Evitar que produtos sem estoque sejam vendidos ou orçados (quando o rastreamento estiver ativado).
- Atualizar o estoque automaticamente ao aprovar ou cancelar orçamentos.

## 2. Escopo Técnico
- **Banco de Dados**:
  - Adição dos campos `track_stock` (booleano) e `stock_quantity` (inteiro) na tabela `products`.
  - Criação da tabela `stock_movements` para auditoria do estoque.
- **Backend (Laravel)**:
  - Model e migration de movimentação de estoque (`StockMovement`).
  - Lógica automática no `QuoteController` ou observer para deduzir/devolver estoque de acordo com a transição de status do orçamento (`Pendente` -> `Aprovado` -> `Cancelado`).
  - Endpoint e regras de negócio para ajustes manuais no estoque.
- **Frontend (Inertia.js + Vue 3)**:
  - Tela de Gerenciamento de Estoque (Listagem de produtos, quantidades, ajuste rápido de estoque e histórico de movimentações).
  - Atualização do formulário de Produtos (adicionando os campos de rastreamento e quantidade de estoque).
  - Integração visual na vitrine da loja (exibindo badges de "Sem Estoque" e bloqueando adição ao carrinho quando aplicável).

## 3. Etapas de Execução
1. Criação das Migrações e Models.
2. Desenvolvimento do Controller Administrativo de Estoque (`StockController`).
3. Criação das telas administrativas do controle de estoque e histórico de movimentações.
4. Ajuste no CRUD de Produtos para adicionar os campos de estoque.
5. Implementação dos hooks/lógicas de atualização de estoque no ciclo de vida do orçamento (Quote).
6. Integração e validações de estoque no storefront (carrinho e vitrine).
7. Recompilação de assets e testes de validação.
