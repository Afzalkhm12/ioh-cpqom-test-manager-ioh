<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sf = new \App\Services\SalesforceService();
$url = env('SALESFORCE_URL');
$clientId = env('SALESFORCE_CLIENT_ID');
$clientSecret = env('SALESFORCE_CLIENT_SECRET');

echo "Requesting token from: {$url}/services/oauth2/token\n";
$response = \Illuminate\Support\Facades\Http::asForm()->post("{$url}/services/oauth2/token", [
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";

$body = $response->json();
if (isset($body['access_token'])) {
    $token = $body['access_token'];
    $instanceUrl = $body['instance_url'] ?? $url;
    
    echo "Using instanceUrl: {$instanceUrl}\n";
    echo "Calling describe API...\n";
    $describeRes = \Illuminate\Support\Facades\Http::withToken($token)->acceptJson()->get("{$instanceUrl}/services/data/v60.0/sobjects/Account/describe");
    echo "Describe Status: " . $describeRes->status() . "\n";
    echo "Describe Body (first 200 chars): " . substr($describeRes->body(), 0, 200) . "\n";
}
