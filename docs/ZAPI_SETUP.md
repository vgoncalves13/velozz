# Z-API Integration Setup Guide

This guide will help you configure the real Z-API WhatsApp integration.

---

## Prerequisites

1. Z-API Account (https://z-api.io)
2. Active WhatsApp Instance
3. Instance credentials ready

---

## Required Credentials

You'll need three pieces of information from your Z-API dashboard:

### 1. Instance ID
- Location: Z-API Dashboard > Your Instance
- Format: Usually a UUID or alphanumeric string
- Example: `3C1234567890ABCDEF`

### 2. Token (Instance Token)
- Location: Z-API Dashboard > Your Instance > Settings
- Format: Alphanumeric string
- Example: `A1B2C3D4E5F6G7H8I9J0`

### 3. Client Token
- Location: Z-API Dashboard > Account Settings > Security
- Format: Alphanumeric string
- Example: `K1L2M3N4O5P6Q7R8S9T0`

---

## Configuration Steps

### Step 1: Add Credentials to .env

Open your `.env` file and add/update these lines:

```bash
# Z-API WhatsApp Integration
ZAPI_ENABLED=true
ZAPI_TOKEN=YOUR_INSTANCE_TOKEN_HERE
ZAPI_CLIENT_TOKEN=YOUR_CLIENT_TOKEN_HERE
```

**Important:**
- Set `ZAPI_ENABLED=true` to use real Z-API
- Set `ZAPI_ENABLED=false` to use Mock service (development)

### Step 2: Configure Instance in Database

The Instance ID is stored per tenant in the `whatsapp_instances` table:

```sql
-- Example: Update existing instance
UPDATE whatsapp_instances
SET instance_id = 'YOUR_INSTANCE_ID_HERE'
WHERE tenant_id = 1;
```

Or create via Filament Admin Panel:
1. Login to `/app`
2. Go to WhatsApp Configuration
3. Enter your Instance ID
4. Click "Connect"

### Step 3: Clear Configuration Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### Step 4: Test Connection

1. Access WhatsApp Configuration page (`/app/whatsapp/config`)
2. Click "Connect WhatsApp"
3. QR Code should appear (real, not mock)
4. Scan with WhatsApp mobile app
5. Status should change to "Connected"

---

## Webhook Configuration

To receive incoming messages, configure webhooks in Z-API dashboard:

### Webhook URL Format
```
https://your-domain.com/api/webhook/zapi/{tenant_slug}/{webhook_token}
```

### Events to Enable
1. **on-message-received** - For incoming messages
2. **on-whatsapp-message-status-changes** - For delivery/read receipts

### Steps
1. Go to Z-API Dashboard > Your Instance > Webhooks
2. Add webhook URL for each event
3. Enable the webhooks
4. Test by sending a message to the connected WhatsApp number

---

## API Endpoints Used

The integration uses these Z-API endpoints:

| Endpoint | Purpose | Method |
|----------|---------|--------|
| `/qr-code/image` | Get QR code as base64 image | GET |
| `/status` | Check connection status | GET |
| `/send-text` | Send text message | POST |
| `/send-image` | Send image with caption | POST |
| `/send-video` | Send video with caption | POST |
| `/send-audio` | Send audio message | POST |
| `/send-document` | Send document/file | POST |
| `/disconnect` | Disconnect instance | GET |

---

## Phone Number Format

The service automatically formats phone numbers to Z-API standard:

**Input formats supported:**
- `+351 912 345 678` → Formatted to `351912345678`
- `912345678` → Formatted to `55912345678` (assumes Brazil if no country code)
- `0912345678` → Formatted to `55912345678`

**Z-API expects:**
- Only digits (no spaces, dashes, or plus signs)
- Country code + area code + number
- Example: `5511999999999` (Brazil), `351912345678` (Portugal)

---

## Troubleshooting

### QR Code Not Appearing
- Check `ZAPI_ENABLED=true` in `.env`
- Verify `ZAPI_TOKEN` and `ZAPI_CLIENT_TOKEN` are correct
- Check Laravel logs: `./vendor/bin/sail artisan pail`
- Verify Instance ID in database matches Z-API dashboard

### Messages Not Sending
- Check connection status (should be "connected")
- Verify phone number format (digits only with country code)
- Check Laravel logs for API errors
- Verify Z-API account has credits/active plan

### Webhooks Not Working
- Verify webhook URL is publicly accessible (not localhost)
- Check webhook configuration in Z-API dashboard
- Ensure webhook token in URL matches tenant configuration
- Test webhook endpoint manually with curl/Postman

### "Already Connected" Error
- Instance is already connected to another WhatsApp number
- Disconnect first, then reconnect with correct number
- Or create new instance in Z-API dashboard

---

## Development vs Production

### Development (Mock Service)
```bash
ZAPI_ENABLED=false
```
- Uses `ZApiMockService`
- No real API calls
- Instant responses
- No Z-API credentials needed

### Production (Real Service)
```bash
ZAPI_ENABLED=true
ZAPI_TOKEN=your_token
ZAPI_CLIENT_TOKEN=your_client_token
```
- Uses `ZApiRealService`
- Real API calls to Z-API
- Actual WhatsApp messages
- Requires valid credentials

---

## Security Best Practices

1. **Never commit credentials to Git**
   - Keep `.env` in `.gitignore`
   - Use `.env.example` as template only

2. **Rotate tokens regularly**
   - Update Client Token periodically in Z-API dashboard
   - Update `.env` after rotation

3. **Secure webhook endpoints**
   - Validate webhook token on incoming requests
   - Use HTTPS in production
   - Log suspicious webhook attempts

4. **Monitor API usage**
   - Check Z-API dashboard for usage stats
   - Set up alerts for quota limits
   - Monitor failed API calls in logs

---

## API Rate Limits

Z-API has rate limits depending on your plan:
- Check your plan limits in Z-API dashboard
- The service includes retry logic with exponential backoff
- Failed messages are logged for review

---

## Support

- **Z-API Documentation:** https://developer.z-api.io
- **Z-API Support:** Check your dashboard for support options
- **VELOZZ Support:** Check application logs and webhook endpoints

---

**Last Updated:** 2026-02-26
