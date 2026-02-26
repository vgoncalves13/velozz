# FASE 9: Produtos, Oportunidades e Funil de Vendas - COMPLETE ✅

**Status:** ✅ Complete
**Data:** 2026-02-26

---

## Overview

FASE 9 implements the complete sales management module, including:
- Product catalog management
- Opportunity tracking linked to leads and products
- Sales funnel visualization
- Revenue forecasting based on probability-weighted opportunities

---

## Implemented Features

### 1. Products Module

#### Database
- **Migration:** `2026_02_26_051638_create_products_table.php`
- **Fields:**
  - `tenant_id` - Tenant isolation
  - `name` - Internal product name
  - `title` - Display title (nullable)
  - `description` - Product description (nullable)
  - `category` - Product category (nullable)
  - `price` - Decimal price (default 0.00)
  - `currency` - Currency code (default EUR)
  - `unit` - Unit of measurement (nullable)
  - `status` - active/inactive (default active)
  - `image_url` - Product image (nullable)
  - Indexes: tenant_id, status

#### Model
- **File:** `app/Models/Product.php`
- **Relationships:**
  - `tenant()` - BelongsTo Tenant
  - `opportunities()` - HasMany Opportunity
- **Casts:** price as decimal:2

#### Filament Resource
- **Main Resource:** `app/Filament/Client/Resources/Products/ProductResource.php`
  - Query scope filters by tenant_id
  - Navigation icon: ShoppingBag
  - Navigation sort: 6

- **Form Schema:** `app/Filament/Client/Resources/Products/Schemas/ProductForm.php`
  - Three sections:
    1. **Basic Information:** name, title, category (datalist), description
    2. **Pricing:** price (EUR prefix), currency select, unit, status
    3. **Image:** file upload with image editor
  - No tenant_id field (auto-filled)
  - Helper texts for guidance

- **Table Schema:** `app/Filament/Client/Resources/Products/Tables/ProductsTable.php`
  - Circular image with fallback avatar
  - Name with title as description
  - Category badge (info color)
  - Price formatted as money (EUR)
  - Unit with N/A placeholder
  - Status badge (success/gray)
  - Default sort: created_at desc

- **Create Page:** `app/Filament/Client/Resources/Products/Pages/CreateProduct.php`
  - Auto-fills tenant_id in `mutateFormDataBeforeCreate()`

### 2. Opportunities Module

#### Database
- **Migration:** `2026_02_26_051646_create_opportunities_table.php`
- **Fields:**
  - `tenant_id` - Tenant isolation
  - `lead_id` - FK to leads (cascadeOnDelete)
  - `product_id` - FK to products (nullOnDelete)
  - `value` - Decimal value (default 0.00)
  - `stage` - proposal/negotiation/closed_won/closed_lost (default proposal)
  - `probability` - Integer 0-100 (default 25)
  - `expected_close_date` - Date (nullable)
  - `loss_reason` - Text (nullable)
  - `assigned_user_id` - FK to users (nullOnDelete)
  - `notes` - Text (nullable)
  - `closed_at` - Timestamp (nullable)
  - Indexes: tenant_id, lead_id, product_id, stage, assigned_user_id

#### Model
- **File:** `app/Models/Opportunity.php`
- **Relationships:**
  - `tenant()` - BelongsTo Tenant
  - `lead()` - BelongsTo Lead
  - `product()` - BelongsTo Product
  - `assignedUser()` - BelongsTo User
- **Casts:**
  - value as decimal:2
  - probability as integer
  - expected_close_date as date
  - closed_at as datetime

#### Filament Resource
- **Main Resource:** `app/Filament/Client/Resources/Opportunities/OpportunityResource.php`
  - Query scope filters by tenant_id
  - Navigation icon: CurrencyDollar
  - Navigation sort: 7

- **Form Schema:** `app/Filament/Client/Resources/Opportunities/Schemas/OpportunityForm.php`
  - Three sections:
    1. **Opportunity Information:** lead (searchable), product (searchable), assigned user
    2. **Value & Stage:** value (EUR prefix), stage dropdown, probability (0-100%), expected close date
    3. **Additional Information:** notes, loss_reason (conditional on stage=closed_lost)
  - No tenant_id field (auto-filled)
  - Helper texts for all fields

