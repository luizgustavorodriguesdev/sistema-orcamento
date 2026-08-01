# Guia do Desenvolvedor - Controle de Estoque

Este guia detalha a arquitetura do sistema de estoque para desenvolvedores que necessitam realizar manutenções ou extensões na feature.

## 1. Lógica de Alteração do Estoque por Status do Orçamento

Para manter o estoque sincronizado com os orçamentos, o sistema implementa validações e ajustes automáticos no `QuoteController`.

### Estados de Transição de Estoque
A dedução ou devolução do estoque ocorre estritamente na mudança de status do orçamento:

1. **`Pendente` / `Cancelado` -> `Aprovado`**:
   - **Ação**: Dedução de estoque.
   - **Regra**: Para cada produto no orçamento que possui `track_stock = true`, diminui o campo `stock_quantity` pelo valor de `quantity` especificado no item do orçamento.
   - **Log**: Registra `StockMovement` com tipo `quote_approved`.

2. **`Aprovado` -> `Cancelado` / `Pendente`**:
   - **Ação**: Devolução de estoque.
   - **Regra**: Incrementa o campo `stock_quantity` pelo valor de `quantity` no orçamento.
   - **Log**: Registra `StockMovement` com tipo `quote_cancelled`.

3. **Edição de um Orçamento já `Aprovado`**:
   - **Ação**: Recálculo da diferença.
   - **Regra**: Se um item for alterado de 10 para 15 unidades, deduz mais 5. Se for alterado de 10 para 7 unidades, devolve 3 ao estoque. Se um produto for removido, devolve a quantidade total dele.
   - **Log**: Registra `StockMovement` com tipo `adjustment` detalhando a renegociação.

---

## 2. API / Endpoints de Ajuste de Estoque

| Método | URL | Descrição | Parâmetros |
| :--- | :--- | :--- | :--- |
| `GET` | `/stock` | Listagem administrativa de estoques e logs. | - |
| `POST` | `/stock/{product}/adjust` | Realiza ajuste manual de estoque. | `quantity` (int), `type` (addition/subtraction), `description` (string) |

---

## 3. Validações de Vitrine
No frontend (Vite/Vue 3), ao renderizar a página de um produto ou listá-lo na vitrine, verifica-se:
- `product.track_stock`: se verdadeiro, valida se `product.stock_quantity > 0`.
- Se a quantidade em estoque for zero, a interface do usuário exibe um badge visual informativo e previne cliques em botões de ação ("Adicionar ao Orçamento").
