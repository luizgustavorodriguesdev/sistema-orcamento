# Guia do Desenvolvedor - Arquitetura do Menu em Destaque

Este guia descreve os detalhes técnicos para desenvolvedores realizarem manutenção ou expandirem a barra de destaque do storefront.

---

## 1. Fluxo de Dados (Backend)
Toda requisição feita à vitrine passa pelo método `getStorefrontData()` no `StorefrontController`. Este método reúne e retorna o array `highlightedMenuItems` estruturado da seguinte forma:

```php
[
    'label' => 'Nome exibido no menu',
    'url'   => 'URL gerada via rota do Laravel',
    'type'  => 'category' | 'page' | 'product'
]
```

## 2. Renderização (Frontend)
Os componentes Vue de storefront recebem a prop `highlightedMenuItems` e injetam a barra de links logo após o elemento de cabeçalho principal:

```html
<!-- Exemplo de template implementado nas views da vitrine -->
<div v-if="highlightedMenuItems && highlightedMenuItems.length > 0" class="border-t border-slate-100 py-2.5 overflow-x-auto scrollbar-none bg-slate-50/50">
    <!-- Links mapeados -->
</div>
```

- **Estilização de Produtos**: Caso o link seja um produto específico, ele ganha uma estilização chamativa (fundo azul-600 e texto branco) agindo como call-to-action imediato.
