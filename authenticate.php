// authenticate.php
require __DIR__ . '/vendor/autoload.php';

use Google\Ads\GoogleAds\Lib\V14\GoogleAdsClientBuilder;
use Google\Auth\OAuth2;

$oAuth2Credential = (new OAuth2([
    'clientId' => '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',
    'clientSecret' => '862420647301-vku1g8mna97j6i3rhcm6gq80ia7qcaq4.apps.googleusercontent.com',
    'refreshToken' => '4/0AUJR-x7JZNOdYsTRudxyFTGS0CaOUjOXZGhSJUt6akHdHC-Uvns8AIumrSXBofP0YwhNIw&scope=https://www.googleapis.com/auth/adwords'
]));

$googleAdsClient = (new GoogleAdsClientBuilder())
    ->fromFile() // or from array config
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

echo "Client ready!";
