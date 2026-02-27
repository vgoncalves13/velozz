# VELOZZ.DIGITAL - Implementation Blueprint
## Multi-Tenant CRM with Integrated WhatsApp

---

## CURRENT PROJECT STATE

**Project located at:** `/home/victor/Projetos/velozz`

### ✅ Implemented:
- Laravel 12.53.0 installed and configured
- Filament V5.2.3 installed
- Laravel Sail configured (MySQL, Redis, Mailpit, Meilisearch)
- AdminPanel Filament created at `/admin`
- ClientPanel Filament created at `/app`
- `.env` file configured
- Standard Laravel migrations (users, cache, jobs)
- **PHASE 0:** Complete
- **PHASE 1:** Complete (Multi-Tenancy Core)

### ❌ Not yet implemented:
- Spatie Permission - Not configured for RBAC
- Leads module - No domain tables created
- Business models - Only basic models exist
- Maatwebsite Excel - Installed but not used
- Laravel Reverb - Configured but not used
- WhatsApp Mock - Not created
- Dashboard widgets - Not created

**Conclusion:** Project foundation complete. Phase 1 (Multi-Tenancy) complete. Ready for Phase 2.

---

## CONTEXT

VELOZZ.DIGITAL is a multi-tenant SaaS CRM platform that allows companies to manage leads, WhatsApp conversations (Z-API), kanban pipeline, and operator teams. Each client (tenant) accesses via isolated subdomain (client.velozz.digital) with completely segregated data.

**Motivation:** Create a modern, minimalist, and professional CRM focused on lead management with WhatsApp as the primary communication channel.

**Expected outcome:** Functional system in Laravel + Filament V5, multi-tenant, responsive, with real-time inbox, intelligent spreadsheet import, and analytical dashboard.

---

## CRITICAL ARCHITECTURAL DECISIONS

