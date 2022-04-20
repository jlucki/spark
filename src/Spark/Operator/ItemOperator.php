<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Operator;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use JLucki\ODM\Spark\Exception\ItemActionFailedException;
use JLucki\ODM\Spark\Interface\ItemInterface;
use JLucki\ODM\Spark\Trait\OperatorTrait;
use Symfony\Component\HttpFoundation\Response;

class ItemOperator
{

    use OperatorTrait;

    private Marshaler $marshaler;

    public function __construct(
        private DynamoDbClient $client,
    ) {
        $this->marshaler = new Marshaler();
    }

    /**
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws ItemActionFailedException
     */
    public function putItem(ItemInterface $item): ItemInterface
    {
        $params = [
            'TableName' => $item->getTableName(),
            'Item' => $this->marshaler->marshalItem($item->toArray()),
        ];

        try {
            $result = $this->client->putItem($params);
        } catch (DynamoDbException $e) {
            $message = "Unable to put item:\n";
            $message .= $e->getMessage() . "\n";
            throw new ItemActionFailedException($message, Response::HTTP_BAD_REQUEST);
        }

        return $item;
    }

    /**
     * @param ItemInterface $item
     * @return bool
     * @throws ItemActionFailedException
     */
    public function deleteItem(ItemInterface $item): bool
    {
        $params = [
            'TableName' => $item->getTableName(),
            'Key' => $item->getKey(),
        ];

        try {
            $result = $this->client->deleteItem($params);
        } catch (DynamoDbException $e) {
            $message = "Unable to delete item:\n";
            $message .= $e->getMessage() . "\n";
            throw new ItemActionFailedException($message, Response::HTTP_BAD_REQUEST);
        }

        return true;
    }

    /**
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws ItemActionFailedException
     */
    public function updateItem(ItemInterface $item): ItemInterface
    {
        $key = $item->getKey();

        $data = $item->toArray(false);

        $updateExpression = [];
        $expressionAttributeNames = [];
        $expressionAttributes = [];
        foreach ($data as $dataKey => $value) {
            $updateExpression[] = sprintf('#%s = :%s', $dataKey, $dataKey);
            $expressionAttributes[':' . $dataKey] = $value;
            $expressionAttributeNames['#' . $dataKey] = $dataKey;
        }
        $updateExpression = 'set ' . implode(', ', $updateExpression);

        $eav = $this->marshaler->marshalItem($expressionAttributes);

        $params = [
            'TableName' => $item->getTableName(),
            'Key' => $key,
            'UpdateExpression' => $updateExpression,
            'ExpressionAttributeValues'=> $eav,
            'ExpressionAttributeNames' => $expressionAttributeNames,
            'ReturnValues' => 'UPDATED_NEW'
        ];

        try {
            $result = $this->client->updateItem($params);
        } catch (DynamoDbException $e) {
            $message = "Unable to update item:\n";
            $message .= $e->getMessage() . "\n";
            throw new ItemActionFailedException($message, Response::HTTP_BAD_REQUEST);
        }

        return $item;
    }

    /**
     * @param string $itemClass
     * @param array<string, mixed> $key
     * @param bool $marshaled
     * @return ItemInterface|null
     */
    public function getItem(string $itemClass, array $key, bool $marshaled = false): ?ItemInterface
    {
        $itemObject = $this->getItemObject($itemClass);

        if ($marshaled === false) {
            $key = $this->makeKey($key);
        }

        $params = [
            'TableName' => $itemObject->getTableName(),
            'Key' => $key,
        ];

        $result = $this->client->getItem($params);

        if (isset($result['Item']) === false) {
            return null;
        }

        /** @var array<string, mixed> $itemData */
        $itemData = $this->marshaler->unmarshalItem($result['Item']);

        return $this->makeModel($itemClass, $itemData);
    }

}
