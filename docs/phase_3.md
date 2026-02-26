# FASE 3 - Módulo de Leads (CRUD Base)

**Status:** ✅ Completa
**Data de Conclusão:** 2026-02-25

## Objetivo
Criar sistema completo de gestão de leads com CRUD, relacionamentos, atividades automáticas e visualização detalhada.

## Implementações

### 1. Database Structure

#### Migration: `leads`
- **44 campos fillable** incluindo custom_fields (JSON)
- Soft deletes (`deleted_at`)
- Indexes otimizados: `tenant_id`, `assigned_user_id`, `pipeline_stage_id`, `email`

**Campos principais:**
- Identificação: `full_name`, `email`
- Contato: `phones` (JSON array), `whatsapps` (JSON array), `primary_whatsapp_index`
- Endereço: 10 campos (tipo, nome, número, complemento, bairro, cidade, etc)
- Gestão: `assigned_user_id`, `pipeline_stage_id`, `priority`, `tags` (JSON)
- Consentimento: `consent_status`, `consent_date`, `opt_out`, `do_not_contact`
- Custom: `notes`, `custom_fields` (JSON)

#### Migration: `lead_activities`
- Tracking de todas as ações nos leads
- Campos: `type`, `description`, `metadata` (JSON), `user_id`
- Tipos: created, updated, assigned, stage_changed, field_changed

#### Migration: `pipeline_stages`
- Etapas do funil
- Campos: `name`, `color`, `order`, `icon`, `sla_hours`
- Automações: `entry_automation`, `exit_automation` (JSON)

#### Refatoração: Phones/WhatsApps
- De: 20 colunas (`phone_1` a `phone_10`, `whatsapp_1` a `whatsapp_10`)
- Para: 2 arrays JSON (`phones`, `whatsapps`)
- Migration de dados existentes
- Conversão `primary_whatsapp_index` de 1-based para 0-based

### 2. Models

#### Lead Model
```php
- Fillable: 44 campos
- Casts: phones, whatsapps, tags, custom_fields (array)
- Relationships: tenant, assignedUser, pipelineStage, activities
- Mutators: setPhonesAttribute(), setWhatsappsAttribute()
  → Reindexam arrays (UUID keys → numeric indexes)
- Accessors: primary_whatsapp, first_phone, first_whatsapp
```

#### LeadActivity Model
```php
- Tracking automático via Observer
- Metadata JSON flexível
- Relationship: lead, user, tenant
```

#### PipelineStage Model
```php
- Relacionamento com leads
- Casts para automations (JSON)
```

### 3. LeadObserver
Registro automático de atividades:
- ✅ `created` - Quando lead é criado
- ✅ `updated` - Detecta campos alterados (before/after)
- ✅ `assigned` - Quando operador muda
- ✅ `stage_changed` - Quando etapa muda

### 4. Filament LeadResource

#### LeadForm (6 Sections organizadas)
1. **Basic Information** - Nome, email, source
2. **Contact Information** - Phones/WhatsApps com Repeater
   - Repeater dinâmico (não limitado a 10)
   - `->simple()` syntax
   - `->live(onBlur: true)` para reatividade
   - Select de WhatsApp primário com opções dinâmicas
3. **Address** - Endereço completo (collapsible)
4. **Lead Management** - Operador, stage, priority, tags
5. **Consent & Privacy** - GDPR compliance
6. **Additional Information** - Notes, custom fields

#### LeadsTable
**Colunas:**
- full_name, email, primary_whatsapp (copyable)
- assignedUser.name, pipelineStage.name (badge)
- priority (badge colorido), source (badge)
- city, opt_out, do_not_contact
- created_at, updated_at

**Filtros:**
- SelectFilter: assigned_user, pipeline_stage, source, priority
- Filter: opt_out, do_not_contact
- TrashedFilter (soft deletes)

**Bulk Actions:**
- Assign to User
- Change Priority
- Delete/ForceDelete/Restore

#### LeadInfolist (7 Sections com ícones)
1. 👤 **Basic Information** - Nome, email, source, created_at
2. 📞 **Contact Information** - Phones, WhatsApps em badges
3. 📍 **Address** - Endereço completo (collapsed por padrão)
4. 💼 **Lead Management** - Operador, stage, priority, tags
5. 🛡️ **Consent & Privacy** - Status consentimento, opt-out
6. 📄 **Additional Information** - Notes, custom fields
7. ℹ️ **Metadata** - Timestamps, deleted_at

