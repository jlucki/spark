<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Query;

use JLucki\ODM\Spark\Exception\QueryException;
use JLucki\ODM\Spark\Interface\ItemInterface;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Result as AwsResult;
use JLucki\ODM\Spark\Trait\OperatorTrait;
use function count;

class Query
{

    use OperatorTrait;

    private ItemInterface $itemObject;

    /** @var Expression[] */
    private array $findBy = [];

    /** @var Expression[] */
    private array $filterBy = [];

    private int $limit = 0;

    /** @var array<string, array>|null  */
    private ?array $lastEvaluatedKey = null;

    private ?string $indexName = null;

    private DynamoDbClient $client;

    private Marshaler $marshaler;

    private bool $consistentRead = false;

    private string $sortOrder = 'asc';

    public function __construct(DynamoDbClient $client, Marshaler $marshaler, string $itemClass) {
        $this->client = $client;
        $this->marshaler = $marshaler;
        $this->itemObject = $this->getItemObject($itemClass);
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return QueryResult
     * @throws QueryException
     */
    public function getHeap(): QueryResult
    {
        $result = $this->getResult();
        $itemObjects = $this->getItemObjects($result);
        return new QueryResult(
            $itemObjects,
            $result['Count'],
            $result['ScannedCount'],
            $result['LastEvaluatedKey'],
            $result['@metadata'],
        );
    }

    /**
     * @return AwsResult<array>
     * @throws QueryException
     */
    public function getRaw(): AwsResult
    {
        return $this->getResult();
    }

    /**
     * @return array<ItemInterface>
     * @throws QueryException
     */
    public function getItems(): array
    {
        $result = $this->getResult();
        return $this->getItemObjects($result);
    }

    /**
     * @return ItemInterface|null
     * @throws QueryException
     */
    public function getFirst(): ?ItemInterface
    {
        $result = $this->getResult();
        $itemObject = $this->getItemObjects($result, true);
        if (empty($itemObject) === true) {
            return null;
        }
        return reset($itemObject);
    }

    /**
     * @param AwsResult<array> $result
     * @param bool $firstOnly
     * @return array<ItemInterface>
     */
    private function getItemObjects(AwsResult $result, bool $firstOnly = false): array
    {
        $itemObjects = [];
        foreach ($result['Items'] as $item) {
            /** @var array<string, mixed> $itemData */
            $itemData = $this->marshaler->unmarshalItem($item);
            $itemObjects[] = $this->makeModel($this->itemObject::class, $itemData);
            if ($firstOnly === true) {
                break;
            }
        }
        return $itemObjects;
    }

    /**
     * @return AwsResult<array>
     * @throws QueryException
     */
    private function getResult(): AwsResult
    {
        $this->certify();

        $params = $this->buildQuery();

        try {
            return $this->client->query($params);
        } catch (DynamoDbException $e) {
            throw new QueryException($e->getMessage());
        }
    }

    /**
     * @param Expression $findBy
     * @return $this
     */
    public function findBy(Expression $findBy): self
    {
        $this->findBy[] = $findBy;
        return $this;
    }

    /**
     * @param Expression $filterBy
     * @return $this
     */
    public function filterBy(Expression $filterBy): self
    {
        $this->filterBy[] = $filterBy;
        return $this;
    }

    /**
     * @param string|null $indexName
     * @return $this
     */
    public function indexName(?string $indexName): self
    {
        $this->indexName = $indexName;
        return $this;
    }

    /**
     * @param bool $consistentRead
     * @return $this
     */
    public function consistentRead(bool $consistentRead): self
    {
        $this->consistentRead = $consistentRead;
        return $this;
    }

    /**
     * @param string $sortOrder
     * @return $this
     */
    public function sortOrder(string $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * @param array<string, array> $lastEvaluatedKey
     * @return $this
     */
    public function continueAfter(array $lastEvaluatedKey): self
    {
        $this->lastEvaluatedKey = $lastEvaluatedKey;
        return $this;
    }

    /**
     * @return int
     * @throws QueryException
     */
    public function getCount(): int
    {
        $this->certify();

        $count = 0;

        $params = $this->buildQuery();
        do {
            try {
                $result = $this->client->query($params);
                $count += $result['Count'];

                if (isset($result['LastEvaluatedKey'])) {
                    $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
                }
                else {
                    unset($params['ExclusiveStartKey']);
                }
            } catch (DynamoDbException $e) {
                $message = "Unable to query:\n";
                $message .= $e->getMessage() . "\n";
                throw new QueryException($message);
            }
        } while(isset($result['LastEvaluatedKey']));

        return $count;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuery(): array
    {
        $names = [];
        $values = [];
        $expression = [];
        $filterExpression = [];

        foreach ($this->findBy as $findBy) {
            $nameKey = '#n' . $findBy->getAttribute();
            $names[$nameKey] = $findBy->getAttribute();

            $valueKey = ':n' . $findBy->getAttribute();
            $values[$valueKey] = $findBy->getValue();

            $expression[] = sprintf('%s %s %s', $nameKey, $findBy->getComparison(), $valueKey);
        }

        $expression = implode(' and ', $expression);

        foreach ($this->filterBy as $filterBy) {
            $nameKey = '#n' . $filterBy->getAttribute();
            $names[$nameKey] = $filterBy->getAttribute();

            $valueKey = ':n' . $filterBy->getAttribute();
            $values[$valueKey] = $filterBy->getValue();

            $filterExpression[] = sprintf('%s %s %s', $nameKey, $filterBy->getComparison(), $valueKey);
        }

        $values = $this->marshaler->marshalItem($values);

        $params = [
            'TableName' => $this->itemObject->getTableName(),
        ];

        if ($this->indexName !== null) {
            $params['IndexName'] = $this->indexName;
        }

        $params = array_merge($params, [
            'KeyConditionExpression' => $expression,
            'ExpressionAttributeNames' => $names,
            'ExpressionAttributeValues' => $values,
            'ConsistentRead' => $this->consistentRead,
        ]);

        if (count($filterExpression) > 0) {
            $params['FilterExpression'] = implode(' and ', $filterExpression);
        }

        if ($this->lastEvaluatedKey !== null) {
            $params['ExclusiveStartKey'] = $this->lastEvaluatedKey;
        }

        if ($this->sortOrder === 'desc') {
            $params['ScanIndexForward'] = false;
        }

        if ($this->limit > 0) {
            $params['Limit'] = $this->limit;
        }

        return $params;
    }

    /**
     * @throws QueryException
     */
    public function certify(): void
    {
        if ($this->limit < 0) {
            throw new QueryException('Limit must be a positive integer.');
        }
    }

}
