# Integrasi WhatsApp

## Overview
Dokumentasi lengkap mengenai integrasi WhatsApp dalam aplikasi Simple Store, yaitu: setup, configuration, dan usage.

## WhatsApp Business API

### Provider
- **Service Provider**: [WhatsApp Business API Provider Name]
- **Account Status**: [Active/Trial]
- **Phone Number**: [WhatsApp Business Number]
- **Account ID**: [Account identifier]

### API Credentials
Lihat: `10_CREDENTIALS_ACCESS/` untuk credentials lengkap.

## Features Implemented

### 1. Notifikasi Order
- Order confirmation
- Order status updates
- Payment notifications
- Shipping updates

### 2. Customer Support
- Automated responses
- Chat routing
- Business hours handling

### 3. Marketing Messages (jika digunakan)
- Promotional messages
- Newsletters
- Product updates

## Configuration

### Environment Variables
```env
# WhatsApp API Configuration
WHATSAPP_API_URL=https://api.whatsapp.com/v1
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_account_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_verify_token
```

### Webhook Setup
**Webhook URL**: `https://your-domain.com/api/webhooks/whatsapp`

**Verification Token**: [Token yang digunakan untuk verify webhook]

**Subscribed Events**:
- messages
- message_status
- messaging_optins
- messaging_optouts

## Implementation

### Send Message Function
```php
// app/Services/WhatsAppService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp message ke customer
     * dengan template atau text message
     */
    public function sendMessage(string $to, string $message): bool
    {
        try {
            $response = Http::withToken(config('services.whatsapp.token'))
                ->post(config('services.whatsapp.api_url') . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', [
                    'to' => $to,
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('WhatsApp message failed', [
                'to' => $to,
                'response' => $response->json()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send template message untuk notifications
     * yang telah pre-approved oleh WhatsApp
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = []): bool
    {
        try {
            $response = Http::withToken(config('services.whatsapp.token'))
                ->post(config('services.whatsapp.api_url') . '/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => [
                            'code' => 'id'
                        ],
                        'components' => $parameters
                    ]
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('WhatsApp template send error: ' . $e->getMessage());
            return false;
        }
    }
}
```

### Usage Example
```php
// Dalam controller atau job
use App\Services\WhatsAppService;

$whatsapp = new WhatsAppService();

// Send simple text
$whatsapp->sendMessage('6285715838733', 'Pesanan Anda telah dikonfirmasi!');

// Send template message
$whatsapp->sendTemplate(
    '6285715838733',
    'order_confirmation',
    [
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => 'ORDER-123'],
                ['type' => 'text', 'text' => 'Rp 150.000']
            ]
        ]
    ]
);
```

## Message Templates

### Template 1: Order Confirmation
**Name**: `order_confirmation`
**Language**: Indonesian
**Category**: TRANSACTIONAL

**Template**:
```
Halo {{1}}!

Pesanan Anda telah dikonfirmasi.
Order ID: {{2}}
Total: {{3}}

Terima kasih telah berbelanja di Simple Store!
```

### Template 2: Shipping Update
**Name**: `shipping_update`
**Language**: Indonesian
**Category**: TRANSACTIONAL

**Template**:
```
Update Pengiriman

Order ID: {{1}}
Status: {{2}}
Estimasi Tiba: {{3}}

Lacak paket Anda: {{4}}
```

[Tambahkan template lainnya]

## Webhook Handling

### Webhook Controller
```php
// app/Http/Controllers/WebhookController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle webhook verification dari WhatsApp
     * untuk validate webhook URL
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events dari WhatsApp
     * untuk process message status dan incoming messages
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        
        Log::info('WhatsApp webhook received', ['data' => $data]);

        // Process webhook data
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        $this->handleMessageEvent($change['value']);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Process message events untuk handle
     * incoming messages dan status updates
     */
    protected function handleMessageEvent(array $value)
    {
        // Handle incoming messages
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                Log::info('Incoming WhatsApp message', ['message' => $message]);
                // Process incoming message
                // e.g., dispatch job, store in database, etc.
            }
        }

        // Handle message status updates
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                Log::info('Message status update', ['status' => $status]);
                // Update message delivery status
            }
        }
    }
}
```

### Routes
```php
// routes/api.php
Route::prefix('webhooks/whatsapp')->group(function () {
    Route::get('/', [WhatsAppWebhookController::class, 'verify']);
    Route::post('/', [WhatsAppWebhookController::class, 'handle']);
});
```

## Rate Limits
- **Messaging Rate**: [Limit per second/minute]
- **Template Messages**: [Limit]
- **Business-Initiated Messages**: [Window period]

## Testing

### Test Environment
- **Test Phone Number**: [Test number]
- **Test API URL**: [Sandbox URL jika ada]

### Manual Testing
```bash
# Send test message via curl
curl -X POST 'https://api.whatsapp.com/v1/messages' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "to": "6285715838733",
    "type": "text",
    "text": {
      "body": "Test message from Simple Store"
    }
  }'
```

## Monitoring

### Key Metrics
- Message delivery rate
- Message failure rate
- Response time
- Webhook processing time

### Logs Location
```bash
# Application logs
tail -f storage/logs/laravel.log | grep WhatsApp

# Check recent WhatsApp activities
```

## Troubleshooting

### Message Not Sent
**Possible Causes**:
1. Invalid phone number format
2. Expired access token
3. Rate limit exceeded
4. Template not approved
5. Network issues

**Solution**:
```bash
# Check logs
tail -100 storage/logs/laravel.log | grep WhatsApp

# Test API connectivity
curl -X GET 'https://api.whatsapp.com/v1/health' \
  -H 'Authorization: Bearer YOUR_ACCESS_TOKEN'

# Verify phone number format (must include country code)
# Format: 6285715838733 (no + or spaces)
```

### Webhook Not Receiving
**Checklist**:
- [ ] Webhook URL accessible dari internet
- [ ] SSL certificate valid
- [ ] Verify token correct
- [ ] Subscribed to correct events

## Compliance & Best Practices

### WhatsApp Business Policy
- Obtain user consent sebelum send messages
- Provide opt-out mechanism
- Don't spam users
- Follow 24-hour window untuk marketing messages
- Use approved templates only

### Message Quality
- Keep messages relevant
- Personalize when possible
- Include clear call-to-action
- Monitor user feedback/blocks

## Cost Management
- **Pricing**: [Per message cost]
- **Monthly Budget**: [Budget]
- **Cost Optimization**: Use templates, batch notifications

## Support & Resources
- **WhatsApp Business API Docs**: https://developers.facebook.com/docs/whatsapp
- **Provider Support**: [Provider contact]
- **Account Manager**: [Name & contact]

## Renewal & Maintenance
- **Access Token Expiry**: [Date/Never]
- **Account Renewal**: [Date]
- **Template Review**: [Schedule]

## Emergency Contact
Jika WhatsApp integration down:
- **Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)
- **Provider Support**: [Contact]



