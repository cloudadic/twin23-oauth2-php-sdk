<?php

namespace Twin23\OAuth2;

use Exception;
use Twin23\OAuth2\HttpClient;
use Twin23\OAuth2\HttpClientInterface;
use UnexpectedValueException;

/**
 * Description of Face23
 *
 * @author miradnan
 */
class Client {

    /**
     * 
     */
    //const OAUTH_ENDPOINT = 'http://localhost/oauth2/';
    const OAUTH_ENDPOINT = 'http://identity.face23.local/oauth2/';

    /**
     *
     * @var type array
     */
    protected $_config;

    /**
     *
     * @var type string
     */
    protected $state;

    /**
     *
     * @var type HttpClient
     */
    protected $_httpClient;

    /**
     * Constructs an OAuth 2.0 client.
     */
    public function __construct(array $config) {
        $this->_config = $config;

        $this->_httpClient = new HttpClient([
            // Base URI is used with relative requests
            'base_uri' => self::OAUTH_ENDPOINT,
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);
    }

    /**
     * 
     * @param array $options
     * @return type
     */
    public function getAuthorizationUrl(array $options = []) {
        return self::OAUTH_ENDPOINT . '?' . urldecode(http_build_query($this->getAuthorizationParameters($options)));
    }

    /**
     * Returns a new random string to use as the state parameter in an authorization url
     * 
     * @param  int $length Length of the random string to be generated.
     * @return string
     */
    protected function getRandomState($length = 32) {
        return \bin2hex(\openssl_random_pseudo_bytes($length / 2));
    }

    /**
     * 
     * @return type array
     */
    protected function getDefaultScopes() {
        return [
            'name',
            'email',
            'photo'
        ];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator() {
        return ',';
    }

    /**
     * Returns authorization parameters based on provided options.
     *
     * @param  array $options
     * @return array Authorization parameters
     */
    protected function getAuthorizationParameters(array $options) {
        if (empty($options['state'])) {
            $options['state'] = $this->getState();
        }
        if (empty($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }
        $options += [
            'response_type' => 'code',
        ];
        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }
        // Store the state as it may need to be accessed later on.
        $this->state = $options['state'];

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->_config['redirect_uri'];
        }
        $options['client_id'] = $this->_config['client_id'];

        unset($options['scope']);
        return $options;
    }

    /**
     * 
     */
    public function getState() {
        if (!$this->state) {
            $this->state = self::generateState();
        }
        return $this->state;
    }

    /**
     * 
     * @return type string
     */
    protected function generateState() {
        return sha1(uniqid());
    }

    /**
     * 
     * @param type $grant
     * @param array $options
     * @return string
     */
    public function getAccessToken($grant = 'authorization_code', array $options = []) {
        if (!isset($options['code'])) {
            throw new \Exception('Invalid code provided!');
        }

        $response = $this->_httpClient->request('POST', self::OAUTH_ENDPOINT . 'token', [
            'form_params' => [
                'grant_type' => $grant,
                'code' => $options['code'],
                'client_id' => $this->_config['client_id'],
                'client_secret' => $this->_config['client_secret'],
                'redirect_uri' => $this->_config['redirect_uri']
            ]
        ]);
        $response = json_decode($response->getBody()->__toString(), true);

        if (!empty($response['error'])) {
            // has errors
            throw new \Exception(__METHOD__ . ' ' . $response['error_description'], E_ERROR);
        }

        return new AccessToken($response);
    }

    /**
     * 
     * @param type $access_token
     * @return type json object
     */
    public function getUserProfileInfo($access_token) {
        $response = $this->_httpClient->request('GET', '/oauth2/profile?' . http_build_query([
                            'access_token' => $access_token
                ]))->getBody()->__toString();

        return json_decode($response);
    }

}
