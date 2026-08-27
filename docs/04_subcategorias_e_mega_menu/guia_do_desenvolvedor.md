# Guia do Desenvolvedor - Relações e Componentização do Mega Menu

Este guia detalha a arquitetura técnica por trás do sistema de subcategorias e menu flutuante.

---

## 1. Relações Eloquent
No modelo `Category`, definimos as seguintes relações de auto-associação:
- `parent()`: Relação `BelongsTo` que retorna o pai imediato da categoria.
- `children()`: Relação `HasMany` que traz todas as subcategorias filhas associadas a ela.

---

## 2. Lógica de Eager Loading no Storefront
Para garantir alta performance, no `StorefrontController`, carregamos as subcategorias com a relação `children` de forma adiantada (Eager Loading):
```php
Category::with('children')->whereNull('parent_id')->where('show_in_highlighted_menu', true)->get();
```

---

## 3. Comportamento CSS no Hover
A visibilidade e a transição suave do painel flutuante do Mega Menu são controladas por Tailwind CSS usando a classe utilitária `group`:
- O painel possui `opacity-0` e `invisible` por padrão.
- Ao passar o mouse sobre a div pai (`class="group"`), as classes `group-hover:opacity-100` e `group-hover:visible` são disparadas, garantindo animações via CSS sem necessidade de JS watchers adicionais.
- A propriedade `overflow-x-auto md:overflow-visible` no contêiner da barra horizontal assegura rolagem móvel no celular e abertura fluida sem cortes de tela no desktop.
