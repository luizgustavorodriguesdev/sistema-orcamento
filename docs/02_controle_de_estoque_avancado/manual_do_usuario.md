# Manual do Usuário - Controle de Estoque Avançado

Este guia orienta os operadores e gerentes sobre como utilizar as ferramentas avançadas do módulo de estoque, ordens de compra e inventário.

---

## 1. Cadastro de Depósitos e Fornecedores
- **Depósitos (Locais)**: Antes de monitorar estoques específicos, acesse a aba "Depósitos" para cadastrar seus armazéns, lojas físicas ou showrooms.
- **Fornecedores**: Acesse a aba "Fornecedores" e cadastre os parceiros de quem você compra as mercadorias, informando CNPJ, contato e telefone.

---

## 2. Lançamentos Manuais de Entrada e Saída
- Para realizar pequenos ajustes diários (como amostras, descarte de quebras ou contagens pontuais):
  1. Vá para a aba **Lançamentos Manuais** no painel de estoque.
  2. Escolha o Produto, o Depósito específico, a quantidade e o tipo de operação (Entrada ou Saída).
  3. Insira uma justificativa descritiva clara (ex: "Descarte de caneca trincada") e confirme.
  4. O log atualizará instantaneamente no feed lateral de histórico.

---

## 3. Ordens de Compra e Check-in de Recebimento
- **Ordem de Compra**:
  1. Crie uma Ordem de Compra na aba dedicada, selecione o Fornecedor e adicione os itens com suas respectivas quantidades e preços negociados. O status inicial será `Rascunho`.
  2. Quando aprovado internamente, mude o status para `Aprovado`.
- **Check-in de Recebimento**:
  1. Quando a carga do fornecedor chegar física ou fiscalmente, selecione a Ordem de Compra e clique em **Check-in**.
  2. Confirme a quantidade física recebida de cada item e o depósito de destino.
  3. Ao finalizar, o sistema atualizará os saldos e alterará o status da ordem para `Recebido`.

---

## 4. Auditoria de Inventários Físicos
- Para realizar um inventário (reconciliação formal periódica):
  1. Clique em **Iniciar Inventário**, dê uma descrição (ex: "Balanço de Final de Mês") e selecione o Depósito a ser auditado.
  2. A sessão entrará em status `Em Andamento` e o sistema salvará a quantidade teórica esperada em banco.
  3. O auditor deve preencher os campos com a contagem real física coletada nas prateleiras. O sistema exibirá o delta (ex: -2, +1).
  4. Ao clicar em **Finalizar Inventário**, as contagens físicas tornam-se o novo saldo oficial no depósito correspondente, e a sessão fecha como `Finalizado`.

---

## 5. Sugestão de Compra & Painel Financeiro
- **Painel Financeiro**: Exibe o valor patrimonial total investido em estoque a preço de custo, o potencial bruto de venda, e a margem de lucro projetada.
- **Sugestão de Compra**: Lista todos os produtos cujos saldos consolidados caíram abaixo do seu estoque mínimo configurado. Selecione os produtos desejados e clique em **Gerar Compra** para criar ordens rascunhos agrupadas de forma rápida.
