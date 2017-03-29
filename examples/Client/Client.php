<?php
ini_set('display_errors',1);

require '../vendor/autoload.php';

$client = new Twin23\OAuth2\Client([
    // The client ID assigned to you by the provided
    'client_id' => 'YOUR_CLIENT_ID',
    // The client secret provided
    'client_secret' => 'YOUR_CLIENT_SECRET',
    // Redirect URL
    'redirect_uri' => 'http://my.website.com/redirect-page',
    // Permissions to the data that you would like to retrieve
    'scope' => ['name', 'email', 'photo', 'phone']
]);
