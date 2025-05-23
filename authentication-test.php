<?php
// Debug: Show current directory
echo "Current directory: " . __DIR__ . "\n";

// Debug: Check if vendor/autoload.php exists
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "Autoload file exists at: " . $autoloadPath . "\n";
} else {
    echo "Autoload file not found at: " . $autoloadPath . "\n";
    exit(1);
}

require $autoloadPath;

use Google\Ads\GoogleAds\Lib\V18\GoogleAdsClientBuilder;
use Google\Auth\OAuth2;

try {
    $googleAdsClient = (new GoogleAdsClientBuilder())
        ->fromFile(__DIR__ . '/google_ads_php.ini') // Explicitly specify the path to config file
        ->withDeveloperToken('WCJEYf1fuxYDj0XlmDpxnA')  // Add developer token programmatically
        ->withOAuth2Credential((new OAuth2([
            'clientId' => 'abc123.apps.googleusercontent.com',
            'clientSecret' => 'mySuperSecret',
            'refreshToken' => '1//0gabcdefg'
        ])))
        ->withLoginCustomerId('2448540539')
        ->build();

    echo "Client ready! Authentication successful!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
