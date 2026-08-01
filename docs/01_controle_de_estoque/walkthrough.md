# Walkthrough - Sistema de Controle de Estoque (Stock Control)

Todas as funcionalidades do sistema de Controle de Estoque foram implementadas com sucesso e compiladas de forma limpa.

---

## O que foi realizado

### 1. Modelagem e Banco de Dados (Migrações)
- Adicionados os campos `track_stock` e `stock_quantity` na tabela `products`.
- Criada a tabela `stock_movements` para registrar o histórico e auditorias de entrada, saída, ajustes manuais e estornos do estoque.

### 2. Lógica e Serviços no Backend
- **[StockMovement.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Models/StockMovement.php)**: Modelagem do histórico com relações com Produto, Usuário (responsável) e Orçamento.
- **[StockService.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Services/StockService.php)**: Helper contendo a inteligência de dedução e estorno de estoque, além de recálculo delta inteligente de orçamentos atualizados.
- **[StockController.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Http/Controllers/StockController.php)**: Endpoints administrativos de listagem de estoque, filtros rápidos e processamento de ajuste manual.
- **Integração**:
  - `ProductController` agora valida e gerencia estoques iniciais no cadastro/edição de produtos.
  - `QuoteController` gerencia a dedução de estoque ao aprovar orçamentos (`Aprovado`) e devolve automaticamente as quantidades se o orçamento for cancelado (`Cancelado`) ou voltar a `Pendente`.

### 3. Painel Administrativo de Controle de Estoque
- **[Stock/Index.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Stock/Index.vue)**: Nova página com:
  - Tabela de saldos com badges e ações rápidas.
  - Modal dinâmico para entradas/saídas manuais de mercadorias com justificativas.
  - Log lateral de movimentações em tempo real.
- **Sidebar**: Integrado o link de acesso rápido ao layout administrativo.
- **Formulários de Produto**: Adicionados campos para ativar/desativar controle e informar estoque.

### 4. Experiência de Compra (Storefront)
- Exibição de badges de **Esgotado** e indisponibilidade automática de inserção ao orçamento.
- Validação dinâmica de quantidades no carrinho, impedindo a solicitação de volumes superiores ao estoque atual.

---

## Verificação Realizada
- **Compilação de Assets**: O comando `npm run build` foi executado e todos os componentes Vue/Inertia compilaram de forma limpa sem avisos ou erros.
