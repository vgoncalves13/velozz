# Stripe Integration Setup Guide

Laravel Cashier está configurado! Siga estes passos para ativar os pagamentos.

---

## 1. Criar Conta no Stripe (Test Mode)

1. Acesse: https://dashboard.stripe.com/register
2. Crie sua conta (gratuito)
3. **IMPORTANTE:** Certifique-se de estar em **Test Mode** (toggle no canto superior direito)

---

## 2. Obter Chaves da API

No Stripe Dashboard (Test Mode):

1. Vá em **Developers** > **API keys**
2. Copie suas chaves de teste:
   - **Publishable key** (começa com `pk_test_...`)
   - **Secret key** (começa com `sk_test_...`)

3. Adicione no `.env`:
```bash
STRIPE_KEY=pk_test_SUA_CHAVE_AQUI
STRIPE_SECRET=sk_test_SUA_CHAVE_AQUI
```

---

## 3. Criar Produtos no Stripe

Para cada plano no banco de dados, crie um produto correspondente no Stripe:

### Passo a passo:

1. No Stripe Dashboard, vá em **Products** > **Add product**

2. **Para o Plano "Starter":**
   - Name: `Starter Plan`
   - Description: `500 leads, 200 messages/day, 2 operators`
   - Pricing: `Recurring` > `€29.00/month`
   - **IMPORTANTE:** Copie o **Price ID** (começa com `price_...`)
   - Advanced: adicione metadata `plan_id = 1` (ou o ID do plano no banco)

3. **Para o Plano "Professional":**
   - Name: `Professional Plan`
   - Description: `2000 leads, 1000 messages/day, 5 operators`
   - Pricing: `Recurring` > `€79.00/month`
   - Copie o **Price ID**
   - Metadata: `plan_id = 2`

4. **Para o Plano "Enterprise":**
   - Name: `Enterprise Plan`
   - Description: `10000 leads, 5000 messages/day, 20 operators`
   - Pricing: `Recurring` > `€199.00/month`
   - Copie o **Price ID**
   - Metadata: `plan_id = 3`

---

## 4. Atualizar Código com Price IDs

Edite o arquivo `app/Filament/Client/Pages/ChoosePlan.php`:

```php
public function subscribe(int $planId)
{
    $plan = Plan::findOrFail($planId);
    $tenant = Tenant::find(auth()->user()->tenant_id);

    // Mapear plan_id para Price ID do Stripe
    $stripePriceIds = [
        1 => 'price_XXXXXXXXXXXXXXXX', // Starter
        2 => 'price_YYYYYYYYYYYYYYYY', // Professional
        3 => 'price_ZZZZZZZZZZZZZZZZ', // Enterprise
    ];

    $checkout = $tenant
        ->newSubscription('default', $stripePriceIds[$planId])
        ->checkout([
            'success_url' => route('filament.client.pages.dashboard'),
            'cancel_url' => route('filament.client.pages.choose-plan'),
        ]);

    return redirect($checkout->url);
}
```

---

## 5. Configurar Webhook (Importante!)

O webhook é essencial para atualizar o status da assinatura automaticamente.

### Criar Webhook no Stripe:

1. No Stripe Dashboard, vá em **Developers** > **Webhooks** > **Add endpoint**

2. **Endpoint URL:**
```
https://seu-dominio.com/stripe/webhook
```

3. **Events to send:** Selecione estes eventos:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`

4. Copie o **Signing secret** (começa com `whsec_...`)

5. Adicione no `.env`:
```bash
STRIPE_WEBHOOK_SECRET=whsec_SUA_SECRET_AQUI
```

### Para desenvolvimento local (Stripe CLI):

```bash
# Instalar Stripe CLI
https://docs.stripe.com/stripe-cli

# Fazer login
stripe login

# Encaminhar webhooks para local
stripe listen --forward-to http://velozz.test/stripe/webhook
```

---

## 6. Cartões de Teste

Use estes cartões no checkout:

| Cartão | Resultado |
|--------|-----------|
| `4242 4242 4242 4242` | ✅ Sucesso |
| `4000 0000 0000 0002` | ❌ Declinado |
| `4000 0025 0000 3155` | 🔒 Requer 3D Secure |

- **CVV:** Qualquer 3 dígitos
- **Data:** Qualquer data futura
- **CEP:** Qualquer CEP

---

## 7. Testar o Fluxo Completo

1. Limpe o cache:
```bash
vendor/bin/sail artisan config:clear
```

2. Acesse: http://velozz.test/app/choose-plan

3. Clique em "Choose Professional"

4. Você será redirecionado para o Stripe Checkout (Test Mode)

5. Use o cartão de teste: `4242 4242 4242 4242`

6. Complete o pagamento

7. Você será redirecionado de volta para o dashboard

8. Verifique o banco de dados:
```sql
SELECT * FROM subscriptions WHERE stripe_status = 'active';
```

---

## 8. Verificar Status da Assinatura

O Cashier adiciona métodos úteis ao modelo Tenant:

```php
$tenant = Tenant::find(1);

// Verificar se tem assinatura ativa
$tenant->subscribed('default'); // true/false

// Obter a assinatura
$subscription = $tenant->subscription('default');

// Verificar status
$subscription->active(); // true/false
$subscription->onTrial(); // true/false
$subscription->cancelled(); // true/false

// Obter plano atual
$subscription->stripe_price; // price_XXXXXXX
```

---

## 9. Cancelar Assinatura

```php
// Cancelar no final do período
$tenant->subscription('default')->cancel();

// Cancelar imediatamente
$tenant->subscription('default')->cancelNow();
```

---

## 10. Webhook Logs

Para ver se os webhooks estão chegando:

```bash
vendor/bin/sail artisan pail
```

Filtre por: `cashier`

---

## Troubleshooting

### Erro: "No such price"
- Verifique se o Price ID no código corresponde ao criado no Stripe
- Certifique-se de estar em Test Mode

### Webhook não funciona
- Verifique se o `STRIPE_WEBHOOK_SECRET` está correto no `.env`
- Use Stripe CLI para testar localmente
- Verifique se a rota `/stripe/webhook` está acessível publicamente

### Checkout abre mas não redireciona
- Verifique as URLs de `success_url` e `cancel_url`
- Certifique-se de que as rotas existem

---

## Produção

Para ir para produção:

1. Toggle para **Live Mode** no Stripe Dashboard
2. Crie novos produtos com os mesmos planos
3. Atualize `.env` com chaves de produção (`pk_live_...` e `sk_live_...`)
4. Configure webhook de produção
5. Teste com cartão real (pequeno valor)

---

**Última atualização:** 2026-02-26
