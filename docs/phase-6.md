# FASE 6 - WhatsApp (Mock da Z-API)

**Status:** ✅ Completa  
**Data de Conclusão:** 2026-02-26

---

## Objetivo

Implementar sistema completo de integração com WhatsApp via Z-API usando Mock Service para desenvolvimento, incluindo:
- Gerenciamento de instâncias WhatsApp
- Envio de mensagens text e media
- Templates com variáveis dinâmicas
- Página de configuração com QR Code
- Simulação completa da Z-API

---

## Componentes Implementados

### 1. Database Migrations

**whatsapp_instances:**
```php
Schema::create('whatsapp_instances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $table->string('instance_id')->unique(); // Z-API instance ID
    $table->string('token'); // Z-API token
    $table->enum('status', ['disconnected', 'connecting', 'connected', 'error'])->default('disconnected');
    $table->text('qr_code')->nullable(); // QR code base64 (temporary)
    $table->string('phone_number')->nullable();
    $table->string('webhook_url')->nullable();
    $table->timestamp('last_connected_at')->nullable();
    $table->timestamps();
});
```

**whatsapp_messages:**
```php
Schema::create('whatsapp_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
    $table->foreignId('whatsapp_instance_id')->constrained('whatsapp_instances')->cascadeOnDelete();
    $table->enum('type', ['text', 'image', 'audio', 'video', 'document', 'internal_note'])->default('text');
    $table->enum('direction', ['incoming', 'outgoing'])->default('outgoing');
    $table->text('content')->nullable();
    $table->string('media_url')->nullable();
    $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
    $table->text('error_message')->nullable();
    $table->string('remote_message_id')->nullable(); // Z-API message ID
    $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

**whatsapp_templates:**
```php
Schema::create('whatsapp_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $table->string('name');
    $table->text('content'); // Supports variables
    $table->boolean('active')->default(true);
    $table->string('trigger_on')->nullable(); // manual, lead_created, import, stage
    $table->foreignId('pipeline_stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
    $table->timestamps();
});
```

### 2. Models

**WhatsAppInstance:**
```php
class WhatsAppInstance extends Model
{
    use HasTenantScope;
    
    protected $table = 'whatsapp_instances';
    
    // Relationships
    public function tenant(): BelongsTo
    public function messages(): HasMany
    
    // Helper methods
    public function isConnected(): bool
    public function needsQrCode(): bool
}
```

**WhatsAppMessage:**
```php
class WhatsAppMessage extends Model
{
    use HasTenantScope;
    
    protected $table = 'whatsapp_messages';
    
    // Relationships
    public function tenant(): BelongsTo
    public function lead(): BelongsTo
    public function whatsappInstance(): BelongsTo
    public function sentBy(): BelongsTo
    
    // Helper methods
    public function isIncoming(): bool
    public function isOutgoing(): bool
    public function isDelivered(): bool
    public function hasFailed(): bool
}
```

**WhatsAppTemplate:**
```php
class WhatsAppTemplate extends Model
{
    use HasTenantScope;
    
    protected $table = 'whatsapp_templates';
    
    // Relationships
    public function tenant(): BelongsTo
    public function pipelineStage(): BelongsTo
    
    // Template rendering
    public function render(Lead $lead, ?User $operator = null): string
    {
        $variables = [
            '{name}' => $lead->full_name,
            '{company}' => $lead->tenant->name,
            '{operator}' => $operator?->name ?? 'Team',
            '{date}' => now()->format('d/m/Y'),
            // ...
        ];
        
        return str_replace(array_keys($variables), array_values($variables), $this->content);
    }
}
```

### 3. Z-API Service

**Interface:**
```php
interface ZApiServiceInterface
{
    public function generateQrCode(string $instanceId): array;
    public function getConnectionStatus(string $instanceId): array;
    public function sendMessage(string $instanceId, string $phone, string $message): array;
    public function sendMedia(string $instanceId, string $phone, string $mediaUrl, string $caption = ''): array;
    public function instanceExists(string $instanceId): bool;
    public function disconnect(string $instanceId): array;
}
```

**Mock Implementation:**
```php
class ZApiMockService implements ZApiServiceInterface
{
    public function generateQrCode(string $instanceId): array
    {
        return [
            'qrcode' => 'data:image/png;base64,...', // Fake QR code
            'status' => 'waiting_qr',
        ];
    }
    
    public function getConnectionStatus(string $instanceId): array
    {
        return [
            'status' => 'connected',
            'phone' => '+351912' . rand(100000, 999999),
        ];
    }
    
