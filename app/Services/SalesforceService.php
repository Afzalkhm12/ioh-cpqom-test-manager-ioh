<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SalesforceService
{
    protected string $url;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->url = env('SALESFORCE_URL', 'https://test.salesforce.com');
        $this->clientId = env('SALESFORCE_CLIENT_ID', '');
        $this->clientSecret = env('SALESFORCE_CLIENT_SECRET', '');
    }

    /**
     * Authenticate via Client Credentials Flow.
     * Caches the token to avoid repeated authentication calls.
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('salesforce_access_token', 7000, function () {
            $response = Http::asForm()->post("{$this->url}/services/oauth2/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            return null;
        });
    }

    /**
     * Authenticate via Persona Web Server Flow (Refresh Token)
     */
    public function getAccessTokenForUser(\App\Models\SalesforceUser $sfUser): string
    {
        // Try the existing access token first
        if ($sfUser->access_token) {
            return $sfUser->access_token;
        }

        return $this->refreshUserToken($sfUser);
    }

    public function refreshUserToken(\App\Models\SalesforceUser $sfUser): string
    {
        if (!$sfUser->refresh_token) {
            throw new \Exception('Persona is not authorized yet. Please link the Salesforce account first.');
        }

        $response = Http::asForm()->post("{$this->url}/services/oauth2/token", [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $sfUser->refresh_token,
        ]);

        if ($response->successful()) {
            $token = $response->json('access_token');
            $sfUser->update(['access_token' => $token]);
            return $token;
        }

        throw new \Exception('Failed to refresh Salesforce Persona token: ' . $response->body());
    }

    /**
     * Execute a Salesforce API Test.
     */
    public function executeApiTest(array $config, ?string $tokenOverride = null, ?\App\Models\SalesforceUser $sfUser = null)
    {
        $token = $tokenOverride ?? $this->getAccessToken();
        
        $method = $config['method'] ?? 'GET';
        $endpoint = $config['endpoint'] ?? '/services/data/v60.0/';
        $payload = $config['payload'] ?? [];

        $request = Http::withToken($token)->acceptJson();

        $response = $method === 'POST' ? 
            $request->post("{$this->url}{$endpoint}", $payload) : 
            $request->get("{$this->url}{$endpoint}");

        // If 401 Unauthorized and we possess an sfUser, refresh and retry once
        if ($response->status() === 401 && $sfUser) {
            $token = $this->refreshUserToken($sfUser);
            $request = Http::withToken($token)->acceptJson();
            $response = $method === 'POST' ? 
                $request->post("{$this->url}{$endpoint}", $payload) : 
                $request->get("{$this->url}{$endpoint}");
        }

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ];
    }

    public function describeObject(string $apiName, bool $isRetry = false)
    {
        $token = $this->getAccessToken(); 
        $endpoint = "/services/data/v60.0/sobjects/{$apiName}/describe";
        $response = Http::withToken($token)->acceptJson()->get("{$this->url}{$endpoint}");
        
        // Handle Stale System Cache Token
        if ((!$response->successful() && $response->status() === 401) || 
            (is_array($response->json()) && isset($response->json()[0]['errorCode']) && $response->json()[0]['errorCode'] === 'INVALID_SESSION_ID')) {
            if (!$isRetry) {
                Cache::forget('salesforce_access_token');
                return $this->describeObject($apiName, true);
            }
        }

        if ($response->status() === 404) {
             throw new \Exception("API name must correct.");
        }
        
        if (!$response->successful()) {
             throw new \Exception("Salesforce returned an error: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Trigger an Apex Test via Tooling API.
     */
    public function executeApexTest(string $className, ?string $tokenOverride = null)
    {
        $token = $tokenOverride ?? $this->getAccessToken();

        // Salesforce Tooling API to run tests synchronously or asynchronously
        $endpoint = "/services/data/v60.0/tooling/runTestsSynchronous";

        $response = Http::withToken($token)
            ->post("{$this->url}{$endpoint}", [
                'classes' => $className
            ]);

        return [
            'success' => $response->successful() && collect($response->json('failures'))->isEmpty(),
            'status' => $response->status(),
            'response' => $response->json(),
        ];
    }
}
