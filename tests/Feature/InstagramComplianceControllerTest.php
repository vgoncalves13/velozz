<?php

namespace Tests\Feature;

use App\Models\MetaAccount;
use App\Models\SocialMessage;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstagramComplianceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeSignedRequest(string $userId, string $appSecret = 'test_secret'): string
    {
        $payload = base64_encode(json_encode(['user_id' => $userId, 'algorithm' => 'HMAC-SHA256']));
        $payload = strtr($payload, '+/', '-_');

        $sig = hash_hmac('sha256', $payload, $appSecret, true);
        $encodedSig = strtr(base64_encode($sig), '+/', '-_');

        return $encodedSig.'.'.$payload;
    }

    public function test_deauthorize_disconnects_meta_account(): void
    {
        config(['services.meta.app_secret' => 'test_secret']);

        $tenant = Tenant::factory()->create();
        $account = MetaAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'instagram_user_id' => 'ig_user_456',
            'status' => 'connected',
        ]);

        $signedRequest = $this->makeSignedRequest('ig_user_456');

        $response = $this->post('/api/instagram/deauthorize', [
            'signed_request' => $signedRequest,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('meta_accounts', [
            'id' => $account->id,
            'status' => 'disconnected',
        ]);
    }

    public function test_deauthorize_rejects_invalid_signature(): void
    {
        config(['services.meta.app_secret' => 'test_secret']);

        $tenant = Tenant::factory()->create();
        MetaAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'instagram_user_id' => 'ig_user_456',
            'status' => 'connected',
        ]);

        $signedRequest = $this->makeSignedRequest('ig_user_456', 'wrong_secret');

        $response = $this->post('/api/instagram/deauthorize', [
            'signed_request' => $signedRequest,
        ]);

        $response->assertStatus(400);

        $this->assertDatabaseHas('meta_accounts', [
            'instagram_user_id' => 'ig_user_456',
            'status' => 'connected',
        ]);
    }

    public function test_delete_data_removes_accounts_and_messages(): void
    {
        config(['services.meta.app_secret' => 'test_secret']);

        $tenant = Tenant::factory()->create();
        $account = MetaAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'instagram_user_id' => 'ig_user_789',
            'status' => 'connected',
        ]);

        SocialMessage::factory()->create([
            'tenant_id' => $tenant->id,
            'meta_account_id' => $account->id,
        ]);

        $signedRequest = $this->makeSignedRequest('ig_user_789');

        $response = $this->post('/api/instagram/delete-data', [
            'signed_request' => $signedRequest,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['url', 'confirmation_code']);

        $this->assertDatabaseMissing('meta_accounts', ['instagram_user_id' => 'ig_user_789']);
        $this->assertDatabaseMissing('social_messages', ['meta_account_id' => $account->id]);
    }

    public function test_delete_data_rejects_invalid_signature(): void
    {
        config(['services.meta.app_secret' => 'test_secret']);

        $tenant = Tenant::factory()->create();
        MetaAccount::factory()->create([
            'tenant_id' => $tenant->id,
            'instagram_user_id' => 'ig_user_789',
            'status' => 'connected',
        ]);

        $signedRequest = $this->makeSignedRequest('ig_user_789', 'wrong_secret');

        $response = $this->post('/api/instagram/delete-data', [
            'signed_request' => $signedRequest,
        ]);

        $response->assertStatus(400);

        $this->assertDatabaseHas('meta_accounts', ['instagram_user_id' => 'ig_user_789']);
    }

    public function test_deletion_confirm_page_returns_200(): void
    {
        $response = $this->get('/data-deletion/confirm?code=test-uuid-123');

        $response->assertStatus(200);
        $response->assertSee('test-uuid-123');
    }
}
