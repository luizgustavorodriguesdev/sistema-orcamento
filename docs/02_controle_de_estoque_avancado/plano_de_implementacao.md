# Plano de Implementação - Controle de Estoque Avançado (ERP)

Este documento especifica o plano de desenvolvimento e expansão do Controle de Estoque (Versão 2) para integrar depósitos múltiplos, ordens de compra, preço de custo e auditorias de inventário físico.

---

## 1. Escopo das Funcionalidades

### A. Controle por Múltiplos Depósitos (Warehouses)
- Cadastro de locais físicos de estoque (ex: Depósito Central, Loja Física).
- Divisão do saldo de estoque do produto entre os depósitos ativos.
- Dedução automática no depósito padrão configurado ao aprovar orçamentos.

### B. Gestão de Custos e Margens Financeiras
- Adição do campo `preço de custo` (`cost_price`) nos produtos.
- Dashboard financeiro consolidando o valor patrimonial em estoque (Custo de Aquisição vs. Valor Potencial de Venda) e margem bruta média.

### C. Lançamentos Manuais de Entrada e Saída Estruturados
- Tela dedicada para lançamentos manuais estruturados.
- Escolha do produto, quantidade, tipo de movimentação (Entrada/Saída), depósito de destino e justificativa/motivo do ajuste.

### D. Ordens de Compra (Procurement) e Fornecedores
- Cadastro completo de fornecedores.
- Criação e envio de Ordens de Compra com múltiplos produtos e custos unitários negociados.
- **Check-in de Recebimento**: Interface de conferência na entrada de mercadorias no almoxarifado, atualizando os saldos e registrando auditoria.

### E. Inventários Físicos e Conferências (Reconciliação)
- Abertura de sessões de inventário para um depósito.
- Inserção de contagens físicas por produto (conferência).
- Reconciliação final com cálculo do delta (estoque esperado vs. contado) e ajuste automático de saldos.

---

## 2. Cronograma de Ações e Etapas

1. **Modelagem de Dados**:
   - Criação das migrations e tabelas: `warehouses`, `product_warehouse`, `suppliers`, `purchase_orders`, `purchase_order_items`, `inventory_sessions`, `inventory_items`.
   - Modificação das tabelas existentes (`products` e `stock_movements`).
2. **Camada de Serviço (Backend)**:
   - Implementação de transações financeiras de custo.
   - Atualização do `StockService.php` para tratar múltiplos depósitos e check-ins de ordens de compra.
3. **Controladores Administrativos**:
   - CRUDs de Fornecedores, Depósitos, Ordens de Compra e Inventários.
   - Geração automática de sugestões de compra baseadas em estoque mínimo.
4. **Interface Administrativa (Vue 3 / Inertia)**:
   - Dashboard financeiro de saldos.
   - Módulo de Ordens de Compra e tela interativa de Check-in.
   - Interface de auditoria física de inventários.