### 1. Multi-Tenancy
- **Approach:** Single Database with `tenant_id` in all tables
- **Library:** Custom implementation (not using stancl/tenancy's multi-database)
- **Identification:** Subdomain (client.velozz.digital)
- **Middleware:** Resolves tenant before any controller

### 2. Confirmed Tech Stack
- **Backend:** Laravel 12.x + Filament V5 (PHP 8.2+)
- **Frontend:** Blade + Livewire 3 (integrated with Filament V5) + Tailwind CSS 4
- **Database:** MySQL 8.0+ (via Sail)
- **Cache/Session:** Redis (via Sail)
- **Queues:** Redis + Horizon
- **WebSocket:** Laravel Reverb (native Laravel 11) for real-time inbox
- **Dev Environment:** Laravel Sail (Docker)
- **Dev Email:** Mailpit (already configured in Sail)
- **Prod Email:** Amazon SES

### 3. Filament V5 - MANDATORY RULES
⚠️ **CRITICAL:** ALWAYS use Filament V5 components. NEVER use v3 or earlier.

**Mandatory checks:**
- Namespace: `Filament\` (v5) and NOT `Filament\` with v3 imports
- Resources: `Filament\Resources\Resource`
- Forms: `Filament\Forms\Components\*`
- Tables: `Filament\Tables\Columns\*` for columns
- **Actions: `Filament\Actions\*` (NOT `Filament\Tables\Actions\*`)** ⚠️
- ALWAYS consult: https://filamentphp.com/docs/5.x in case of doubt

**CRITICAL - Actions Namespace:**
```php
// ✅ CORRECT (V5)
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

// ❌ WRONG (V3/V4)
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
```

**Table API V5:**
- Use `->recordActions([])` not `->actions([])`
- Use `->toolbarActions([])` for bulk actions
- Use `TextColumn::make('status')->badge()` not `BadgeColumn`
- Use `->color(fn (string $state): string => match($state) {...})` not `->colors([])`

### 4. Panel Organization
Filament V5 supports multiple panels. We'll use:
- **Admin Master Panel:** `/admin` - Global management (all tenants)
- **Client Panel:** `/app` - Tenant CRM (default after login)

---

## DATABASE STRUCTURE

### Multi-Tenant Tables (with tenant_id)

#### `tenants`
- id, name, slug (unique), domain, status (trial/active/suspended/blocked)
- plan_id, trial_ends_at, subscription_ends_at
- admin_name, admin_email, admin_phone
- settings (JSON: schedule, logo, colors, webhooks)
- created_at, updated_at

#### `users`
- id, tenant_id (nullable for Admin Master)
- name, email, password, phone
- role (admin_master, admin_cliente, supervisor, operador, financeiro)
- status (active, invited, suspended, temporary)
- photo, two_factor_secret, two_factor_confirmed_at
- invite_token, invite_expires_at
- last_login_at, created_at, updated_at

#### `leads`
- id, tenant_id
- full_name (required), email
- phones (JSON array - dynamic list of phone numbers)
- whatsapps (JSON array - dynamic list of WhatsApp numbers)
- primary_whatsapp_index (index of primary WhatsApp in array)
- street_type, street_name, number, complement
- neighborhood, district, region, city, postal_code
- country (default: 'Portugal')
- source (import/manual/api/form)
- assigned_user_id (FK users), pipeline_stage_id (FK)
- tags (JSON array), priority (low/medium/high/urgent)
- consent_status (pending/consented/refused)
- consent_date, opt_out (boolean), opt_out_reason, opt_out_date
- do_not_contact (boolean)
- notes (text), custom_fields (JSON)
- created_at, updated_at, deleted_at

#### `lead_activities`
- id, tenant_id, lead_id
- type (created/message_sent/message_received/kanban_move/field_changed/note/transfer/import)
- description, metadata (JSON: field, previous_value, new_value, etc)
- user_id (who did it), created_at

#### `pipeline_stages`
- id, tenant_id
- name, color, order, icon
- sla_hours (nullable)
- entry_automation (JSON: template_id, operator_id, tags)
- exit_automation (JSON: webhook_url, custom_fields)
- created_at, updated_at

#### `whatsapp_instances`
- id, tenant_id
- instance_id (Z-API), token (Z-API)
- status (disconnected/connecting/connected/error)
- qr_code (nullable, temporary), phone_number
- webhook_url, last_connected_at, created_at, updated_at

#### `whatsapp_messages`
- id, tenant_id, lead_id, whatsapp_instance_id
- type (text/image/audio/pdf/internal_note)
- direction (incoming/outgoing)
- content (text), media_url (nullable)
- status (pending/sent/delivered/read/failed)
- error_message (nullable)
- remote_message_id (Z-API ID)
- sent_by_user_id (nullable for incoming)
- created_at, updated_at

#### `whatsapp_templates`
- id, tenant_id
- name, content (supports {name}, {company}, {operator}, {link}, {date}, {product})
- active (boolean)
- trigger_on (manual_creation/import/stage_id)
- created_at, updated_at

#### `products`
- id, tenant_id
- name, title, description, category
- price, currency (default: 'EUR'), unit
- status (active/inactive), image_url
- created_at, updated_at

#### `opportunities`
- id, tenant_id, lead_id, product_id
- amount, stage (proposal/negotiation/closed/lost)
- probability (0-100), expected_close_date (date)
- lost_reason (nullable), responsible_id (FK users)
- notes, created_at, updated_at, closed_at

#### `imports`
- id, tenant_id, user_id
- filename, type (xlsx/csv/url)
- total_rows, imported, duplicates, errors
- mapping (JSON), deduplication_rules (JSON)
- assigned_operator_id (nullable), tags (JSON)
- status (pending/processing/completed/failed)
- report (JSON: errors per row), created_at, updated_at

#### `audit_logs`
- id, tenant_id (nullable for Admin Master actions)
- user_id, action, entity, entity_id
- previous_data (JSON), new_data (JSON)
- ip_address, user_agent
- created_at (immutable, no updated_at)

#### `plans`
- id, name, price, currency
- leads_limit_per_month, messages_limit_per_day
- operators_limit, whatsapp_instances_limit
- trial_days, created_at, updated_at

### Global Tables (without tenant_id)

#### `domains`
- id, tenant_id, domain
- created_at, updated_at
- (For wildcard domain management by custom implementation)

---

## IMPLEMENTATION PHASES

### PHASE 0: Initial Project Setup ✅ COMPLETE
**Objective:** Complete Laravel + Sail + Filament V5 base structure

**Status:** ✅ COMPLETE

### PHASE 1: Multi-Tenancy Core ✅ COMPLETE
**Objective:** Implement tenant system with subdomain identification

**Status:** ✅ COMPLETE

See PHASE1_COMPLETE.md for testing instructions.

### PHASE 2: Authentication and RBAC
**Objective:** Permission system with 5 profiles (Admin Master, Client Admin, Supervisor, Operator, Financial)

**Tasks:**

1. **Configure Spatie Permission:**
   ```bash
   php artisan migrate
   ```

2. **Create Roles and Permissions Seeder:**
   ```php
   // database/seeders/RolesAndPermissionsSeeder.php
   // Roles: admin_master, admin_cliente, supervisor, operador, financeiro
   // Permissions: create_lead, edit_lead, delete_lead, send_message,
   //              view_dashboard, manage_operators, configure_whatsapp, etc.
   ```

3. **Policies:**
   - `TenantPolicy` - Only Admin Master can create/edit tenants
   - `LeadPolicy` - Operator only sees leads assigned to them
   - `WhatsAppConfigPolicy` - Only Client Admin can access QR Code

4. **Customize Filament User:**
   ```php
   // app/Models/User.php
   use HasRoles; // Spatie
   use FilamentUser; // Filament
   // Already implemented canAccessPanel() method
   ```

5. **Permission Middleware on Panels:**
   Already implemented in User model

6. **Invitation System:**
   - Create `InviteUserCommand` (sends email with token)
   - Create route `/accept-invite/{token}`
   - Controller: validates token, allows password definition, activates user

**Critical Files:**
- `database/seeders/RolesAndPermissionsSeeder.php`
- `app/Policies/TenantPolicy.php`
- `app/Policies/LeadPolicy.php`
- `app/Models/User.php` ✅
- `app/Console/Commands/InviteUserCommand.php`
- `app/Http/Controllers/AcceptInviteController.php`

---

### PHASE 3: Leads Module (Base CRUD)
**Objective:** Create, view, edit, and delete leads with detailed fields

**Tasks:**

1. **Migration `leads`:**
   - All fields as per specification
   - Indexes: tenant_id, responsible_id, whatsapp_1, email
   - Soft deletes

2. **Lead Model:**
   ```php
   // app/Models/Lead.php
   protected $fillable = [...all fields];
   protected $casts = [
       'tags' => 'array',
       'custom_fields' => 'array',
       'consent_date' => 'date',
       'opt_out_date' => 'date',
   ];

   public function responsible() { return $this->belongsTo(User::class); }
   public function activities() { return $this->hasMany(LeadActivity::class); }
   public function tenant() { return $this->belongsTo(Tenant::class); }
   ```

3. **LeadResource (Filament Client Panel):**
   - Full CRUD with all fields
   - Filters: responsible, source, priority, opt_out, do_not_contact
   - Actions: View, Edit, Delete
   - Bulk Actions: Assign operator, Add tags

4. **ViewLead Page (Activity Timeline):**
   - Infolist with all lead information
   - Widget: Activity timeline (LeadActivitiesWidget)

5. **Observer LeadObserver:**
   - Log creation
   - Log field changes
   - Log transfers

**Critical Files:**
- `database/migrations/*_create_leads_table.php`
- `app/Models/Lead.php`
- `app/Models/LeadActivity.php`
- `app/Filament/Client/Resources/LeadResource.php`
- `app/Observers/LeadObserver.php`

---

### PHASE 4: Kanban Pipeline
**Objective:** Stage management and drag-and-drop lead movement

**Tasks:**

1. **Migration `pipeline_stages`**
2. **PipelineStage Model**
3. **PipelineStageResource (Filament)**
4. **KanbanBoard Page (Custom Filament + Livewire)**
5. **Livewire KanbanBoard Component with drag-and-drop**
6. **Job ProcessStageAutomation**

**Critical Files:**
- `database/migrations/*_create_pipeline_stages_table.php`
- `app/Models/PipelineStage.php`
- `app/Filament/Client/Resources/PipelineStageResource.php`
- `app/Filament/Client/Pages/KanbanBoard.php`
- `app/Livewire/KanbanBoard.php`
- `app/Jobs/ProcessStageAutomation.php`

---

### PHASE 5: Spreadsheet Import
**Objective:** Upload and intelligent mapping of .xlsx/.csv with deduplication

**Tasks:**

1. **Migration `imports`**
2. **Import Model**
3. **ImportLeadsWizard (Filament Multi-Step Form)**
4. **Job ProcessImport**
5. **NormalizationHelper**
6. **Automatic Mapping Suggestion**

---

### PHASE 6: WhatsApp (Z-API Mock)
**Objective:** Simulate Z-API integration for development without real instances

**Tasks:**

1. **Migrations: whatsapp_instances, whatsapp_messages, whatsapp_templates**
2. **Create Mock Z-API (Fake Service)**
3. **ZApiServiceInterface**
4. **Models: WhatsAppInstance, WhatsAppMessage, WhatsAppTemplate**
5. **WhatsAppConfigPage (Filament)**
6. **WhatsAppTemplateResource (Filament)**
7. **Job SendWhatsAppMessage**
8. **TemplateHelper**

---

### PHASE 7: WhatsApp Inbox (Real-Time)
**Objective:** Chat interface with WebSocket for real-time conversations

**Tasks:**

1. **Configure Laravel Reverb**
2. **Events: MessageReceived, MessageSent**
3. **InboxPage (Filament Custom Page + Livewire)**
4. **Livewire InboxConversation Component**
5. **ZApiWebhookController**
6. **Public webhook route**
7. **SimulateIncomingMessage Command**

---

### PHASE 8: Operators and Team Management
**Objective:** Operator CRUD, invitation system, and lead assignment

**Tasks:**

1. **UserResource (Filament Client Panel)**
2. **Job SendInviteEmail**
3. **Mail InviteMail**
4. **Controller AcceptInviteController**
5. **View accept-invite.blade.php**
6. **Widget TeamPerformanceWidget**

---

### PHASE 9: Products, Opportunities, and Sales Funnel
**Objective:** Product management and linking to leads as opportunities

**Tasks:**

1. **Migrations: products, opportunities**
2. **Models: Product, Opportunity**
3. **ProductResource (Filament)**
4. **OpportunityResource (Filament)**
5. **Widget SalesFunnelWidget**
6. **Widget RevenueForecastWidget**

---

### PHASE 10: Analytical Dashboard
**Objective:** Widgets and KPIs for decision making

**Tasks:**

1. **Client Dashboard (widgets):**
   - StatsOverviewWidget
   - LeadsBySourceChart
   - ResponseRateChart
   - KanbanFunnelWidget
   - AverageResponseTimeWidget
   - OperatorRankingWidget

2. **Admin Master Dashboard:**
   - AdminStatsWidget
   - TenantUsageChart
   - ImportsT

odayWidget
   - AlertsWidget

---

### PHASE 11: Plans, Limits, and Billing
**Objective:** Plan system with limits and usage control

**Tasks:**

1. **Migration `plans`**
2. **Plan Model**
3. **Add `plan_id` to tenants table**
4. **PlanResource (Filament Admin Panel)**
5. **Middleware CheckTenantLimits**
6. **Widget UsageOverviewWidget**
7. **Command CheckSubscriptions**
8. **Prepare payments table (optional)**

---

### PHASE 12: Audit, Logs, and GDPR
**Objective:** Immutable log system and GDPR compliance

**Tasks:**

1. **Migration `audit_logs`**
2. **AuditLog Model**
3. **AuditHelper**
4. **Integrate AuditHelper at critical points**
5. **AuditLogResource (Filament)**
6. **GDPR settings in Tenant**
7. **Command GdprCleanup**

---

### PHASE 13: Tenant Settings and Webhooks
**Objective:** Advanced settings panel and outbound webhooks

**Tasks:**

1. **TenantSettingsPage (Filament Client Panel)**
2. **Job DispatchWebhook**
3. **Integrate webhooks in events**
4. **API Key Generation**

---

### PHASE 14: Polish and UX
**Objective:** Visual improvements, notifications, and user experience

**Tasks:**

1. **Customize Filament Panels (colors, logo)**
2. **Filament Notifications (real-time)**
3. **Loading States and Skeleton**
4. **Empty States**
5. **Guided Tour (optional)**
6. **Tooltips and Help Text**
7. **Mobile Responsiveness**
8. **Favicon and Metadata**

---

### PHASE 15: Tests and Documentation
**Objective:** Ensure quality and facilitate future maintenance

**Tasks:**

1. **Feature Tests (main):**
   - TenantTest
   - LeadTest
   - ImportTest
   - WhatsAppTest
   - KanbanTest
   - PermissionTest

2. **README.md**
3. **Technical Documentation:**
   - architecture.md
   - api.md
   - deploy.md
   - zapi-integration.md
   - filament-v5.md

4. **Complete Seeders:**
   - DemoSeeder ✅

---

## CRITICAL BUSINESS RULES (REMINDERS)

1. ⛔ Lead with `opt_out = true` or `do_not_contact = true` → NEVER send automatic message
2. ⏰ Messages only sent within configured tenant schedule
3. 🚦 Rate limit: queue + configurable cadence per tenant
4. 🔒 Accounts `status = temporary` → login allowed, but no sending messages or importing
5. 📞 Deduplication: never create lead with same `whatsapp_1` if rule active
6. 🎯 Stage automation triggers only ONCE per passage
7. 🚫 Operator DOES NOT access QR Code or tenant settings
8. 📝 Audit logs are IMMUTABLE (no edit/delete)
9. 🗃️ Custom fields = JSONB (no schema changes)
10. 🔄 Webhooks: queue + 3 attempts with exponential backoff

---

## FILAMENT V5 - MANDATORY CHECKLIST

Before writing any Filament code, ALWAYS verify:

✅ Use `Filament\Resources\Resource` (V5)
✅ Forms: `Filament\Forms\Components\*`
✅ Tables: `Filament\Tables\Columns\*` and `Filament\Tables\Actions\*`
✅ Widgets: `Filament\Widgets\*`
✅ Notifications: `Filament\Notifications\Notification`
✅ KeyValue field for custom_fields (V5 feature)
✅ Multi-step forms with `Forms\Components\Wizard`
✅ Consult docs: https://filamentphp.com/docs/5.x

❌ NEVER use imports from v3 or earlier
❌ NEVER use deprecated methods

---

## RECOMMENDED IMPLEMENTATION ORDER

1. ✅ **PHASE 0:** Initial setup - Sail + Filament V5 + Tenancy
2. ✅ **PHASE 1:** Multi-Tenancy Core - Subdomain identification
3. ⏭️ **PHASE 2:** Authentication and RBAC - 5 profiles + invites
4. ⏭️ **PHASE 3:** Leads Module - Complete CRUD
5. ⏭️ **PHASE 4:** Kanban Pipeline - Drag-and-drop + automations
6. ⏭️ **PHASE 5:** Spreadsheet Import - Upload + mapping + normalization
7. ⏭️ **PHASE 6:** WhatsApp Mock - Simulate Z-API + templates
8. ⏭️ **PHASE 7:** Real-Time Inbox - WebSocket + chat interface
9. ⏭️ **PHASE 8:** Operators - Team management + invites
10. ⏭️ **PHASE 9:** Products and Opportunities - Sales funnel
11. ⏭️ **PHASE 10:** Dashboard - Widgets and KPIs
12. ⏭️ **PHASE 11:** Plans and Limits - Usage control
13. ⏭️ **PHASE 12:** Audit and GDPR - Immutable logs + compliance
14. ⏭️ **PHASE 13:** Tenant Settings - Webhooks + API Key
15. ⏭️ **PHASE 14:** Polish and UX - Notifications + empty states
16. ⏭️ **PHASE 15:** Tests and Documentation - Quality + deploy

---

## ATTENTION POINTS

### Performance
- Indexes on: tenant_id (all tables), whatsapp_1, email, responsible_id
- Eager loading: always load relationships (.with(['responsible', 'tenant']))
- Cache: dashboard stats (5 minutes)
- Queue: imports, message sending, webhooks

### Security
- ALWAYS validate tenant_id in queries (middleware already does, but validate)
- Sanitize inputs (XSS) - Filament already does, but careful with custom_fields
- Rate limit on public webhooks
- CSRF protection on routes
- 2FA mandatory for Admin Master and Client Admin

### Scalability
- Queue workers: multiple workers for imports
- Redis for cache and sessions
- Prepare for horizontal scaling (no file sessions)
- Database read replicas (future)

---

## NEXT STEPS POST-MVP

- [ ] Public REST API (with tenant API Key)
- [ ] Real Z-API integration (replace Mock)
- [ ] Public forms for lead capture
- [ ] Stripe integration for payments
- [ ] Mobile app (Flutter/React Native)
- [ ] Exportable PDF reports
- [ ] Email campaigns (in addition to WhatsApp)
- [ ] AI for response suggestions in inbox
- [ ] Configurable chatbot
- [ ] Integration with Google Sheets (auto sync)

---

**This blueprint is ready for implementation. Each phase is incremental and individually testable.**