### 5. Fixes Críticos

#### Fix 1: UUID Keys do Repeater
**Problema:** Filament Repeater usa UUID como keys, causava erro "Out of range value" no `primary_whatsapp_index` (tinyInteger).

**Solução:**
```php
// Mutators no Lead Model
public function setPhonesAttribute($value): void {
    $this->attributes['phones'] = !empty($value)
        ? json_encode(array_values($value))  // Reindexação!
        : null;
}
```

#### Fix 2: Live Reactivity
**Problema:** Select de WhatsApp primário não atualizava ao adicionar novos números.

**Solução:**
```php
Repeater::make('whatsapps')
    ->simple(
        TextInput::make('whatsapp')
            ->live(onBlur: true)  // ← Atualiza ao perder foco
    )
    ->live()  // ← Atualiza ao adicionar/remover
```

#### Fix 3: Navigation Visibility
**Problema:** Menu Leads não aparecia.

**Causa:** Users criados sem `assignRole()`.

**Solução:** Fix no DemoTenantsSeeder.

## Arquivos Criados/Modificados

### Migrations
- `2026_02_25_220919_create_pipeline_stages_table.php`
- `2026_02_25_220920_create_leads_table.php`
- `2026_02_25_220948_create_lead_activities_table.php`
- `2026_02_25_223938_refactor_leads_phones_to_json.php`

### Models
- `app/Models/Lead.php`
- `app/Models/LeadActivity.php`
- `app/Models/PipelineStage.php`

### Observers
- `app/Observers/LeadObserver.php`

### Filament Resources
- `app/Filament/Client/Resources/Leads/LeadResource.php`
- `app/Filament/Client/Resources/Leads/Schemas/LeadForm.php`
- `app/Filament/Client/Resources/Leads/Schemas/LeadInfolist.php`
- `app/Filament/Client/Resources/Leads/Tables/LeadsTable.php`

### Filament Pages
- `app/Filament/Client/Resources/Leads/Pages/CreateLead.php`
- `app/Filament/Client/Resources/Leads/Pages/EditLead.php`
- `app/Filament/Client/Resources/Leads/Pages/ListLeads.php`
- `app/Filament/Client/Resources/Leads/Pages/ViewLead.php`

### Seeders
- `database/seeders/DemoTenantsSeeder.php` (fix assignRole)

### Providers
- `app/Providers/AppServiceProvider.php` (Observer registration)

## Funcionalidades Especiais

### Repeater Dinâmico
- ✅ Não limitado a 10 items
- ✅ `->simple()` para entrada direta
- ✅ `->itemLabel()` para preview
- ✅ `->collapsible()` para economizar espaço
- ✅ Auto-fill de tenant_id nos mutators

### Primary WhatsApp
- ✅ Select dinâmico mostrando todos os WhatsApps
- ✅ Atualiza em tempo real (live)
- ✅ Accessor `getPrimaryWhatsAppAttribute()` no model
- ✅ Tabela mostra WhatsApp primário correto

### Activity Timeline
- ✅ Registro automático via Observer
- ✅ Metadata JSON com before/after values
- ✅ User_id do autor
- ✅ Tipos específicos por ação

### Custom Fields
- ✅ KeyValue component do Filament V5
- ✅ Campos dinâmicos sem alterar schema
- ✅ JSON storage flexível

## Verificações Realizadas

✅ CRUD completo funcionando
✅ Repeaters salvando/carregando corretamente
✅ Primary WhatsApp sendo salvo e exibido
✅ Activities sendo registradas automaticamente
✅ Filtros e bulk actions funcionando
✅ Infolist mostrando todos os dados
✅ Soft deletes funcionando
✅ Permissions respeitadas (operador só vê seus leads)
✅ UUID keys convertidas para numeric indexes

## Estatísticas

- **18 arquivos** criados/modificados
- **1.470 linhas** de código adicionadas
- **4 migrations** criadas
- **3 models** implementados
- **1 observer** para tracking
- **6 sections** no formulário
- **7 sections** na infolist
- **44 campos** fillable no Lead

## Próximos Passos

→ **FASE 4:** Pipeline Kanban

---

**Commit:** `feat: Implementar FASE 3 - Módulo de Leads (CRUD Base)`
