# Twin23 OAuth 2.0 Client

This package makes it simple to integrate your application with [Twin23 OAuth 2.0](http://oauth.net/2/) service.

---

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights, please send a patch via pull request. If you're interesting in contributing to this library, please take a look at our [contributing guidelines](CONTRIBUTING.md).

## Requirements

The following versions of PHP are supported.

* PHP 5.5
* PHP 5.6
* PHP 7.0
* PHP 7.1
* HHVM

## Install

Via Composer

``` bash
$ composer require cloudadic/twin23-oauth2-php-sdk
```
### OR 
Add the following line to your composer.json
``` bash
"cloudadic/twin23-oauth2-php-sdk":"*"
```

### Authorization Code Grant

The authorization code grant type is the most common grant type used when authenticating users with a third-party service. This grant type utilizes a client (this library), a server (the service provider), and a resource owner (the user with credentials to a protected—or owned—resource) to request access to resources owned by the user. This is often referred to as _3-legged OAuth_, since there are three parties involved.

Now, for users who you don't have an account on Twin23, they'll be asked to put in their user info. Once fill out the form they are good to get logged-in.

#### Here's how you can configure your client.

```php
// In order to get your OAuth 2 credentials you need to register your app at 

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
```
#### Generate Authorization URL
```php
// Fetch the authorization URL. You can assign this to link on your web page.
$authorizationUrl = $client->getAuthorizationUrl();
```

#### Generate Access Token
```php
if (!empty($_GET['code'])) {
    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $client->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

        // Using the access token, we may look up details about the user

    } catch (\Twin23\Exception\ResponseException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}
```

### Refreshing a Token

Once your application is authorized, you can refresh an expired token using a refresh token rather than going through the entire process of obtaining a brand new token. To do so, simply reuse this refresh token from your data store to request a refresh.

```php
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
## Get Identity Info
```php
$identity = $client->getIdentity($accessToken->getToken());
```

## License

The MIT License (MIT). Please see [License File](https://github.com/cloudadic/twin23-oauth2-php-sdk/blob/master/LICENSE) for more information.


[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md
