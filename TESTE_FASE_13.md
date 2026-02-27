# 🧪 Guia de Testes - FASE 13
## Configurações Tenant e Webhooks

---

## 📋 Checklist de Testes

### 1️⃣ Página de Configurações

**Acesso:**
```
http://demo1.velozz.test/app/tenant-settings
```

**Verificações:**
- [ ] Página carrega sem erros
- [ ] Todas as 6 seções aparecem corretamente:
  - Company Information
  - Business Hours
  - Custom Fields
  - Outbound Webhooks
  - GDPR Compliance
  - API Access
- [ ] Seções são colapsáveis (clique para expandir/colapsar)
- [ ] Botão "Save Settings" está visível
- [ ] Botão "Regenerate API Key" está visível

---

### 2️⃣ Seção: Company Information

**Teste 1: Alterar Nome da Empresa**
1. Altere o nome da empresa para "VELOZZ Test"
2. Clique em "Save Settings"
3. ✅ Deve mostrar notificação "Settings Saved"
4. Recarregue a página
5. ✅ Nome deve estar salvo como "VELOZZ Test"

**Teste 2: Upload de Logo**
1. Clique em "Logo" e faça upload de uma imagem (PNG/JPG, max 2MB)
2. Clique em "Save Settings"
3. ✅ Logo deve ser salva
4. Verifique em `storage/app/public/tenant-logos/`

