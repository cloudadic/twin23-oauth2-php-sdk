# Twin23 OAuth 2.0 Client

This package makes it simple to integrate your application with [Twin OAuth 2.0](http://oauth.net/2/) service.

[![Gitter Chat](https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square)](https://gitter.im/thephpleague/oauth2-client)
[![Source Code](http://img.shields.io/badge/source-thephpleague/oauth2--client-blue.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client)
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-client.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/thephpleague/oauth2-client/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-client/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-client)
[![HHVM Status](https://img.shields.io/hhvm/league/oauth2-client.svg?style=flat-square)](http://hhvm.h4cc.de/package/league/oauth2-client)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-client/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-client/)
[![Coverage Status](https://img.shields.io/coveralls/thephpleague/oauth2-client/master.svg?style=flat-square)](https://coveralls.io/r/thephpleague/oauth2-client?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-client)

---
Having users sign up / sign in quickly is troublesome. Twin23 makes it super easy. You don't have to worry about passwords anymore.

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights, please send a patch via pull request. If you're interesting in contributing to this library, please take a look at our [contributing guidelines](CONTRIBUTING.md).

## Requirements

The following versions of PHP are supported.

* PHP 5.6
* PHP 7.0
* PHP 7.1
* HHVM

## Providers

A list of official PHP League providers, as well as third-party providers, may be found in the [providers list README](docs/providers/thirdparty.md).

To build your own provider, please refer to the [provider guide README](README.PROVIDER-GUIDE.md).

## Usage

If using Composer to require a specific provider client library, you **do not need to also require this library**. Composer will handle the dependencies for you.

### Authorization Code Grant

The following example uses the out-of-the-box `GenericProvider` provided by this library. If you're looking for a specific provider (i.e. Facebook, Google, GitHub, etc.), take a look at our [list of provider client libraries](docs/providers/thirdparty.md). **HINT: You're probably looking for a specific provider.**

The authorization code grant type is the most common grant type used when authenticating users with a third-party service. This grant type utilizes a client (this library), a server (the service provider), and a resource owner (the user with credentials to a protected—or owned—resource) to request access to resources owned by the user. This is often referred to as _3-legged OAuth_, since there are three parties involved.

The following example illustrates this using [Brent Shaffer's](https://github.com/bshaffer) demo OAuth 2.0 application named **Lock'd In**. When running this code, you will be redirected to Lock'd In, where you'll be prompted to authorize the client to make requests to a resource on your behalf.

Now, you don't really have an account on Lock'd In, but for the sake of this example, imagine that you are already logged in on Lock'd In when you are redirected there.

```php
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

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $client->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['state'] = $client->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (isset($_SESSION['state']) && $_GET['state'] !== $_SESSION['state'])) {

    if (isset($_SESSION['state'])) {
        unset($_SESSION['state']);
    }
    
    exit('Invalid state');

} else {

    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

        // Using the access token, we may look up details about the
        
        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        

    } catch (\Twin23\OAuth2\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}
```

### Refreshing a Token

Once your application is authorized, you can refresh an expired token using a refresh token rather than going through the entire process of obtaining a brand new token. To do so, simply reuse this refresh token from your data store to request a refresh.

```php

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

$existingAccessToken = $client->getAccessToken('authorization_code', [
    'code' => $code
]);

if ($existingAccessToken->hasExpired()) {
    $newAccessToken = $client->getAccessToken('refresh_token', [
        'refresh_token' => $existingAccessToken->getRefreshToken()
    ]);

    // Purge old access token and store new access token to your data store.
}
```

## Install

Via Composer

``` bash
$ composer require cloudadic/twin23-oauth2-php-sdk
```
### OR
``` bash
$ "cloudadic/twin23-oauth2-php-sdk":"*"
```

## License

The MIT License (MIT). Please see [License File](https://github.com/cloudadic/twin23-oauth2-php-sdk/blob/master/LICENSE) for more information.


[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md
