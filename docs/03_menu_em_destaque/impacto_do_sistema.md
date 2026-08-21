# Impacto no Sistema - Menu em Destaque

Este documento descreve os impactos arquiteturais e funcionais introduzidos pelo novo Menu em Destaque.

---

## 1. Desempenho e Carregamento (Storefront)
- As consultas para preenchimento dos itens de menu em destaque ocorrem dentro do método `getStorefrontData()` no `StorefrontController`.
- Para evitar sobrecarga no banco de dados, a consulta seleciona apenas campos fundamentais (`id`, `name`, `title`, `slug`) das entidades ativas.
- O resultado é compartilhado de forma eficiente com todas as páginas através do Inertia.

---

## 2. Experiência do Usuário (Responsividade)
- A barra de sub-header possui overflow lateral habilitado (`overflow-x-auto`) com barra de rolagem oculta (`scrollbar-none`).
- Isso garante que, mesmo que o administrador selecione dezenas de itens para o menu, a experiência visual em dispositivos celulares não quebre, mantendo navegação por deslize lateral fluida.
