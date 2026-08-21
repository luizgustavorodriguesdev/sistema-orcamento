# Plano de Implementação - Menu em Destaque (Sub-header)

Este plano detalha o design e a implementação do novo **Menu em Destaque (Sub-header)** no storefront, permitindo destacar Categorias, Páginas institucionais e Produtos com flags configuráveis no painel administrativo.

---

## 1. Banco de Dados
Adição da flag booleana `show_in_highlighted_menu` às tabelas `categories`, `pages` e `products` para controle individual de exibição.

---

## 2. Interface Administrativa
Inclusão de campos de checkbox nos formulários de criação e edição correspondentes no Painel Administrativo para controle visual simplificado.

---

## 3. Interface da Vitrine
Inclusão de uma barra de sub-header horizontal diretamente abaixo do menu principal com scroll horizontal responsivo para dispositivos móveis e destaque visual para os itens.