- **Table Schema:** `app/Filament/Client/Resources/Opportunities/Tables/OpportunitiesTable.php`
  - Lead name with email as description
  - Product name (toggleable)
  - Value formatted as money (EUR)
  - Stage badge with colors:
    - proposal → info (blue)
    - negotiation → warning (amber)
    - closed_won → success (green)
    - closed_lost → danger (red)
  - Probability with % suffix
  - Expected close date (d/m/Y)
  - Assigned user (toggleable)
  - Default sort: created_at desc

- **Create Page:** `app/Filament/Client/Resources/Opportunities/Pages/CreateOpportunity.php`
  - Auto-fills tenant_id in `mutateFormDataBeforeCreate()`

### 3. Sales Funnel Widget

- **File:** `app/Filament/Client/Widgets/SalesFunnelWidget.php`
- **Type:** Bar Chart (ChartWidget)
- **Data:**
  - Shows opportunity count per stage
  - Stages: Proposal, Negotiation, Closed Won, Closed Lost
  - Color-coded bars matching stage badges
  - Filtered by current tenant
- **Sort:** 3

### 4. Revenue Forecast Widget

- **File:** `app/Filament/Client/Widgets/RevenueForecastWidget.php`
- **Type:** Stats Overview (4 stats)
- **Metrics:**
  1. **Predicted Revenue** (info)
     - Formula: Σ(value × probability / 100) for open opportunities
     - Description: "Based on probability weighted value"

  2. **Closed Revenue** (success)
     - Formula: Σ(value) for closed_won opportunities
     - Description: "Total closed won opportunities"

  3. **Open Opportunities** (warning)
     - Formula: COUNT(*) for proposal + negotiation
     - Description: "In proposal or negotiation"

  4. **Total Pipeline Value** (primary)
     - Formula: Σ(value) for all open opportunities
     - Description: "Sum of all open opportunities"
- **Sort:** 2
- **Formatting:** EUR currency with thousands separator

---

## Model Relationships Added

### Lead Model
- Added `opportunities()` HasMany relationship to Opportunity model

---

## File Structure

```
app/
├── Filament/Client/
│   ├── Resources/
│   │   ├── Products/
│   │   │   ├── ProductResource.php
│   │   │   ├── Pages/
│   │   │   │   ├── CreateProduct.php
│   │   │   │   ├── EditProduct.php
│   │   │   │   └── ListProducts.php
│   │   │   ├── Schemas/
│   │   │   │   └── ProductForm.php
│   │   │   └── Tables/
│   │   │       └── ProductsTable.php
│   │   └── Opportunities/
│   │       ├── OpportunityResource.php
│   │       ├── Pages/
│   │       │   ├── CreateOpportunity.php
│   │       │   ├── EditOpportunity.php
│   │       │   └── ListOpportunities.php
│   │       ├── Schemas/
│   │       │   └── OpportunityForm.php
│   │       └── Tables/
│   │           └── OpportunitiesTable.php
│   └── Widgets/
│       ├── SalesFunnelWidget.php
│       └── RevenueForecastWidget.php
├── Models/
│   ├── Product.php
│   └── Opportunity.php
└── database/
    └── migrations/
        ├── 2026_02_26_051638_create_products_table.php
        └── 2026_02_26_051646_create_opportunities_table.php
```

---

## Testing Checklist

- [ ] Create a product with all fields
- [ ] Upload product image
- [ ] Create opportunity linked to lead
- [ ] Create opportunity linked to product
- [ ] Change opportunity stage (proposal → negotiation → closed_won)
- [ ] Verify stage badge colors update correctly
- [ ] Check Sales Funnel Widget shows correct counts
- [ ] Verify Revenue Forecast Widget calculations:
  - [ ] Predicted revenue = weighted by probability
  - [ ] Closed revenue = only closed_won opportunities
  - [ ] Open opportunities count
  - [ ] Total pipeline value
- [ ] Verify tenant isolation (each tenant sees only their data)
- [ ] Test loss_reason field only visible when stage=closed_lost

---

## Business Rules Implemented

1. **Auto-fill tenant_id:** Both products and opportunities automatically get tenant_id from authenticated user
2. **Optional product:** Opportunities can exist without a product link
3. **Probability-based forecast:** Predicted revenue uses probability weighting (value × probability / 100)
4. **Stage-based filtering:** Widgets only count relevant stages for each metric
5. **Conditional fields:** Loss reason only shown/required when opportunity is lost
6. **Soft references:** Product deletion doesn't delete opportunities (sets product_id to null)
7. **Hard references:** Lead deletion cascades to delete all opportunities

---

## Next Steps

Continue to FASE 10: Dashboard Analítico (Analytical Dashboard)

---

**FASE 9 Status:** ✅ COMPLETE
