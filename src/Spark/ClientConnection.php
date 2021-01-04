<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;

class ClientConnection
{
    protected DynamoDbClient $client;

    protected Marshaler $marshaler;

    public function __construct(
        private string $version,
        private string $region,
        private string $endpoint,
        private string $key,
        private string $secret,
    ) {
        $this->marshaler = new Marshaler();
        $this->setup();
    }

    private function setup(): void
    {
        $config = [
            'version'  => $this->version,
            'region'   => $this->region,
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ],
        ];
        if ($this->endpoint !== 'regional') {
            $config['endpoint'] = $this->endpoint;
        }
        $this->client = new DynamoDbClient($config);
    }
}
