<?php

namespace LeeBrooks3\OAuth2\Tests\Examples\Http\Clients;

use LeeBrooks3\OAuth2\Http\Clients\Client;
use LeeBrooks3\OAuth2\Tests\Examples\Models\ExampleUser;

class ExampleClient extends Client
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $user = ExampleUser::class;
}
