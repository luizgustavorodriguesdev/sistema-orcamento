# Impacto do Sistema - Controle de Estoque Avançado (ERP)

Este documento avalia o impacto e as integrações do novo módulo avançado de estoque sobre as regras e fluxos já existentes na plataforma de orçamentos.

---

## 1. Impacto no Cadastro de Produtos
- **Cadastro e Edição**: As views `Create.vue` e `Edit.vue` de produtos receberão os campos `Estoque Mínimo` e `Preço de Custo`.
- **Preço de Custo**: A introdução do preço de custo não altera o preço de venda exibido para o cliente final, mas permite a apuração de lucros, custos de aquisição e a valoração total dos estoques.

---

## 2. Impacto no Fluxo de Orçamentos
- **Aprovação de Orçamento**:
  - Quando um orçamento for marcado como `Aprovado`, as mercadorias serão deduzidas de um **Depósito Padrão** (definido nas configurações do sistema ou o primeiro depósito ativo).
  - Caso o orçamento possua um item cuja quantidade solicitada seja maior que o saldo no depósito padrão, o sistema alertará o administrador antes de confirmar a aprovação.
- **Cancelamento**:
  - Ao cancelar um orçamento aprovado, o estoque estornado retorna diretamente para o depósito original de onde foi retirado.
- **Edição de Itens**:
  - O cálculo do delta na alteração de quantidades (comparando quantidades antigas com novas) é repassado ao depósito padrão, garantindo que o saldo daquele almoxarifado específico permaneça correto.

---

## 3. Impacto na Experiência do Cliente (Storefront)
- **Vitrine e Busca**: O storefront consultará a soma de todos os saldos disponíveis em **todos os depósitos ativos** do produto para determinar se o produto está "Sem Estoque" (esgotado).
- **Carrinho de Compras**: A quantidade máxima permitida de adição no carrinho será limitada ao saldo agregado total de todos os depósitos.

---

## 4. Auditoria de Estoque e Logs
- **Histórico Geral**: Qualquer entrada por recebimento de compras, ajuste de inventário corretivo, ou lançamento manual gerará uma linha de histórico rastreável associada ao usuário executor e com link/referência à ordem de compra ou sessão de inventário relacionada.
- **Prevenção de Furos**: Não será permitido deletar depósitos com saldo de mercadorias vinculados, nem fornecedores com ordens de compra em aberto.
