# Stripe CLI - Guia de Uso

O Stripe CLI está instalado e pronto para testar webhooks localmente!

---

## 1. Fazer Login no Stripe CLI

Primeiro, você precisa autenticar o CLI com sua conta Stripe:

```bash
vendor/bin/sail root-shell -c "stripe login"
```

Isso vai:
1. Abrir seu navegador
2. Pedir para você confirmar a conexão
3. Criar uma chave de API para o CLI

---

## 2. Testar Webhooks Localmente

### Método 1: Forward de Webhooks (Recomendado)

Encaminhe todos os webhooks do Stripe para sua aplicação local:

```bash
vendor/bin/sail root-shell -c "stripe listen --forward-to http://localhost/stripe/webhook"
```

**O que isso faz:**
- Cria um webhook endpoint temporário no Stripe
- Captura todos os eventos de teste
- Encaminha para sua aplicação local
- Mostra logs em tempo real

**Output esperado:**
```
> Ready! Your webhook signing secret is whsec_xxxxx (^C to quit)
```

⚠️ **IMPORTANTE:** Copie o `whsec_xxxxx` e adicione no `.env`:
```bash
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
```

### Método 2: Trigger Manual de Eventos

Simule eventos específicos sem fazer o checkout real:

```bash
# Assinatura criada
vendor/bin/sail root-shell -c "stripe trigger customer.subscription.created"

# Pagamento bem-sucedido
vendor/bin/sail root-shell -c "stripe trigger invoice.payment_succeeded"

# Pagamento falhou
vendor/bin/sail root-shell -c "stripe trigger invoice.payment_failed"

# Assinatura cancelada
vendor/bin/sail root-shell -c "stripe trigger customer.subscription.deleted"
```

---

## 3. Ver Logs de Webhooks

Em outro terminal, rode:

```bash
vendor/bin/sail artisan pail
```

Filtre por `cashier` ou `webhook` para ver os logs.

---

## 4. Testar Fluxo Completo

### Passo a passo:

**Terminal 1 - Webhook Listener:**
```bash
vendor/bin/sail root-shell -c "stripe listen --forward-to http://localhost/stripe/webhook"
```

**Terminal 2 - Application Logs:**
```bash
vendor/bin/sail artisan pail
```

**Terminal 3 - Trigger Evento:**
```bash
vendor/bin/sail root-shell -c "stripe trigger customer.subscription.created"
```

**O que acontece:**
1. Stripe gera evento fake
2. CLI encaminha para `/stripe/webhook`
3. Laravel Cashier processa
4. Subscription é criada/atualizada no banco
5. Logs aparecem no `pail`

---

## 5. Comandos Úteis

### Listar eventos disponíveis:
```bash
vendor/bin/sail root-shell -c "stripe trigger --help"
```

### Ver últimos eventos:
```bash
vendor/bin/sail root-shell -c "stripe events list --limit 10"
```

### Ver detalhes de um evento:
```bash
vendor/bin/sail root-shell -c "stripe events retrieve evt_xxxxx"
```

### Ver subscriptions:
```bash
vendor/bin/sail root-shell -c "stripe subscriptions list"
```

### Ver customers:
```bash
vendor/bin/sail root-shell -c "stripe customers list"
```

---

## 6. Simular Pagamento Completo

### Criar subscription de teste:

```bash
# 1. Criar customer
vendor/bin/sail root-shell -c "stripe customers create --email='teste@example.com' --name='Cliente Teste'"

# Output: cus_xxxxx (copie o ID)

# 2. Criar subscription
vendor/bin/sail root-shell -c "stripe subscriptions create \
  --customer=cus_xxxxx \
  --items='[{\"price\":\"price_1T5DfG1cYiRGeJ1WX6QOK2QN\"}]'"

# Output: sub_xxxxx
```

---

## 7. Cancelar Subscription de Teste

```bash
vendor/bin/sail root-shell -c "stripe subscriptions cancel sub_xxxxx"
```

---

## 8. Workflow Recomendado

Para desenvolvimento diário:

1. **Abrir terminal com listener permanente:**
```bash
vendor/bin/sail root-shell -c "stripe listen --forward-to http://localhost/stripe/webhook"
```

2. **Deixar rodando em background** (opcional):
```bash
vendor/bin/sail root-shell -c "stripe listen --forward-to http://localhost/stripe/webhook > /dev/null 2>&1 &"
```

3. **Testar no navegador:**
   - Acesse: http://velozz.test/app/choose-plan
   - Escolha um plano
   - Complete checkout com `4242 4242 4242 4242`
   - Webhooks serão processados automaticamente

4. **Ver o resultado:**
```sql
-- Verificar subscription criada
SELECT * FROM subscriptions ORDER BY created_at DESC LIMIT 1;

-- Ver customer no Stripe
SELECT stripe_id FROM tenants WHERE id = 1;
```

---

## 9. Troubleshooting

### Erro: "stripe: command not found"
Você está fora do container. Use:
```bash
vendor/bin/sail root-shell -c "stripe --version"
```

### Erro: "No such webhook endpoint"
O webhook secret mudou. Copie o novo do `stripe listen` e atualize o `.env`.

### Erro: "401 Unauthorized"
Faça login novamente:
```bash
vendor/bin/sail root-shell -c "stripe login"
```

### Webhooks não chegam
1. Verifique se `stripe listen` está rodando
2. Verifique se o `STRIPE_WEBHOOK_SECRET` está correto no `.env`
3. Limpe o cache: `vendor/bin/sail artisan config:clear`

---

## 10. Produção

⚠️ **IMPORTANTE:** O `stripe listen` é apenas para desenvolvimento!

Em produção:
1. Configure webhook real no Stripe Dashboard
2. URL: `https://seu-dominio.com/stripe/webhook`
3. Use o webhook secret do Dashboard (não do CLI)

---

**Versão instalada:** 1.37.1

**Documentação oficial:** https://docs.stripe.com/stripe-cli
