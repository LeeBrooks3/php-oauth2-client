<?php

namespace LeeBrooks3\OAuth2\Tests\Unit\Http\Clients;

use GuzzleHttp\Psr7\Response;
use LeeBrooks3\OAuth2\Http\Clients\Client;
use LeeBrooks3\OAuth2\Models\AccessToken;
use LeeBrooks3\OAuth2\Models\User;
use LeeBrooks3\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ClientTest extends TestCase
{
    /**
     * The client instance.
     *
     * @var Client|MockObject
     */
    private $mockClient;

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
     * Creates a mock config repository instance and the partially mocked client instance.
     */
    public function setUp()
    {
        parent::setUp();

        $this->mockClient = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([
                $this->clientId = $this->faker->uuid,
                $this->clientSecret = $this->faker->uuid,
                $this->serverUrl = $this->faker->url,
                $this->userEndpoint = $this->faker->url,
                $this->tokenEndpoint = $this->faker->url,
                $this->authorizeEndpoint = $this->faker->url,
            ])
            ->setMethods(['get', 'post'])
            ->getMock();
    }

    /**
     * Tests that a get request is made to get a user.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetUser()
    {
        $accessToken = new AccessToken([
            'token_type' => 'Bearer',
            'access_token' => $this->faker->uuid,
        ]);
        $attributes = [];

        $this->mockClient->expects($this->once())
            ->method('get')
            ->with($this->userEndpoint, [
                'headers' => [
                    'Authorization' => "{$accessToken->token_type} {$accessToken->access_token}",
                ],
            ])
            ->willReturn(new Response(200, [], \GuzzleHttp\json_encode($attributes)));

        $result = $this->mockClient->getUser($accessToken);

        $this->assertInstanceOf(User::class, $result);
    }

    /**
     * Tests that a post request is made to get a user token.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetUserToken()
    {
        $username = $this->faker->email;
        $password = $this->faker->password;

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with($this->tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'username' => $username,
                    'password' => $password,
                ],
            ])
            ->willReturn(new Response(200, [], \GuzzleHttp\json_encode([])));

        $result = $this->mockClient->getUserToken($username, $password);

        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /**
     * Tests that a post request is made to get an auth token.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAuthToken()
    {
        $redirectUri = $this->faker->url;
        $code = $this->faker->uuid;

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with($this->tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ],
            ])
            ->willReturn(new Response(200, [], \GuzzleHttp\json_encode([])));

        $result = $this->mockClient->getAuthToken($redirectUri, $code);

        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /**
     * Tests that the url to redirect to authenticate is returned.
     */
    public function testGetAuthUrl()
    {
        $redirectUri = $this->faker->url;

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => '',
        ]);

        $result = $this->mockClient->getAuthUrl($redirectUri);

        $this->assertEquals("{$this->serverUrl}/{$this->authorizeEndpoint}?{$query}", $result);
    }
}