    public function sendMessage(string $instanceId, string $phone, string $message): array
    {
        usleep(500000); // 0.5s delay
        
        return [
            'messageId' => 'mock_' . Str::uuid(),
            'status' => 'sent',
            'timestamp' => time(),
        ];
    }
}
```

**Service Binding:**
```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \App\Services\ZApi\ZApiServiceInterface::class,
        \App\Services\ZApi\ZApiMockService::class // Mock for development
    );
}
```

### 4. SendWhatsAppMessage Job

```php
class SendWhatsAppMessage implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    
    public function __construct(
        public Lead $lead,
        public string $message,
        public ?int $userId = null,
        public string $type = 'text',
        public ?string $mediaUrl = null
    ) {}
    
    public function handle(ZApiServiceInterface $zapi): void
    {
        // 1. Get WhatsApp instance
        $instance = WhatsAppInstance::where('tenant_id', $this->lead->tenant_id)
            ->where('status', 'connected')
            ->first();
        
        // 2. Check opt-out
        if ($this->lead->opt_out || $this->lead->do_not_contact) {
            return;
        }
        
        // 3. Create message record
        $whatsappMessage = WhatsAppMessage::create([...]);
        
        // 4. Send via Z-API
        $response = $zapi->sendMessage($instance->instance_id, $phone, $this->message);
        
        // 5. Update message status
        $whatsappMessage->update(['status' => 'sent']);
        
        // 6. Register activity
        LeadActivity::create([...]);
    }
}
```

### 5. WhatsAppTemplate Resource

**Form:**
- Template name
- Message content with variable placeholders
- Active toggle
- Trigger type (manual, on create, on import, stage-based)
- Pipeline stage selector (conditional)
- Variables info placeholder

**Table:**
- Name (searchable, sortable)
- Content (truncated with tooltip)
- Active (icon column)
- Trigger (badge with colors)
- Pipeline stage
- Created date
- Filters: by status and trigger type

**Available Variables:**
- `{name}` - Lead's full name
- `{company}` - Tenant name
- `{operator}` - Assigned operator name
- `{date}` - Current date (d/m/Y)
- `{product}` - Product name (if applicable)
- `{link}` - Custom link (if applicable)

### 6. WhatsAppConfig Page

**Features:**
- Create WhatsApp instance (instance_id + token)
- Generate QR Code for connection
- Display QR Code with instructions
- Check connection status
- Show connection info (phone, last connected)
- Disconnect WhatsApp
- Visual status indicators
- Step-by-step instructions

**Status Flow:**
1. **No instance** → Button: Create Instance
2. **Disconnected** → Button: Connect WhatsApp
3. **Connecting** → Show QR Code + Button: Check Status
4. **Connected** → Show phone number + Button: Disconnect

**View Components:**
- Status card with colored badges
- QR code display (256x256px)
- Connection instructions
- Success/info alerts

---

## Template Variables System

### Rendering Process

```php
$template = WhatsAppTemplate::find(1);
$lead = Lead::find(1);
$operator = User::find(1);

$message = $template->render($lead, $operator);

// Input: "Hello {name}, welcome to {company}\! Your operator {operator} will contact you."
// Output: "Hello João Silva, welcome to ACME Corp\! Your operator Maria Santos will contact you."
```

### Variable Mapping

| Variable | Source | Example |
|----------|--------|---------|
| `{name}` | `$lead->full_name` | João Silva |
| `{company}` | `$lead->tenant->name` | ACME Corp |
| `{operator}` | `$operator->name` | Maria Santos |
| `{date}` | `now()->format('d/m/Y')` | 26/02/2026 |
| `{product}` | Custom | Product Name |
| `{link}` | Custom | https://... |

---

## Message Flow

```
1. User creates template or sends manual message
   ↓
2. Dispatch SendWhatsAppMessage job
   ↓
3. Job validates:
   - WhatsApp instance connected?
   - Lead has phone/whatsapp?
   - Lead not opted out?
   ↓
4. Create WhatsAppMessage record (status: pending)
   ↓
5. Call ZApi service (Mock or Real)
   ↓
6. Update message status (sent/failed)
   ↓
7. Register LeadActivity
   ↓
