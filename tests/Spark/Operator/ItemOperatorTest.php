<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Tests\Operator;

use DateTime;
use JLucki\ODM\Spark\Exception\ItemActionFailedException;
use JLucki\ODM\Spark\Operator\ItemOperator;
use JLucki\ODM\Spark\Schema\Helper\ArrayHelper;
use JLucki\ODM\Spark\Tests\Model\TestItem;
use JLucki\ODM\Spark\Tests\Trait\DynamoDbTestClientTrait;
use PHPUnit\Framework\TestCase;
use function count;

class ItemOperatorTest extends TestCase
{

    use DynamoDbTestClientTrait;

    private ItemOperator $itemOperator;

    private TestItem $testItem;

    protected function setUp(): void
    {
        $this->testItem = $this->getTestItem();
        $this->itemOperator = new ItemOperator($this->getTestClient());
    }

    public function testUpdateItem(): void
    {
        $this->setUp();
        $this->expectNotToPerformAssertions();

        try {
            $itemResult = $this->itemOperator->updateItem($this->testItem);
        } catch (ItemActionFailedException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testGetUpdateParamsResultMatchesExpectedStructure(): void
    {
        $this->setUp();
        $params = $this->itemOperator->getUpdateParams($this->testItem);
        $diff = ArrayHelper::getArrayDiff($params, $this->getUpdateParamsExpectedStructure());
        $this->assertSame(count($diff), 0);
    }

    private function getTestItem(): TestItem
    {
        return (new TestItem())
            ->setTitle('Title')
            ->setDatetime(DateTime::createFromFormat('Y-m-d H:i:s', '2022-01-01 00:00:00'))
            ->setSection('Section')
            ->setSlug('slug')
            ->setContent('Lorem ipsum');
    }

    private function getUpdateParamsExpectedStructure(): array
    {
        return [
            'TableName' => 'TestItems',
            'Key' => [
                'datetime' => [
                    'N' => '1640995200',
                ],
            ],
            'UpdateExpression' => 'set #title = :title, #section = :section, #content = :content',
            'ExpressionAttributeValues' => [
                ':title' => [
                    'S' => 'Title',
                ],
                ':section' => [
                    'S' => 'Section',
                ],
                ':content' => [
                    'S' => 'Lorem ipsum',
                ],
            ],
            'ExpressionAttributeNames' => [
                '#title' => 'title',
                '#section' => 'section',
                '#content' => 'content',
            ],
            'ReturnValues' => 'UPDATED_NEW',
        ];
    }

}
