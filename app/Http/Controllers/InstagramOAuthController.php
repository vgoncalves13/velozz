<?php

namespace App\Http\Controllers;

use App\Enums\Channel;
use App\Filament\Client\Pages\MetaAccountSettings;
use App\Models\MetaAccount;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstagramOAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $state = Str::random(40);
        session(['instagram_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => config('services.instagram.client_id'),
            'redirect_uri' => url(config('services.instagram.redirect')),
            'scope' => 'instagram_business_basic,instagram_business_manage_messages',
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect('https://www.instagram.com/oauth/authorize?'.$query);
    }

    public function callback(Request $request, MetaGraphApiServiceInterface $metaApi): RedirectResponse
    {
        $tenant = auth()->user()?->tenant;
        $settingsUrl = MetaAccountSettings::getUrl(tenant: $tenant, panel: 'client');

        if ($request->has('error')) {
            return redirect($settingsUrl)
                ->with('meta_oauth_error', __('meta_settings.oauth.denied'));
        }

        $state = session()->pull('instagram_oauth_state');

        if (! $state || ! hash_equals($state, $request->state ?? '')) {
            return redirect($settingsUrl)
                ->with('meta_oauth_error', __('meta_settings.oauth.invalid_state'));
        }

        try {
            $tokenData = $metaApi->exchangeInstagramCode(
                $request->code,
                url(config('services.instagram.redirect'))
            );

            $shortLivedToken = $tokenData['access_token'];

            $longLivedData = $metaApi->getInstagramLongLivedToken($shortLivedToken);
            $longLivedToken = $longLivedData['access_token'];

            $userInfo = $metaApi->getInstagramUserInfo($longLivedToken);

            $metaApi->subscribeInstagramUser($userInfo['id'], $longLivedToken);

            MetaAccount::updateOrCreate(
                [
                    'tenant_id' => auth()->user()->tenant_id,
                    'instagram_user_id' => $userInfo['id'],
                    'type' => Channel::Instagram->value,
                ],
                [
                    'page_id' => $userInfo['id'],
                    'page_name' => $userInfo['username'] ?? $userInfo['name'],
                    'access_token' => $longLivedToken,
                    'source' => 'instagram_business_login',
                    'status' => 'connected',
                ]
            );
        } catch (\Exception $e) {
            return redirect($settingsUrl)
                ->with('meta_oauth_error', $e->getMessage());
        }

        return redirect($settingsUrl)
            ->with('meta_oauth_success', __('meta_settings.oauth.instagram_success'));
    }
}
