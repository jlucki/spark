<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark;

use JLucki\ODM\Spark\Exception\TableAlreadyExistsException;
use JLucki\ODM\Spark\Exception\TableDoesNotExistException;
use JLucki\ODM\Spark\Exception\ItemActionFailedException;
use JLucki\ODM\Spark\Exception\TableUpdateFailedException;
use JLucki\ODM\Spark\Interface\ItemInterface;
use JLucki\ODM\Spark\Model\Base\Table;
use JLucki\ODM\Spark\Operator\ItemOperator;
use JLucki\ODM\Spark\Operator\TableOperator;
use JLucki\ODM\Spark\Query\Query;
use Aws\DynamoDb\DynamoDbClient;
use JLucki\ODM\Spark\Trait\OperatorTrait;

class Spark extends ClientConnection
{

    use OperatorTrait;

    /**
     * @param string $itemClass
     * @return Table
     * @throws TableAlreadyExistsException
     */
    public function createTable(string $itemClass): Table
    {
        return (new TableOperator($this->client))->createTable($itemClass);
    }

    /**
     * @param string $itemClass
     * @return Table
     * @throws TableUpdateFailedException
     */
    public function updateTable(string $itemClass): Table
    {
        return (new TableOperator($this->client))->updateTable($itemClass);
    }

    /**
     * @param string $itemClass
     * @return Table
     * @throws TableDoesNotExistException
     */
    public function getTable(string $itemClass): Table
    {
        return (new TableOperator($this->client))->getTable($itemClass);
    }

    /**
     * @param string $itemClass
     * @return bool
     * @throws TableDoesNotExistException
     */
    public function deleteTable(string $itemClass): bool
    {
        return (new TableOperator($this->client))->deleteTable($itemClass);
    }

    /**
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws ItemActionFailedException
     */
    public function putItem(ItemInterface $item): ItemInterface
    {
        return (new ItemOperator($this->client))->putItem($item);
    }

    /**
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws ItemActionFailedException
     */
    public function updateItem(ItemInterface $item): ItemInterface
    {
        return (new ItemOperator($this->client))->updateItem($item);
    }

    /**
     * @param string $itemClass
     * @param array<string, mixed> $key
     * @param bool $marshaled
     * @return ItemInterface|null
     */
    public function getItem(string $itemClass, array $key, bool $marshaled = false): ?ItemInterface
    {
        return (new ItemOperator($this->client))->getItem($itemClass, $key, $marshaled);
    }

    /**
     * @param ItemInterface $item
     * @return bool
     * @throws ItemActionFailedException
     */
    public function deleteItem(ItemInterface $item): bool
    {
        return (new ItemOperator($this->client))->deleteItem($item);
    }

    /**
     * @param string $itemClass
     * @return Query
     */
    public function query(string $itemClass): Query
    {
        return new Query(
            $this->client,
            $this->marshaler,
            $itemClass
        );
    }

    /**
     * @param string $itemClass
     * @return array<ItemInterface>
     */
    public function scan(string $itemClass): array
    {
        $itemObject = $this->getItemObject($itemClass);
        $params = [
            'TableName' => $itemObject->getTableName(),
        ];

        $result = $this->client->scan($params);

        if (count($result['Items']) === 0) {
            return [];
        }

        $itemObjects = [];
        foreach ($result['Items'] as $item) {
            /** @var array<string, mixed> $itemData */
            $itemData = $this->marshaler->unmarshalItem($item);
            $itemObjects[] = $this->makeModel($itemClass, $itemData);
        }

        return $itemObjects;
    }

    /**
     * @return DynamoDbClient
     */
    public function client(): DynamoDbClient
    {
        return $this->client;
    }

}
