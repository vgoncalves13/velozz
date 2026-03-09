<?php

namespace App\Http\Controllers;

use App\Models\MetaAccount;
use App\Models\SocialMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstagramComplianceController extends Controller
{
    public function deauthorize(Request $request): Response
    {
        $payload = $this->parseAndVerifySignedRequest($request->input('signed_request', ''));

        if ($payload === null) {
            return response('Invalid signature', 400);
        }

        $instagramUserId = $payload['user_id'] ?? null;

        if ($instagramUserId) {
            MetaAccount::where('instagram_user_id', $instagramUserId)
                ->update(['status' => 'disconnected']);
        }

        return response('', 200);
    }

    public function deleteData(Request $request): JsonResponse
    {
        $payload = $this->parseAndVerifySignedRequest($request->input('signed_request', ''));

        if ($payload === null) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $instagramUserId = $payload['user_id'] ?? null;

        if ($instagramUserId) {
            $metaAccountIds = MetaAccount::where('instagram_user_id', $instagramUserId)
                ->pluck('id');

            SocialMessage::whereIn('meta_account_id', $metaAccountIds)->delete();

            MetaAccount::where('instagram_user_id', $instagramUserId)->delete();
        }

        $confirmationCode = (string) Str::uuid();
        $confirmUrl = route('instagram.deletion-confirm', ['code' => $confirmationCode]);

        return response()->json([
            'url' => $confirmUrl,
            'confirmation_code' => $confirmationCode,
        ]);
    }

    public function confirmDeletion(Request $request): View
    {
        return view('instagram.deletion-confirm', [
            'code' => $request->query('code'),
        ]);
    }

    /**
     * Parse and verify a Meta signed_request parameter.
     *
     * @return array<string, mixed>|null
     */
    private function parseAndVerifySignedRequest(string $signedRequest): ?array
    {
        if (! str_contains($signedRequest, '.')) {
            return null;
        }

        [$encodedSig, $payload] = explode('.', $signedRequest, 2);

        $sig = base64_decode(strtr($encodedSig, '-_', '+/'));
        $expectedSig = hash_hmac('sha256', $payload, config('services.meta.app_secret', ''), true);

        if (! hash_equals($expectedSig, $sig)) {
            return null;
        }

        $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        return is_array($data) ? $data : null;
    }
}
