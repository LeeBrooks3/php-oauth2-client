<?php

namespace LeeBrooks3\OAuth2\Http\Clients;

use LeeBrooks3\Http\Clients\Client as BaseClient;
use LeeBrooks3\Models\ModelInterface;
use LeeBrooks3\OAuth2\Models\AccessToken;
use LeeBrooks3\OAuth2\Models\User;

class Client extends BaseClient
{
    /**
     * The oauth2 client id.
     *
     * @var int
     */
    private $clientId;

    /**
     * The oauth2 client secret.
     *
     * @var string
     */
    private $clientSecret;

    /**
     * The oauth2 server url
     *
     * @var string
     */
    private $serverUrl;

    /**
     * The oauth2 user endpoint.
     *
     * @var string
     */
    private $userEndpoint;

    /**
     * The oauth2 token endpoint.
     *
     * @var string
     */
    private $tokenEndpoint;

    /**
     * The oauth2 authorize endpoint.
     *
     * @var string
     */
    private $authorizeEndpoint;

    /**
     * @param string|int $clientId
     * @param string $clientSecret
     * @param string $serverUrl
     * @param string $userEndpoint
     * @param string $tokenEndpoint
     * @param string $authorizeEndpoint
     */
    public function __construct(
        $clientId,
        string $clientSecret,
        string $serverUrl,
        string $userEndpoint,
        string $tokenEndpoint,
        string $authorizeEndpoint
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->serverUrl = $serverUrl;
        $this->userEndpoint = $userEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->authorizeEndpoint = $authorizeEndpoint;

        parent::__construct([
            'base_uri' => $this->serverUrl . '/',
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Returns a user via the given user token.
     *
     * @param AccessToken $token
     * @return ModelInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(AccessToken $token) : ModelInterface
    {
        $response = $this->get($this->userEndpoint, [
            'headers' => [
                'Authorization' => "{$token->token_type} {$token->access_token}",
            ],
        ]);

        $json = $response->getBody()->getContents();
        $data = \GuzzleHttp\json_decode($json, true);

        return $this->makeUser($data);
    }

    /**
     * Returns a user token via the given credentials.
     *
     * @param string $username
     * @param string $password
     * @return AccessToken
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserToken(string $username, string $password) : AccessToken
    {
        $response = $this->post($this->tokenEndpoint, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $username,
                'password' => $password,
            ],
        ]);

        $json = $response->getBody()->getContents();
        $data = \GuzzleHttp\json_decode($json, true);

        return $this->makeAccessToken($data);
    }

    /**
     * Returns an authentication token.
     *
     * @param string $redirectUri
     * @param string $code
     * @return AccessToken
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAuthToken(string $redirectUri, string $code) : AccessToken
    {
        $response = $this->post($this->tokenEndpoint, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ],
        ]);

        $json = $response->getBody()->getContents();
        $data = \GuzzleHttp\json_decode($json, true);

        return $this->makeAccessToken($data);
    }

    /**
     * Returns the url to redirect to authenticate.
     *
     * @param string $redirectUri
     * @return string
     */
    public function getAuthUrl(string $redirectUri) : string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => '',
        ]);

        return "{$this->serverUrl}/{$this->authorizeEndpoint}?{$query}";
    }

    /**
     * Makes a user instance.
     *
     * @param array $attributes
     * @return User
     */
    protected function makeUser(array $attributes) : ModelInterface
    {
        return new User($attributes);
    }

    /**
     * Makes an access token instance.
     *
     * @param array $attributes
     * @return AccessToken
     */
    protected function makeAccessToken(array $attributes) : ModelInterface
    {
        return new AccessToken($attributes);
    }
}
