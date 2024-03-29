# Spark Object Document Mapper

[![GitHub license](https://img.shields.io/badge/license-MIT-green)](https://choosealicense.com/licenses/mit/)
![Code coverage](https://img.shields.io/badge/coverage-11.70%25-red)

**Spark ODM is currently in its infancy and may not be suitable for all applications. Please consider your use case carefully before deploying it to any critical systems.**

### Test Drive DynamoDB

If you're looking for an easy, model driven way to get started with DynamoDB, look no further. This ODM comes equipped with [Docker](https://docs.docker.com/get-docker/) configuration files. Download the library and run `docker-compose up` to spin up a local development environment, including a local instance of DynamoDB. 

Once the containers are running, simply visit `localhost` in your browser. Refresh a couple of times to get additional example blog entries. Have a look at `public/index.php` to see what's happening under the hood.

You can also download the [NoSQL Workbench from AWS](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/workbench.settingup.html), to easily browse your DynamoDB data.  Once installed, open the workbench, click on `Operation builder` on the left hand side menu. Click on `+ Add connection`, switch to `DynamoDB local`, give the connection a name, leave the port on 8000, and click on `Connect`.

### Requirements

This library only works with PHP 8.0 and up.

## Documentation

Spark is a PHP ODM library for use with Amazon DynamoDB.

### How to install using Composer:

```
composer require jlucki/spark
```

### How to create a Spark connection:
If you're using Symfony, you can make use of Symfony's autowiring and add Spark to your service's constructor.
```php
public function __construct(
    private Spark $spark,
) {}
```

Otherwise, you can simply create a new Spark class, passing in the required credentials.
```php
$spark = new Spark(
    'latest', // version
    'us-east-1', // region
    'http://dynamodb:8000', // endpoint, use 'regional' for production
    'aws_access_key_id', // your aws access key
    'aws_access_key_secret', // your aws access key secret
);
```
*Tip: You can use an empty string for the id and secret for local development.*

### How to create a data model:
See [Article.php](https://github.com/jlucki/spark/blob/master/src/Spark/Model/Article.php) for an example table/item data model.

### How to create a global secondary index:
To create a GSI on your table add the `GlobalSecondaryIndex` attribute to the property. The GSI name will default to the value of `AttributeName`. Alternatively, you can assign a custom name by passing a string into the GSI attribute `GlobalSecondaryIndex('customName')`.

**Note:** You must provide a custom name for a GSI if the GSI has both `HASH` and `RANGE` keys, otherwise the two keys will not be recognised as a single GSI. See example below.

The default projection type is `KEYS_ONLY`. To change the projection type add the `ProjectionType` attribute to the property, and pass in one of the following values: `ProjectionType::KEYS_ONLY`, `ProjectionType::INCLUDE`, or `ProjectionType::ALL`.

When using `ProjectionType::INCLUDE`, you will also have to add the `NonKeyAttributes` attribute to the property, and pass in an array that contains the names of any attributes you want to project into the index. For example: `NonKeyAttributes(['title', 'content'])`  
```php
#[
    KeyType('HASH'),
    AttributeName('slug'),
    AttributeType('S'),
    GlobalSecondaryIndex('slugIndex'),
    ProjectionType(ProjectionType::ALL),
]
private string $slug;

#[
    KeyType('RANGE'),
    AttributeName('slugDatetime'),
    AttributeType('N'),
    GlobalSecondaryIndex('slugIndex'),
    ProjectionType(ProjectionType::ALL),
]
private DateTime $newSlugDatetime;
```

### How to create a table:
```php
$table = $spark->createTable(Article::class);
```

### How to get or check if a table exists:
```php
$table = $spark->getTable(Article::class);
```

### How to update a table:
```php
$result = $spark->updateTable(Article::class);
```
The `updateTable()` method will compare the described table schema as provided by DynamoDB to your local file schema, and compute any required changes. The `updateTable()` method supports:
- Creating a new GSI
- Removing an existing GSI
- Uptading a GSI, but only if you're also updating the ProvisionedThroughput. This is an apparent limitation of the AWS PHP SDK.

_Tip: You can add open attributes to your model without running the `updateTable()` method. This data will be automatically added to your table next time you put or update the item._ 


### How to delete a table:
```php
$result = $spark->deleteTable(Article::class);
```
*Warning: This will also delete all items in the table. This cannot be undone.*

### How to put a new item into the database:
```php
$date = new DateTime();

$blog = (new Article())
    ->setType('blog')
    ->setDatetime($date)
    ->setSlug('my-blog-post-' . $date->format('y-m-d-H-i-s'))
    ->setTitle('My Blog Post ' . $date->format('Y-m-d H:i:s'))
    ->setContent('<p>Hello, this is the blog post content.</p>');

// putItem() will return the same object that it persisted to DynamoDB
$blog = $spark->putItem($blog);

```
### How to get an item from the database:
```php
$itemByKey = $spark->getItem(Article::class, [
    'datetime' => $timestamp,
    'type' => 'blog',
]);
```

### How to get an item from the database using a secondary index:

You cannot use `getItem()` to retrieve an item from DynamoDB using a secondary index. This is because a secondary index's hash and range keys are not unique. You can however, query the table using the secondary index, and get the first result. See [here](#how-to-query-specific-items-using-a-secondary-index) for more details.

### How to update an item in the database:
```php
$blog->setContent('<p>Updated content!</p>');

try {
    $blog = $spark->updateItem($blog);
} catch (ItemActionFailedException $e) {
    // handle the error
}

if ($blog instanceof Article) {
    // success
}
```

### How to delete an item from the database:
```php
try {
    $result = $spark->deleteItem($blog);
} catch (ItemActionFailedException $e) {
    // handle the error
}
```

### How to scan the whole table:
```php
$allItems = $spark->scan(Article::class);
```

### How to query specific items:
```php
$from = (new DateTime())->setDate(2010, 1, 1)->getTimestamp();

$blogs = $spark
    ->query(Article::class)
    ->findBy(
        (new Expression())
            ->attribute('datetime')
            ->comparison('>=')
            ->value($from)
    )
    ->findBy(
        (new Expression())
            ->attribute('type')
            ->value('blog')
    )
    ->consistentRead(false)
    ->sortOrder('desc')
    ->getItems();
```
*Tip: `findBy()` only works on table index attributes. You can use `filterBy()`, also passing in an `Expression` object, to additionally filter by other attributes.*

*Tip: You can also return the raw `AWS\Result` with `getRaw()` or return all details, including the LastEvaluatedKey in an easy to use object with `getHeap()`.*

### How to query specific items using a secondary index:
```php
$slug = 'my-blog-post-20-12-28-14-46-01';

$foundBySlug = $spark
    ->query(Article::class)
    ->indexName('slug')
    ->findBy(
        (new Expression())
            ->attribute('slug')
            ->value($slug)
    )
    ->getItems();
```
*Tip: If you're expecting a single item result with your query, you can use `getFirst()` instead of `getItems()`. This will return the first `ItemInterface` object from your results, or `null` if nothing is found.*

### How to get a count of your queried items:
```php
// getCount() will automatically loop through any additional results
// if the first query result contains a LastEvaluatedKey to get the full count
$totalBlogs = $spark
    ->query(Article::class)
    ->findBy(
        (new Expression())
            ->attribute('datetime')
            ->comparison('>=')
            ->value($from)
    )
    ->findBy(
        (new Expression())
            ->attribute('type')
            ->value('blog')
    )
    ->getCount();
```

### How to query the DynamoDbClient directly:
The scope of the ODM doesn't cover the entire AWS SDK for PHP for DynamoDB. You can access the DynamoDbClient and all its methods, and run your own custom queries, with the method below.
```php
$dynamoDbClient = $spark->client();
```
