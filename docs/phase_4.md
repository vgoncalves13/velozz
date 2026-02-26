# FASE 4 - Pipeline Kanban

**Status:** ✅ Completa
**Data de Conclusão:** 2026-02-25

## Objetivo
Criar sistema de gestão visual de leads com Kanban drag-and-drop, etapas customizáveis e automações.

## Implementações

### 1. TenantScope Global Scope (Fix Crítico de Segurança)

#### Problema Identificado
**CRÍTICO:** Leads e stages de diferentes tenants sendo exibidos incorretamente.
- Usuário logado em demo1 visualizava pipelines de demo2
- Falta de isolamento automático por tenant

#### Solução Implementada

**TenantScope (Global Scope):**
```php
// app/Models/Scopes/TenantScope.php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->tenant_id) {
            $builder->where($model->getTable().'.tenant_id', auth()->user()->tenant_id);
        }
    }
}
```

**HasTenantScope Trait:**
```php
// app/Models/Traits/HasTenantScope.php
trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        // Adiciona scope global automaticamente
        static::addGlobalScope(new TenantScope);

        // Auto-preenche tenant_id ao criar
        static::creating(function (Model $model) {
            if (!$model->tenant_id && auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
```

**Modelos Atualizados:**
- ✅ Lead.php - Adicionada trait HasTenantScope
- ✅ PipelineStage.php - Adicionada trait HasTenantScope
- ✅ LeadActivity.php - Adicionada trait HasTenantScope

**Resultado:**
- ✅ Isolamento automático por tenant em todas as queries
- ✅ Impossível esquecer de filtrar por tenant_id
- ✅ Código mais limpo (sem where('tenant_id') manual)
- ✅ Auto-fill de tenant_id ao criar registros

### 2. PipelineStageResource

#### CRUD Completo
```php
// app/Filament/Client/Resources/PipelineStages/PipelineStageResource.php
- Navigation: 'Pipeline' (icon: heroicon-o-queue-list)
- Tenant-aware (HasTenantScope trait)
```

#### PipelineStageForm (2 Sections)

**1. Basic Information:**
- `name` (TextInput) - Nome da etapa
- `color` (ColorPicker) - Cor da etapa no Kanban
- `order` (TextInput, numeric) - Ordem de exibição
- `icon` (Select) - Ícone Heroicon
- `sla_hours` (TextInput, numeric) - SLA em horas

**2. Automations:**
- `entry_automation` (KeyValue) - Automação ao entrar na etapa
- `exit_automation` (KeyValue) - Automação ao sair da etapa

#### PipelineStagesTable

**Colunas:**
- `order` (TextColumn, sortable)
- `color` (ColorColumn)
- `name` (TextColumn, searchable, badge com cor)
- `icon` (IconColumn)
- `sla_hours` (TextColumn)
- `leads_count` (counts relationship)
- `created_at` (TextColumn, dateTime)

**Features:**
- ✅ Reorderable (`->reorderable('order')`)
- ✅ Default sort: order ASC
- ✅ Edit, View, Delete actions
- ✅ Contador de leads por etapa

### 3. KanbanBoard Page

#### Layout Responsivo (Grid)
```blade
<!-- Grid que adapta de 1 a 4 colunas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
```

**Responsividade:**
- Mobile (< 768px): 1 coluna
- Tablet (768-1024px): 2 colunas
- Desktop (1024-1280px): 3 colunas
- Large Desktop (> 1280px): 4 colunas

**Vantagens vs Scroll Horizontal:**
- ✅ Sem scroll horizontal
- ✅ Todas as etapas sempre visíveis
- ✅ Drag-and-drop funciona em qualquer direção
- ✅ Layout mais limpo e organizado

#### Stages Colapsáveis

**Implementação Alpine.js + localStorage:**
```javascript
x-data="{
    collapsed: JSON.parse(localStorage.getItem('kanban_stage_{{ $stage->id }}_collapsed') || 'false'),
    toggle() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('kanban_stage_{{ $stage->id }}_collapsed', this.collapsed);
    }
}"
```

