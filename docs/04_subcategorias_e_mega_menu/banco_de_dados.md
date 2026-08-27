# Documentação de Banco de Dados - Subcategorias e Mega Menu

Este documento descreve as colunas adicionadas ao banco de dados para suportar a funcionalidade de Subcategorias e Mega Menu promocional.

---

## Estrutura de Tabelas Modificadas

### Tabela `categories`
- **Coluna**: `parent_id`
  - **Tipo**: `UNSIGNED BIGINT` (Nullable)
  - **Chave Estrangeira**: Referencia `id` na tabela `categories`
  - **Ação no Delete**: `SET NULL`
  - **Descrição**: Relação de auto-referência para criar estrutura hierárquica. Se for nulo, a categoria é tratada como categoria pai (principal).
- **Coluna**: `mega_menu_banner_path`
  - **Tipo**: `STRING` (Nullable)
  - **Descrição**: Caminho do arquivo da imagem do banner promocional exibido à direita do Mega Menu no storefront.
