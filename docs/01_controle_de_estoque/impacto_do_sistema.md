# Impacto da Melhoria de Controle de Estoque no Sistema

Este documento descreve como a nova funcionalidade de controle de estoque interage com os módulos existentes no sistema de orçamentos e quais são os impactos em cada um deles.

## 1. Módulo de Produtos (CRUD Admin)
- **Impacto**: O formulário de cadastro e edição de produtos incluirá os novos campos de estoque.
- **Comportamento**: 
  - Se a flag `Habilitar controle de estoque` (track_stock) estiver desligada, o sistema ignora qualquer verificação de quantidade para o produto.
  - Se estiver ligada, o usuário deverá preencher a quantidade inicial do estoque.
  - Na listagem de produtos no painel, uma coluna exibirá o saldo do estoque atual.

## 2. Módulo de Orçamentos (Quotes Admin)
- **Impacto**: Ações de criação, edição e alteração de status de orçamentos interagem diretamente com o inventário.
- **Comportamento**:
  - **Aprovação**: Quando um orçamento tem seu status alterado para `Aprovado`, o estoque de todos os itens do orçamento (que possuem `track_stock` ativo) é automaticamente reduzido.
  - **Cancelamento / Retorno**: Se o orçamento for alterado de `Aprovado` para `Cancelado` ou `Pendente`, o estoque deduzido anteriormente é devolvido aos produtos.
  - **Edição de Quantidades**: Se o orçamento já estiver `Aprovado` e for editado, o sistema recalculará a diferença de estoque e fará a devida correção de saldo e log correspondente.

## 3. Vitrine da Loja (Storefront)
- **Impacto**: Clientes finais visualizam a disponibilidade dos produtos no site.
- **Comportamento**:
  - Produtos com controle de estoque ativo e saldo `0` exibirão uma tag destacada de **"Sem Estoque"**.
  - O botão de "Adicionar ao Orçamento" e "Comprar pelo WhatsApp" serão desabilitados ou alterados para indicar indisponibilidade, evitando solicitações de produtos indisponíveis no estoque real.
  - No carrinho da vitrine, haverá validações no frontend para impedir que o cliente adicione uma quantidade maior do que a disponível em estoque.

## 4. Auditoria e Rastreabilidade
- **Impacto**: Histórico centralizado de todas as operações de estoque.
- **Comportamento**:
  - Qualquer alteração (seja por venda, cancelamento, entrada de mercadoria ou ajuste manual do gerente) registrará um log com o ID do usuário responsável, tipo de operação, quantidade movimentada e justificativa.