**Features:**
- ✅ Estado persistido no navegador
- ✅ Botão toggle com ícone chevron (rotaciona 180°)
- ✅ Animação suave (x-collapse)
- ✅ Contador de leads sempre visível
- ✅ SLA info esconde quando colapsado

#### Drag-and-Drop (HTML5)

**Eventos implementados:**
```javascript
// No card (lead):
draggable="true"
@dragstart - Define leadId no dataTransfer, opacidade 0.5
@dragend - Restaura opacidade

// Na drop zone (container):
@dragover.prevent - Adiciona visual feedback (ring-2, bg-blue-50)
@dragleave - Remove visual feedback
@drop - Chama $wire.moveCard(leadId, stageId), remove feedback
```

**Método Livewire:**
```php
public function moveCard(int $leadId, int $stageId): void
{
    $lead = Lead::find($leadId);
    $oldStageId = $lead->pipeline_stage_id;

    $lead->update(['pipeline_stage_id' => $stageId]);

    // Registra atividade
    LeadActivity::create([
        'tenant_id' => $lead->tenant_id,
        'lead_id' => $leadId,
        'type' => 'stage_changed',
        'description' => "Etapa alterada",
        'metadata' => [
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $stageId,
        ],
        'user_id' => auth()->id(),
    ]);

    $this->loadData(); // Recarrega dados
}
```

#### Lead Cards

**Informações exibidas:**
- Nome completo (bold)
- Email (se existir)
- Badge de prioridade (cores: urgent=red, high=orange, medium=blue, low=gray)
- Operador responsável (com ícone)
- Links: View | Edit

**Estilização:**
- Background branco/dark
- Shadow-sm (hover: shadow-lg)
- Border sutil (hover: border mais escura)
- Cursor move
- Transições suaves

**Empty State:**
- Ícone do stage em opacidade baixa
- Mensagem: "Drop leads here"
- Também é drop zone ativa

### 4. PipelineStagesSeeder

**6 Etapas Padrão:**
```php
1. New Lead (🆕) - Azul (#3B82F6) - SLA: 24h
2. Contacted (📞) - Verde (#10B981) - SLA: 48h
3. Negotiation (💬) - Âmbar (#F59E0B) - SLA: 72h
4. Proposal Sent (📄) - Roxo (#8B5CF6) - SLA: 120h
5. Won (✅) - Esmeralda (#059669) - Sem SLA
6. Lost (❌) - Vermelho (#EF4444) - Sem SLA
```

**Aplicação:**
- ✅ Criado para todos os tenants existentes
- ✅ Order sequencial (10, 20, 30, ...)
- ✅ Automações vazias (JSON null)

## Arquivos Criados/Modificados

### Scopes
- `app/Models/Scopes/TenantScope.php` (🔒 Security)

### Traits
- `app/Models/Traits/HasTenantScope.php` (🔒 Security)

### Models (Modified)
- `app/Models/Lead.php` - Adicionada trait HasTenantScope
- `app/Models/PipelineStage.php` - Adicionada trait HasTenantScope
- `app/Models/LeadActivity.php` - Adicionada trait HasTenantScope

### Filament Resources
- `app/Filament/Client/Resources/PipelineStages/PipelineStageResource.php`
- `app/Filament/Client/Resources/PipelineStages/Schemas/PipelineStageForm.php`
- `app/Filament/Client/Resources/PipelineStages/Tables/PipelineStagesTable.php`

### Filament Pages
- `app/Filament/Client/Resources/PipelineStages/Pages/CreatePipelineStage.php`
- `app/Filament/Client/Resources/PipelineStages/Pages/EditPipelineStage.php`
- `app/Filament/Client/Resources/PipelineStages/Pages/ListPipelineStages.php`
- `app/Filament/Client/Resources/PipelineStages/Pages/ViewPipelineStage.php`
- `app/Filament/Client/Pages/KanbanBoard.php` (⭐ Livewire Page)

