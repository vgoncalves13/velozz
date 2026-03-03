<?php

namespace App\Http\Controllers;

use App\Enums\Channel;
use App\Filament\Client\Pages\MetaAccountSettings;
use App\Models\MetaAccount;
use App\Services\Meta\MetaGraphApiServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class MetaOAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('facebook')
            ->scopes([
                'pages_show_list',
                'pages_messaging',
                'instagram_basic',
                'instagram_manage_messages',
                'pages_read_engagement',
            ])
            ->redirect();
    }

    public function callback(Request $request, MetaGraphApiServiceInterface $metaApi): RedirectResponse
    {
        $settingsUrl = MetaAccountSettings::getUrl(panel: 'client');

        if ($request->has('error')) {
            return redirect($settingsUrl)
                ->with('meta_oauth_error', __('meta_settings.oauth.denied'));
        }

        $socialiteUser = Socialite::driver('facebook')->user();
        $longLivedData = $metaApi->extendToken($socialiteUser->token);
        $longLivedToken = $longLivedData['access_token'];

        $pagesResponse = $metaApi->getPages($longLivedToken);
        $pages = $pagesResponse['data'] ?? [];

        $tenantId = auth()->user()->tenant_id;
        $connectedCount = 0;

        foreach ($pages as $page) {
            MetaAccount::updateOrCreate(
                ['tenant_id' => $tenantId, 'page_id' => $page['id'], 'type' => Channel::FacebookMessenger->value],
                [
                    'page_name' => $page['name'],
                    'access_token' => $page['access_token'],
                    'status' => 'connected',
                ]
            );

            $metaApi->subscribePage($page['id'], $page['access_token']);

            $connectedCount++;

            $instagramAccountId = $metaApi->getInstagramBusinessAccount($page['id'], $page['access_token']);

            if ($instagramAccountId !== null) {
                MetaAccount::updateOrCreate(
                    ['tenant_id' => $tenantId, 'page_id' => $page['id'], 'type' => Channel::Instagram->value],
                    [
                        'page_name' => $page['name'],
                        'instagram_user_id' => $instagramAccountId,
                        'access_token' => $page['access_token'],
                        'status' => 'connected',
                    ]
                );

                $connectedCount++;
            }
        }

        return redirect($settingsUrl)
            ->with('meta_oauth_success', __('meta_settings.oauth.success', ['count' => $connectedCount]));
    }
}
