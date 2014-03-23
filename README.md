# WebScale #
WebScale is a cache abstraction library __under development__. It is based on proposed [PSR-6](https://github.com/Crell/fig-standards/blob/Cache/proposed/cache.md) interfaces. First stable release (1.0) is expected to be ready soon after PSR-6 is finalised.

WebScale has currently drivers for following data stores:
- Apc(u)
- XCache
- WinCache
- Redis
- Memcache(d)
- Couchbase
- File system
- PHP memory

## How can I contribute? ##
- Fork, hack, commit and push. Do __not__ put your name to source code files (@author tags).
- Share your (non-PSR-6) ideas on the issues tab.
- Go [here](https://groups.google.com/forum/?fromgroups#!forum/php-fig) and push PSR-6 forward.

## Usage ##

### Set up your pools ###
```php
$driver = new WebScale\Driver\Apc;

// items in different pools are separated from each other
$userpool = new WebScale\Pool($driver, 'users');
$blogpostpool = new WebScale\Pool($driver, 'blogposts');
```

### Get and set items ###
```php
$item = $pool->getItem('foo');
if ($item->isHit()) {
    /*
        Item::get() does not make a second call to your
        cache backend: value is already there.
    */
    $value = $item->get();
} else {
    $value = doSomeExpensiveStuff();
    $item->set($value, 3600);
}
// now do something with the value
```

### Delete items ###
```php
/*
    Don't worry: item's value isn't actually fetched
    unless you call Item::isHit or Item::get before
    deleting it.
 */
$pool->getItem('foo')->delete();

/*
    Invalidate all items from a pool.
 */
$pool->clear();
```

### Logging ###
You can use any PSR-3 compatible logger.
```php
$driver = WebScale\Driver\Factory::getRedisDriver(array(
    'host' => 'localhost',
    'port' => 6379
));

$logger = new Monolog\Logger('log', array(
    /* handlers */
));

$driver->setLogger($logger);
```

### Session handler ###
```php
$driver = WebScale\Driver\Factory::getMemcachedDriver(array(
    'host' => 'localhost',
    'port' => 11211
));

$handler = new WebScale\Session\Handler($driver);
$handler->register();

session_start();
```

Cache another Session handler. This allows you to store sessions in a long-term storage (like database) while still keeping currently active sessions in the cache.
```php
$driver = WebScale\Driver\Factory::getMemcachedDriver(array(
    'host' => 'localhost',
    'port' => 11211
));

$pdoHandler = new Acme\PdoSessionHandler($pdo);

$handler = new WebScale\Session\DecoratingHandler($driver, $pdoHandler);
$handler->register();

session_start();
```

### Nested pools ###
Pool can also have nested subpools. Clearing subpool does not affect it's parent or siblings. This functionality is not part of the current PSR-6 draft.
```php
$mainpool = new WebScale\Pool($driver, 'example.com');
$postpool = $mainpool->getSubPool('posts');
$userpool = $mainpool->getSubPool('users');

// Invalidate items from the userpool.
$userpool->clear();

// Invalidate all items from the main pool and it's subpools.
$mainpool->clear();
```

### Piping multiple operations at once ###
This functionality should be considered experimental. It is (obviously) faked with some drivers. Not part of the current PSR-6 draft.
```php
$collection = $pool->getItems(array('foo', 'bar', 'baz'));

$output = $collection->pipe(function ($collection) use ($db) {
    foreach ($collection as $key => $item) {
        if (!$item->isHit()) {
            $value = $db->xyz->findOne(array('key' => $key));
            $item->set($value);
        }
    }
});

print_r($output);
/*
    Array
    (
        [foo] => value
        [bar] => value
        [baz] => value
    )
*/
```
