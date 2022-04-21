<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Tests\Model;

use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\AttributeType;
use JLucki\ODM\Spark\Attribute\GlobalSecondaryIndex;
use JLucki\ODM\Spark\Attribute\KeyType;
use JLucki\ODM\Spark\Attribute\NonKeyAttributes;
use JLucki\ODM\Spark\Attribute\OnDemand;
use JLucki\ODM\Spark\Attribute\OpenAttribute;
use JLucki\ODM\Spark\Attribute\ProjectionType;
use JLucki\ODM\Spark\Attribute\TableName;
use JLucki\ODM\Spark\Model\Base\Item;
use DateTime;

#[
    TableName('TestItems'),
    OnDemand(true),
]
class TestItem extends Item
{

    #[
        KeyType('RANGE'),
        AttributeName('datetime'),
        AttributeType('N'),
    ]
    private DateTime $datetime;

    #[
        KeyType('HASH'),
        AttributeName('slug'),
        AttributeType('S'),
        GlobalSecondaryIndex,
        ProjectionType(ProjectionType::INCLUDE),
        NonKeyAttributes(['title', 'datetime']),
    ]
    private string $slug;

    #[
        OpenAttribute('title'),
    ]
    private ?string $title;

    #[
        OpenAttribute('section'),
    ]
    private ?string $section;

    #[
        OpenAttribute('content'),
    ]
    private ?string $content;

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(DateTime $datetime): self
    {
        $this->datetime = $datetime;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): self
    {
        $this->section = $section;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

}
