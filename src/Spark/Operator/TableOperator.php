<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Operator;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use JLucki\ODM\Spark\Exception\TableAlreadyExistsException;
use JLucki\ODM\Spark\Exception\TableDoesNotExistException;
use JLucki\ODM\Spark\Model\Base\Table;
use JLucki\ODM\Spark\Trait\OperatorTrait;
use Symfony\Component\HttpFoundation\Response;

class TableOperator
{

    use OperatorTrait;

    public function __construct(
        private DynamoDbClient $client,
    ) {}

    /**
     * @param string $itemClass
     * @return Table
     * @throws TableAlreadyExistsException
     */
    public function createTable(string $itemClass): Table
    {
        $schema = $this->getSchema($itemClass);

        try {
            $result = $this->client->createTable($schema);
        } catch (DynamoDbException $e) {
            $message = "Unable to create table:\n";
            $message .= $e->getMessage() . "\n";
            throw new TableAlreadyExistsException($message, Response::HTTP_BAD_REQUEST);
        }

        return new Table($schema['TableName'], $result['TableDescription']);
    }

    /**
     * @param string $itemClass
     * @return Table
     * @throws TableDoesNotExistException
     */
    public function getTable(string $itemClass): Table
    {
        $schema = $this->getSchema($itemClass);

        try {
            $result = $this->client->describeTable($schema);
        } catch (DynamoDbException $e) {
            $message = "Unable to get table:\n";
            $message .= $e->getMessage() . "\n";
            throw new TableDoesNotExistException($message, Response::HTTP_BAD_REQUEST);
        }

        return new Table($schema['TableName'], $result['Table']);
    }

    /**
     * @param string $itemClass
     * @return bool
     * @throws TableDoesNotExistException
     */
    public function deleteTable(string $itemClass): bool
    {
        $itemObject = $this->getItemObject($itemClass);

        $params = [
            'TableName' => $itemObject->getTableName(),
        ];

        try {
            $result = $this->client->deleteTable($params);
        } catch (DynamoDbException $e) {
            $message = "Unable to delete table:\n";
            $message .= $e->getMessage() . "\n";
            throw new TableDoesNotExistException($message, Response::HTTP_BAD_REQUEST);
        }

        return true;
    }

}