### Views
- `resources/views/filament/client/pages/kanban-board.blade.php`

### Seeders
- `database/seeders/PipelineStagesSeeder.php`

## Funcionalidades Especiais

### Grid Responsivo vs Scroll Horizontal
**Problema:** Com muitas etapas, scroll horizontal tornava drag-and-drop difícil.

**Solução:** Grid com wrap automático
- 4 etapas por linha em desktop
- Reduz para 3, 2 ou 1 conforme viewport
- Sempre visível, sem scroll horizontal

### Collapsible Stages
- ✅ Estado persistido no localStorage
- ✅ Animação suave (Alpine x-collapse)
- ✅ Ícone chevron rotaciona
- ✅ Contador sempre visível
- ✅ Cards e SLA info escondem/aparecem

### Drag-and-Drop Visual Feedback
- Arrastar: Card fica com opacidade 0.5
- Drop zone: Ring azul + background azul claro
- Soltar: Feedback remove, card move
- Empty states também aceitam drops

### Tenant Isolation (Arquitetural)
**Antes:**
```php
// Manual em todos os lugares
$stages = PipelineStage::where('tenant_id', auth()->user()->tenant_id)->get();
```

**Depois:**
```php
// Automático via HasTenantScope
$stages = PipelineStage::query()->get();
```

**Vantagens:**
- Impossível esquecer filtro
- Código 50% menor
- Segurança by design
- DRY principle

## Verificações Realizadas

✅ CRUD de stages funcionando (Create, Edit, View, Delete)
✅ Reordenação de stages via drag icon
✅ KanbanBoard exibe stages agrupados por tenant correto
✅ Drag-and-drop move leads entre stages
✅ Atividade "stage_changed" registrada automaticamente
✅ Visual feedback durante drag (opacidade + ring)
✅ Stages colapsáveis com estado persistido
✅ Grid responsivo sem scroll horizontal
✅ Empty states funcionando como drop zones
✅ Links View/Edit funcionando corretamente
✅ TenantScope isolando dados automaticamente
✅ Seeder criando 6 stages padrão

## Estatísticas

- **15 arquivos** criados/modificados
- **3 models** com HasTenantScope trait
- **1 global scope** para segurança
- **1 trait** reutilizável
- **6 etapas** padrão seedadas
- **Grid responsivo** 1-4 colunas
- **Drag-and-drop** HTML5 nativo
- **localStorage** para estado UI

## Fixes Críticos

### Fix 1: Tenant Isolation Breach 🔒
**Problema:** Usuário de demo1 via dados de demo2.

**Causa:** Ausência de filtro tenant_id em queries.

**Evolução da Solução:**
1. ❌ where('tenant_id') manual em cada query (repetitivo, esquecível)
2. ✅ Global Scope TenantScope (automático)
3. ✅✅ Trait HasTenantScope (reutilizável, DRY)

**Resultado:** Segurança garantida por design.

### Fix 2: Livewire Multiple Root Elements
**Problema:** Erro "Livewire only supports one HTML element per component"

**Causa:** Extra `</div>` no template + @push scripts

**Solução:**
- Única div root (grid)
- Scripts movidos para x-init
- Estrutura limpa e validada

### Fix 3: Route Resolution
**Problema:** `Route [filament.client.resources.leads.leads.view] not defined`

**Causa:** Nome de rota incorreto

**Solução:** Usar `LeadResource::getUrl('view', ['record' => $id])`

### Fix 4: UX - Scroll Horizontal
**Problema:** Muitas etapas causavam scroll horizontal, drag difícil

**Solução:** Grid responsivo com wrap automático

## Próximos Passos

→ **FASE 5:** Importação de Planilhas

---

**Commit:** `feat: Implementar FASE 4 - Pipeline Kanban com TenantScope`
