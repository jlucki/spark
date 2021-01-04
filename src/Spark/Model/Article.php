<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Model;

use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\AttributeType;
use JLucki\ODM\Spark\Attribute\GlobalSecondaryIndex;
use JLucki\ODM\Spark\Attribute\KeyType;
use JLucki\ODM\Spark\Attribute\OpenAttribute;
use JLucki\ODM\Spark\Attribute\ProjectionType;
use JLucki\ODM\Spark\Attribute\TableName;
use JLucki\ODM\Spark\Model\Base\Item;
use DateTime;

/**
 * Class Article
 * @package JLucki\ODM\Spark\Model
 *
 * This is an example DynamoDB Spark ODM item model
 */

#[
    TableName('Articles'),
]
class Article extends Item
{

    #[
        KeyType('HASH'),
        AttributeName('type'),
        AttributeType('S'),
    ]
    private string $type;

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
        ProjectionType(ProjectionType::ALL),
    ]
    private string $slug;

    #[
        OpenAttribute('title'),
    ]
    private ?string $title;

    #[
        OpenAttribute('content'),
    ]
    private ?string $content;

    #[
        OpenAttribute('published'),
    ]
    private bool $published = false;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    /**
     * @param DateTime $datetime
     * @return $this
     */
    public function setDatetime(DateTime $datetime): self
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     * @return $this
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param bool $published
     * @return $this
     */
    public function setPublished(bool $published): self
    {
        $this->published = $published;
        return $this;
    }

}
