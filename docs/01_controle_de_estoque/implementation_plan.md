# Implementation Plan - Dynamic Stock Control Feature

This plan details the implementation of a full-fledged Stock Control system integrated with products and quotes. It also introduces the `docs` folder structure to store project improvements.

---

## Proposed Changes

### Database Changes
- **Migration**: `database/migrations/2026_08_01_180000_add_stock_fields_to_products_table.php` (adds `track_stock` and `stock_quantity` columns to `products`).
- **Migration**: `database/migrations/2026_08_01_181000_create_stock_movements_table.php` (creates history/audit log table).

---

### Backend Logic

#### [MODIFY] [Product.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Models/Product.php)
- Include `track_stock` and `stock_quantity` in fillable array and casts.

#### [NEW] [StockMovement.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Models/StockMovement.php)
- Create Eloquent model with relationships: product, user, quote.

#### [NEW] [StockService.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Services/StockService.php)
- Create service helper for stock operations: adjust stock, deduct on quote approval, replenish on cancellation, and sync diff on quote updates.

#### [NEW] [StockController.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Http/Controllers/StockController.php)
- Add CRUD controller to list stocks, query search/categories filters, load movement history, and process manual stock adjustment requests.

#### [MODIFY] [ProductController.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Http/Controllers/ProductController.php)
- Parse and validate stock values when saving or updating products.

#### [MODIFY] [QuoteController.php](file:///C:/xampp/htdocs/sistema-orcamentos/app/Http/Controllers/QuoteController.php)
- Hook stock deductions, replenishments, or synchronizations during quote updates.

#### [MODIFY] [web.php](file:///C:/xampp/htdocs/sistema-orcamentos/routes/web.php)
- Register stock panel and adjustment endpoints.

---

### Frontend Components

#### [MODIFY] [AuthenticatedLayout.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Layouts/AuthenticatedLayout.vue)
- Add sidebar link to Stock panel.

#### [MODIFY] [Create.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Products/Create.vue) & [Edit.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Products/Edit.vue)
- Add stock checkbox and quantity input controls.

#### [NEW] [Stock/Index.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Stock/Index.vue)
- Build stock balance and movement logs dashboard with adjustment modal.

#### [MODIFY] Storefront Pages: [Index.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Storefront/Index.vue), [Category.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Storefront/Category.vue), [Search.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Storefront/Search.vue), [Show.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Storefront/Show.vue), [Cart.vue](file:///C:/xampp/htdocs/sistema-orcamentos/resources/js/Pages/Storefront/Cart.vue)
- Implement display of "Sem Estoque" badges, disable quote addition if stock is 0, and validate cart quantity limits.

---

## Verification Plan

### Automated Verification
- Run `npm run build`.

### Manual Verification
- Test manual adjustments (addition/subtraction) in the admin panel.
- Change quote status to "Aprovado" and check stock deduction. Cancel it and check stock replenishment.
- Test out-of-stock display on storefront pages and cart limit checks.
