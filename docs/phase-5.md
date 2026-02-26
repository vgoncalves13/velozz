# FASE 5 - Importação de Planilhas

**Status:** ✅ Completa  
**Data de Conclusão:** 2026-02-26

---

## Objetivo

Implementar sistema completo de importação de leads via Excel (.xlsx, .xls) e CSV, com:
- Upload e processamento em background
- Preview visual dos dados
- Mapeamento automático e manual de colunas
- Normalização de dados
- Deduplicação inteligente
- Histórico de importações

---

## Componentes Implementados

### 1. Database Migration
**Arquivo:** `database/migrations/2026_02_26_005205_create_imports_table.php`

```php
Schema::create('imports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('filename');
    $table->enum('type', ['xlsx', 'csv', 'url'])->default('xlsx');
    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
    $table->integer('total_rows')->default(0);
    $table->integer('imported')->default(0);
    $table->integer('duplicated')->default(0);
    $table->integer('errors')->default(0);
    $table->json('mapping')->nullable();
    $table->json('deduplication_rules')->nullable();
    $table->json('tags')->nullable();
    $table->json('report')->nullable();
    $table->foreignId('assigned_operator_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

### 2. Model Import
**Arquivo:** `app/Models/Import.php`

- Usa `HasTenantScope` para isolamento automático
- Casts para arrays: mapping, deduplication_rules, tags, report
- Relacionamentos: tenant, user, assignedOperator

### 3. Job ProcessImport
**Arquivo:** `app/Jobs/ProcessImport.php`

**Responsabilidades:**
- Ler arquivo Excel/CSV usando Maatwebsite/Excel
- Aplicar mapeamento de colunas
- Normalizar dados (phones, emails, names)
- Verificar deduplicação
- Criar leads e activities
- Gerar relatório detalhado

**Métodos principais:**
- `handle()` - Processa importação completa
- `mapRow()` - Mapeia linha para dados do Lead
- `checkDuplicate()` - Verifica duplicatas (corrigido para respeitar tenant)
- `autoMapHeaders()` - Auto-mapeamento inteligente de colunas

### 4. Helper NormalizationHelper
**Arquivo:** `app/Helpers/NormalizationHelper.php`

**Métodos:**
- `normalizePhone()` - Converte para formato E.164 (+351...)
- `normalizeEmail()` - Lowercase e trim
- `capitalizeName()` - Capitaliza nomes próprios
- `cleanValue()` - Remove espaços extras e line breaks

### 5. Filament Page ImportLeads
**Arquivo:** `app/Filament/Client/Pages/ImportLeads.php`

**Wizard de 3 Etapas:**

#### Step 1: Upload
- FileUpload component
- Aceita: .xlsx, .xls, .csv
- Limite: 10MB
- Salva em: `storage/app/imports/`
- AfterStateUpdated: Lê headers e gera preview

#### Step 2: Mapping
- **Preview Table:** Primeiras 20 linhas com scroll
- **Auto-mapping:** Detecta automaticamente colunas conhecidas
- **Manual Mapping:** Selects para ajustar cada coluna
- Campos disponíveis: full_name, email, phones, whatsapps, address, city, etc.

#### Step 3: Settings
- Deduplication Rules: email, phone, whatsapp
- Assign to Operator: Atribuição em massa
- Tags: Adicionar tags a todos os leads

**Métodos auxiliares:**
- `getAvailableFields()` - Lista campos do Lead
- `autoMapHeaders()` - Lógica de auto-mapeamento (PT/EN)

### 6. Blade Component Preview
**Arquivo:** `resources/views/filament/components/import-preview.blade.php`

- Tabela responsiva com scroll vertical (max-h-96)
- Headers sticky
- Contraste otimizado (dark mode ready)
- Coluna # destacada
- Indicador de linhas restantes

### 7. View History
**Arquivo:** `resources/views/filament/client/pages/import-leads.blade.php`

Mostra histórico de importações:
- Status: pending, processing, completed, failed
- Resultados: ✓ imported, ⊘ duplicated, ✗ errors
- Data relativa (diffForHumans)
- Empty state para primeira importação

---

## Fluxo de Importação

```
1. User Upload
   ↓
2. File Saved (storage/app/imports/)
   ↓
3. Read Headers + Preview (20 rows)
   ↓
4. Auto-map columns
   ↓
5. User reviews/adjusts mapping
   ↓
6. User selects settings
   ↓
7. Create Import record
   ↓
8. Dispatch ProcessImport job
   ↓
9. Job processes in background
   ↓
10. Results saved in database
    ↓
11. User sees history updated
```

---

## Auto-Mapping Inteligente

### Português
- nome, nome completo → `full_name`
- telefone, celular → `phones`
- cidade → `city`
- estado → `region`
- cep, código postal → `postal_code`
- endereço, rua → `street_name`
- número → `number`
- bairro → `district`
- país → `country`

### English
- name, full name → `full_name`
- phone, mobile → `phones`
- city → `city`
- state → `region`
- postal code, zip → `postal_code`
- address, street → `street_name`
- number → `number`
- neighborhood → `neighborhood`
- country → `country`

### Special
- email, e-mail → `email`
- whatsapp → `whatsapps`
- notas, notes, observações → `notes`
- empresa, company → `company_name`

Colunas não reconhecidas: `ignore`

---

## Deduplicação

### Regras Disponíveis
1. **Email** - Busca por email exato
2. **Phone** - Busca em JSON array de phones
3. **WhatsApp** - Busca em JSON array de whatsapps

### Lógica (Corrigida)
```php
Lead::where('tenant_id', $tenantId)
    ->where(function($q) {
        // OR conditions grouped
        $q->orWhere('email', $email)
          ->orWhereJsonContains('phones', $phone)
          ->orWhereJsonContains('whatsapps', $whatsapp);
    })
    ->first();
