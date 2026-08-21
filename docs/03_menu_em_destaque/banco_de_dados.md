# Documentação de Banco de Dados - Menu em Destaque

Este documento descreve as colunas adicionadas ao banco de dados para suportar a funcionalidade de Menu em Destaque (Sub-header).

---

## Estrutura de Tabelas Modificadas

### 1. Tabela `categories`
- **Coluna**: `show_in_highlighted_menu`
- **Tipo**: `BOOLEAN`
- **Valor Padrão**: `FALSE`
- **Descrição**: Define se a categoria deve ser exibida como link rápido na barra horizontal do storefront.

### 2. Tabela `pages`
- **Coluna**: `show_in_highlighted_menu`
- **Tipo**: `BOOLEAN`
- **Valor Padrão**: `FALSE`
- **Descrição**: Define se a página institucional ativa deve ser exibida como link rápido na barra horizontal do storefront.

### 3. Tabela `products`
- **Coluna**: `show_in_highlighted_menu`
- **Tipo**: `BOOLEAN`
- **Valor Padrão**: `FALSE`
- **Descrição**: Define se o produto deve ser exibido como link rápido na barra horizontal do storefront.
