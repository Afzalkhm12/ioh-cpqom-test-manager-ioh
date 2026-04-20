<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesforceOAuthController extends Controller
{
    public function redirect($sfUserId)
    {
        $clientId = env('SALESFORCE_CLIENT_ID');
        $callbackUrl = str_replace('127.0.0.1', 'localhost', route('salesforce.callback'));
        $redirectUri = urlencode($callbackUrl);
        $loginUrl = env('SALESFORCE_URL', 'https://test.salesforce.com');
        
        // Generate PKCE code verifier and challenge
        $codeVerifier = \Illuminate\Support\Str::random(128);
        session(['pkce_verifier_' . $sfUserId => $codeVerifier]);
        $codeChallenge = strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');
        
        $url = "{$loginUrl}/services/oauth2/authorize?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}&state={$sfUserId}&code_challenge={$codeChallenge}&code_challenge_method=S256";
        return redirect()->away($url);
    }

    public function callback(\Illuminate\Http\Request $request)
    {
        $code = $request->query('code');
        $sfUserId = $request->query('state');

        if (!$code) {
            return redirect()->route('sf-users.index')->withErrors(['oauth' => 'Authorization failed. No code returned.']);
        }

        $sfUser = \App\Models\SalesforceUser::findOrFail($sfUserId);
        $callbackUrl = str_replace('127.0.0.1', 'localhost', route('salesforce.callback'));
        $codeVerifier = session('pkce_verifier_' . $sfUserId);

        $response = \Illuminate\Support\Facades\Http::asForm()->post(env('SALESFORCE_URL', 'https://test.salesforce.com') . '/services/oauth2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('SALESFORCE_CLIENT_ID'),
            'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
            'redirect_uri' => $callbackUrl,
            'code' => $code,
            'code_verifier' => $codeVerifier,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $sfUser->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $sfUser->refresh_token,
            ]);
            // Clear the session verifier
            session()->forget('pkce_verifier_' . $sfUserId);
            return redirect()->route('sf-users.index')->with('success', 'Salesforce Persona authorized successfully!');
        }

        return redirect()->route('sf-users.index')->withErrors(['oauth' => 'Failed to obtain tokens from Salesforce: ' . $response->body()]);
    }
}