```

**Importante:** Query agrupa OR conditions para respeitar tenant_id.

---

## Normalização de Dados

### Phones
- Remove caracteres não-numéricos
- Detecta país (Portugal/Brasil)
- Converte para E.164:
  - `912345678` → `+351912345678`
  - `11987654321` → `+5511987654321`

### Emails
- `strtolower()` + `trim()`
- `JoAo@Example.COM` → `joao@example.com`

### Names
- Capitaliza primeira letra de cada palavra
- Mantém conectores em lowercase:
  - `joão silva de souza` → `João Silva de Souza`
  - `maria da costa` → `Maria da Costa`

---

## Activity Logging

Cada lead importado gera uma activity:

```php
LeadActivity::create([
    'tenant_id' => $tenantId,
    'lead_id' => $lead->id,
    'type' => 'created',
    'description' => 'Lead created via import',
    'metadata' => [
        'import_id' => $importId,
        'row' => $rowNumber,
    ],
    'user_id' => $userId,
]);
```

---

## Relatório de Importação

```php
[
    'row' => 2,
    'type' => 'success', // ou 'duplicate', 'error'
    'lead_id' => 123,
    'message' => 'Lead imported successfully',
]
```

---

## Problemas Resolvidos

### 1. Type Hint Incorreto
**Problema:** `Filament\Forms\Get` vs `Filament\Schemas\Components\Utilities\Get`  
**Solução:** Remover type hints dos closures

### 2. FileUpload Path
**Problema:** TemporaryUploadedFile object vs string  
**Solução:** 
```php
$filePath = is_string($state) 
    ? Storage::disk('local')->path($state)
    : $state->getRealPath();
```

### 3. Deduplicação Incorreta
**Problema:** Query sem agrupar OR conditions  
**Solução:** Usar `where(function($q) { ... })` para agrupar

### 4. Preview com Texto Invisível
**Problema:** Contraste ruim no modo claro  
**Solução:** `text-gray-900 dark:text-gray-200` nas células

---

## Arquivos Críticos

```
app/
├── Filament/Client/Pages/
│   └── ImportLeads.php              # Wizard page
├── Jobs/
│   └── ProcessImport.php            # Background job
├── Models/
│   └── Import.php                   # Import model
└── Helpers/
    └── NormalizationHelper.php      # Data normalization

database/migrations/
└── 2026_02_26_005205_create_imports_table.php

resources/views/
└── filament/
    ├── client/pages/
    │   └── import-leads.blade.php   # History view
    └── components/
        └── import-preview.blade.php # Preview table
```

---

## Testes Realizados

✅ Upload de arquivo Excel (.xlsx)  
✅ Upload de arquivo CSV  
✅ Preview de 20 linhas exibido corretamente  
✅ Auto-mapping de colunas PT/EN  
✅ Mapeamento manual funcional  
✅ Deduplicação por email respeitando tenant  
✅ Normalização de phones para E.164  
✅ Normalização de emails (lowercase)  
✅ Normalização de nomes (capitalize)  
✅ Import em background com queue  
✅ Activity logging criado para cada lead  
✅ Histórico de importações atualizado  
✅ Relatório detalhado gerado  
✅ Atribuição em massa de operador  
✅ Tags em massa aplicadas  

---

## Melhorias Futuras (Opcional)

- [ ] Suporte para múltiplas abas de Excel
- [ ] Import via URL (Google Sheets, etc)
- [ ] Agendamento de importações recorrentes
- [ ] Validação de dados antes de importar
- [ ] Preview de erros na interface
- [ ] Rollback de importação
- [ ] Export de template Excel
- [ ] Mapeamento salvo como preset
- [ ] Notificação por email ao concluir

---

## Dependências Utilizadas

- **maatwebsite/excel** (^3.1) - Leitura de Excel/CSV
- **propaganistas/laravel-phone** - Normalização de telefones
- **Filament V5** - Wizard, FileUpload, Forms
- **Laravel Queue** - Processamento em background

---

## Comandos Úteis

```bash
# Processar importações pendentes
php artisan queue:work --queue=default

# Limpar arquivos antigos (opcional)
php artisan import:cleanup --days=30

# Ver importações recentes
php artisan tinker
>>> Import::latest()->take(5)->get()
```

---

## Conclusão

A FASE 5 implementa um sistema robusto e profissional de importação de leads com:
- Interface intuitiva (3-step wizard)
- Preview visual dos dados
- Auto-mapping inteligente
- Normalização completa
- Deduplicação correta
- Processamento escalável em background

Sistema pronto para produção! ✅

**Próxima Fase:** FASE 6 - WhatsApp (Mock da Z-API)
