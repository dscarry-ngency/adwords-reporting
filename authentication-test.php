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
    $oAuth2Credential = (new OAuth2([
        'clientId' => 'abc123.apps.googleusercontent.com',
        'clientSecret' => 'mySuperSecret',
        'refreshToken' => '1//0gabcdefg'
    ]));

    $googleAdsClient = (new GoogleAdsClientBuilder())
        ->withDeveloperToken('WCJEYf1fuxYDj0XlmDpxnA')
        ->withLoginCustomerId(2448540539)
        ->withOAuth2Credential($oAuth2Credential)
        ->build();

    echo "Client ready! Authentication successful!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
