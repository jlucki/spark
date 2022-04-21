<?php

require __DIR__ . '/../vendor/autoload.php';

use JLucki\ODM\Spark\Exception\ItemActionFailedException;
use JLucki\ODM\Spark\Exception\NothingToUpdateException;
use JLucki\ODM\Spark\Exception\TableDoesNotExistException;
use JLucki\ODM\Spark\Exception\TableUpdateFailedException;
use JLucki\ODM\Spark\Model\Article;
use JLucki\ODM\Spark\Query\Expression;
use JLucki\ODM\Spark\Spark;

$spark = new Spark(
    version: 'latest',
    region: 'us-east-1',
    endpoint: 'http://dynamodb:8000',
    key: '', // Not required for local dev
    secret: '', // Not required for local dev
);

//$spark->deleteTable(Article::class);

try {
    $table = $spark->getTable(Article::class);
} catch (TableDoesNotExistException) {
    $table = $spark->createTable(Article::class);
}

//try {
//    $table = $spark->updateTable(Article::class);
//} catch (TableUpdateFailedException|NothingToUpdateException) {
//
//}

function getItems(Spark $spark): array
{

    $from = (new DateTime())->setDate(2010, 1, 1)->getTimestamp();

    return $spark
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
        ->limit(10)
        ->getItems();

}

$items = getItems($spark);

if (count($items) < 4) {

    $date = new DateTime();

    $blog = (new Article())
        ->setType('blog')
        ->setDatetime($date)
        ->setSlug('my-blog-post-' . $date->format('y-m-d-H-i-s'))
        ->setTitle('My Blog Post ' . $date->format('Y-m-d H:i:s'))
        ->setContent('<p>Hello, this is the blog post content.</p>');

    try {
        $blog = $spark->putItem($blog);
    } catch (ItemActionFailedException $e) {
        // log the error
    }

    if ($blog instanceof Article) {
        // success
    }

}

/** @var Article[] $items */
$items = getItems($spark);

$reservedItem = $items[0];

$spark->updateItem($reservedItem);

// testing using reserved word 'section'

//$date = new DateTime();
//
//$blog = (new Article())
//    ->setType('blog')
//    ->setDatetime($date)
//    ->setSlug('my-blog-post-' . $date->format('y-m-d-H-i-s'))
//    ->setTitle('My Blog Post ' . $date->format('Y-m-d H:i:s'))
//    ->setSection('section value')
//    ->setContent('<p>Hello, this is the blog post content with a reserved keyword for an attribute.</p>');
//
//$blog = $spark->putItem($blog);

?>

<html lang="en">

    <head>
        <title>Spark ODM</title>
        <link rel="shortcut icon" href="favicon.ico">
    </head>

    <body>

        <?php
            foreach ($items as $item) {
                echo sprintf('<h3>%s</h3>', $item->getTitle());
                echo sprintf('<p>%s</p>', $item->getSlug());
                echo sprintf('<p>%s</p>', $item->getDatetime()->format('d-m-Y'));
                echo $item->getContent();
            }
        ?>

    </body>

</html>