8. Retry on failure (3x with backoff)
```

---

## Mock vs Real Z-API

### Development (Mock)
```php
// app/Providers/AppServiceProvider.php
$this->app->bind(
    \App\Services\ZApi\ZApiServiceInterface::class,
    \App\Services\ZApi\ZApiMockService::class
);
```

**Mock Features:**
- Fake QR codes
- Simulated delays (0.5s text, 1s media)
- Random phone numbers
- Always returns success
- No real API calls

### Production (Real)
```php
// app/Providers/AppServiceProvider.php
$this->app->bind(
    \App\Services\ZApi\ZApiServiceInterface::class,
    \App\Services\ZApi\ZApiRealService::class // To be implemented
);
```

**Real Implementation (Future):**
```php
class ZApiRealService implements ZApiServiceInterface
{
    public function sendMessage(string $instanceId, string $phone, string $message): array
    {
        $response = Http::withHeaders([
            'Client-Token' => $this->getToken($instanceId),
        ])->post("https://api.z-api.io/instances/{$instanceId}/send-text", [
            'phone' => $phone,
            'message' => $message,
        ]);
        
        return $response->json();
    }
}
```

---

## Arquivos Críticos

```
app/
├── Models/
│   ├── WhatsAppInstance.php        # Instance model
│   ├── WhatsAppMessage.php         # Message model
│   └── WhatsAppTemplate.php        # Template model with render()
├── Services/ZApi/
│   ├── ZApiServiceInterface.php    # Contract
│   └── ZApiMockService.php         # Mock implementation
├── Jobs/
│   └── SendWhatsAppMessage.php     # Background sending
├── Filament/Client/
│   ├── Pages/
│   │   └── WhatsAppConfig.php      # Config page with QR code
│   └── Resources/WhatsAppTemplates/
│       ├── WhatsAppTemplateResource.php
│       ├── Schemas/WhatsAppTemplateForm.php
│       └── Tables/WhatsAppTemplatesTable.php
└── Providers/
    └── AppServiceProvider.php      # Service binding

database/migrations/
├── 2026_02_26_022950_create_whatsapp_instances_table.php
├── 2026_02_26_022957_create_whatsapp_messages_table.php
└── 2026_02_26_022957_create_whatsapp_templates_table.php

resources/views/filament/client/pages/
└── whatsapp-config.blade.php       # Config page view
```

---

## Bugs Corrigidos

### 1. Table Name Mismatch
**Problema:** Laravel procurava `whats_app_instances` mas tabela era `whatsapp_instances`  
**Solução:** Adicionar `protected $table = 'whatsapp_instances';` nos models

### 2. LeadObserver Null Reference
**Problema:** Tentava acessar `$lead->assignedUser->name` sem carregar relação  
**Solução:** Adicionar `$lead->load('assignedUser')` antes de acessar

---

## Testes Realizados

✅ Criar instância WhatsApp  
✅ Gerar QR Code (Mock)  
✅ Verificar status de conexão  
✅ Conectar WhatsApp (simulado)  
✅ Desconectar WhatsApp  
✅ Criar template com variáveis  
✅ Renderizar template com dados do Lead  
✅ Enviar mensagem via Job  
✅ Verificar retry logic (3 tentativas)  
✅ Opt-out checking antes de enviar  
✅ Activity logging de mensagens  
✅ Filtros de templates funcionando  
✅ Badges de status coloridos  
✅ QR Code display responsivo  

---

## Próximos Passos (FASE 7)

- [ ] Implementar Inbox com Laravel Reverb (WebSocket)
- [ ] Chat interface em tempo real
- [ ] Recebimento de mensagens via webhook
- [ ] Notificações de novas mensagens
- [ ] Assumir/transferir conversas
- [ ] Notas internas
- [ ] Status de leitura (✓ ✓✓)

---

## Dependências

- **Laravel 12** - Framework base
- **Filament V5** - Admin interface
- **Laravel Queue** - Background jobs
- **GD/Imagick** - QR code display (futuro)

---

## Comandos Úteis

```bash
# Processar envio de mensagens
php artisan queue:work --queue=default

# Verificar instâncias conectadas
php artisan tinker
>>> WhatsAppInstance::where('status', 'connected')->get()

# Testar envio de mensagem
>>> SendWhatsAppMessage::dispatch($lead, 'Test message', auth()->id())

# Listar templates ativos
>>> WhatsAppTemplate::where('active', true)->get()
```

---

## Conclusão

A FASE 6 implementa um sistema completo de WhatsApp usando Mock Service, pronto para desenvolvimento e testes. A transição para Z-API real é simples: basta implementar `ZApiRealService` e mudar o binding no ServiceProvider.

**Sistema pronto para:**
- ✅ Criar e gerenciar templates
- ✅ Enviar mensagens automáticas
- ✅ Conectar WhatsApp (simulado)
- ✅ Processar em background
- ✅ Retry automático
- ✅ Activity logging completo

**Próxima Fase:** FASE 7 - Inbox de WhatsApp com tempo real via Laravel Reverb\! 🚀
