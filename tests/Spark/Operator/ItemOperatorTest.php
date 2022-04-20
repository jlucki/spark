<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Tests\Operator;

use DateTime;
use Faker\Factory;
use Faker\Generator;
use JLucki\ODM\Spark\Exception\ItemActionFailedException;
use JLucki\ODM\Spark\Operator\ItemOperator;
use JLucki\ODM\Spark\Tests\Model\DocumentWithReservedWordAttribute;
use JLucki\ODM\Spark\Tests\Trait\DynamoDbTestClientTrait;
use PHPUnit\Framework\TestCase;

class ItemOperatorTest extends TestCase
{

    use DynamoDbTestClientTrait;

    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testItemUpdateWorksWithReservedWordAttribute(): void
    {
        $this->setUp();

        $this->expectNotToPerformAssertions();

        $testClient = $this->getTestClient();

        $itemWithReservedWordAttribute = $this->getTestItemWithReservedWordAttribute();
        $itemOperator = new ItemOperator($testClient);

        try {
            $itemResult = $itemOperator->updateItem($itemWithReservedWordAttribute);
        } catch (ItemActionFailedException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * In this item, 'section' is the reserved word
     * See: https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/ReservedWords.html
     */
    private function getTestItemWithReservedWordAttribute(): DocumentWithReservedWordAttribute
    {
        return (new DocumentWithReservedWordAttribute())
            ->setTitle($this->faker->name())
            ->setDatetime(new DateTime())
            ->setSection($this->faker->word())
            ->setSlug($this->faker->slug())
            ->setContent($this->faker->text());
    }

}