**Teste 3: Cores**
1. Selecione uma cor primária (#00FF00)
2. Selecione uma cor secundária (#FF0000)
3. Clique em "Save Settings"
4. ✅ Cores devem ser salvas

---

### 3️⃣ Seção: Business Hours

**Teste 1: Definir Horário**
1. Opening Time: 09:00
2. Closing Time: 18:00
3. After Hours Message: "Estamos fechados. Horário: 9h às 18h"
4. Clique em "Save Settings"
5. ✅ Horários devem ser salvos

---

### 4️⃣ Seção: Custom Fields

**Teste 1: Adicionar Campo Customizado**
1. Clique em "Add Custom Field"
2. Preencha:
   - Name: `cpf`
   - Type: Text
   - Label: `CPF do Cliente`
3. Clique em "Save Settings"
4. ✅ Campo deve ser salvo

**Teste 2: Verificar se Campo Aparece nos Leads**
1. Vá para Leads → Create
2. ✅ Deve aparecer um campo "CPF do Cliente" no formulário

**Teste 3: Adicionar Múltiplos Campos**
1. Adicione mais 2 campos:
   - `data_nascimento` (Date)
   - `tem_whatsapp` (Yes/No)
2. Clique em "Save Settings"
3. ✅ Todos os 3 campos devem estar salvos
4. ✅ Campos podem ser reordenados (drag-and-drop)

---

### 5️⃣ Seção: Outbound Webhooks

**Teste 1: Configurar Webhook**
1. Clique em "Add Webhook"
2. Preencha:
   - Webhook URL: `https://webhook.site/seu-uuid` (crie em https://webhook.site)
   - Events to Send: Selecione `lead_created` e `message_sent`
3. Clique em "Save Settings"
4. ✅ Webhook deve ser salvo

**Teste 2: Testar Webhook - Lead Created**
1. Vá para Leads → Create
2. Crie um novo lead com nome "Test Webhook Lead"
3. Salve o lead
4. Vá para https://webhook.site e verifique se recebeu o payload
5. ✅ Deve receber JSON com:
   ```json
   {
     "event": "lead_created",
     "data": {
       "lead_id": 123,
       "full_name": "Test Webhook Lead",
       ...
     },
     "timestamp": "2026-02-26T...",
     "tenant_id": 1
   }
   ```

**Teste 3: Testar Webhook - Message Sent**
1. Vá para Inbox
2. Envie uma mensagem para um lead
3. Verifique webhook.site
4. ✅ Deve receber payload com `event: "message_sent"`

**Teste 4: Múltiplos Webhooks**
1. Adicione um segundo webhook com URL diferente
2. Configure eventos diferentes (`lead_transferred`, `stage_changed`)
3. Clique em "Save Settings"
4. ✅ Ambos webhooks devem funcionar independentemente

**Teste 5: Testar Todos os Eventos**
- [ ] `lead_created` - Criar novo lead
- [ ] `lead_updated` - Editar lead existente
- [ ] `lead_transferred` - Mudar responsável do lead
- [ ] `message_sent` - Enviar mensagem no Inbox
- [ ] `message_received` - Simular recebimento (via comando ou webhook Z-API)
- [ ] `stage_changed` - Mover lead no Kanban
- [ ] `import_completed` - Importar planilha de leads

---

### 6️⃣ Seção: GDPR Compliance

**Teste 1: Configurar GDPR**
1. Anonymize Inactive Leads After: 12 meses
2. Delete Messages After: 24 meses
3. Consent Policy Text: "Ao usar nossos serviços, você concorda..."
4. Clique em "Save Settings"
5. ✅ Configurações devem ser salvas

**Teste 2: Testar Anonimização (Manual)**
```bash
vendor/bin/sail artisan gdpr:cleanup
```
1. Execute o comando
2. ✅ Deve mostrar quantos leads foram anonimizados
3. ✅ Leads inativos por 12+ meses devem ter dados anonimizados

---

### 7️⃣ Seção: API Access

**Teste 1: Visualizar API Key**
1. Expanda a seção "API Access"
2. ✅ Deve mostrar uma API key no formato `vz_xxxxxxxx...`
3. ✅ Campo deve estar desabilitado (não editável)

**Teste 2: Regenerar API Key**
1. Clique em "Regenerate API Key"
2. ✅ Deve abrir modal de confirmação
3. Confirme a ação
4. ✅ Deve mostrar notificação "API Key Regenerated"
5. ✅ Nova API key deve aparecer (diferente da anterior)
6. Recarregue a página
7. ✅ Nova API key deve persistir

**Teste 3: Gerar API Keys para Tenants Existentes**
```bash
vendor/bin/sail artisan tenants:generate-api-keys
```
1. Execute o comando
2. ✅ Deve mostrar quantos tenants receberam API keys
3. ✅ Tenants sem API key devem receber uma
4. ✅ Tenants com API key existente devem ser pulados

**Teste 4: Regenerar Todas as API Keys (Force)**
```bash
vendor/bin/sail artisan tenants:generate-api-keys --force
```
1. Execute com flag `--force`
2. ✅ Deve regenerar API keys de TODOS os tenants

---

## 🔍 Testes de Integração

### Teste 1: Webhooks + Audit Logs
1. Configure um webhook para `lead_created`
2. Crie um novo lead
3. Verifique:
   - ✅ Webhook foi disparado (webhook.site)
   - ✅ Audit log foi criado (`/app/audit-logs`)
   - ✅ Lead activity foi registrada

### Teste 2: Webhooks + Retry
1. Configure webhook com URL inválida: `https://invalid-url-12345.com/webhook`
2. Crie um lead
3. Verifique logs:
   ```bash
   vendor/bin/sail artisan pail
   ```
4. ✅ Deve mostrar tentativas de retry (1min, 5min, 15min)
5. ✅ Após 3 tentativas, deve falhar permanentemente

### Teste 3: Multiple Events → Multiple Webhooks
1. Configure 2 webhooks:
   - Webhook A: `lead_created`, `message_sent`
   - Webhook B: `lead_transferred`, `stage_changed`
2. Execute ações que disparam todos os 4 eventos
3. ✅ Webhook A deve receber apenas 2 eventos
4. ✅ Webhook B deve receber apenas 2 eventos

---

## 🐛 Testes de Erro

### Teste 1: Webhook URL Inválida
1. Tente adicionar webhook com URL: `not-a-url`
2. ✅ Deve mostrar erro de validação

### Teste 2: Eventos Vazios
1. Tente adicionar webhook sem selecionar eventos
2. ✅ Deve mostrar erro "Events to Send is required"

### Teste 3: Nome da Empresa Vazio
1. Limpe o campo "Company Name"
2. Clique em "Save Settings"
3. ✅ Deve mostrar erro de validação

---

## 📊 Verificação Final

### Banco de Dados
```sql
-- Verificar que settings foram salvos
SELECT id, name, settings FROM tenants WHERE id = 1;

-- Verificar audit logs
SELECT * FROM audit_logs WHERE entity = 'lead' ORDER BY created_at DESC LIMIT 10;

-- Verificar webhooks disparados
SELECT * FROM jobs WHERE queue = 'default' ORDER BY id DESC LIMIT 10;
```

### Arquivos Criados
```bash
# Helpers
ls -la app/Helpers/WebhookHelper.php

# Jobs
ls -la app/Jobs/DispatchWebhook.php

# Commands
ls -la app/Console/Commands/GenerateTenantApiKeys.php

# Page
ls -la app/Filament/Client/Pages/TenantSettings.php
ls -la resources/views/filament/client/pages/tenant-settings.blade.php
```

---

## ✅ Critérios de Sucesso

Para considerar a FASE 13 **100% completa**, todos os itens abaixo devem funcionar:

- [x] Página de configurações carrega sem erros
- [x] Todas as 6 seções são funcionais
- [x] Dados são salvos corretamente no banco
- [x] Webhooks são disparados para todos os 7 eventos
- [x] Webhooks fazem retry em caso de falha
- [x] API Keys podem ser geradas e regeneradas
- [x] Comando `tenants:generate-api-keys` funciona
- [x] Custom fields aparecem nos formulários de Lead
- [x] Audit logs são criados para todas as ações
- [x] Configurações GDPR são salvas
- [x] Múltiplos webhooks podem coexistir

---

## 🎯 Próximos Passos

Após completar todos os testes da FASE 13:
1. Commit as mudanças
2. Passar para **FASE 14 - Polimento e UX**

**Comando para commit:**
```bash
git add .
git commit -m "feat: implement FASE 13 - Tenant Settings and Webhooks

- TenantSettings page with 6 sections
- Webhook integration for 7 critical events
- DispatchWebhook job with retry logic
- WebhookHelper for easy webhook dispatching
- GenerateTenantApiKeys command
- API key regeneration functionality

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```
