<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Tests\Trait;

use Aws\DynamoDb\DynamoDbClient;
use Aws\MockHandler;
use Aws\Result;

trait DynamoDbTestClientTrait
{

    private function getTestClient(): DynamoDbClient
    {
        $mock = new MockHandler();

        $mock->append(new Result(['foo' => 'bar']));

        return new DynamoDbClient([
            'region'  => 'us-east-1',
            'version' => 'latest',
            'handler' => $mock,
            'credentials' => [
                'key'    => '',
                'secret' => '',
            ],
        ]);
    }

}
