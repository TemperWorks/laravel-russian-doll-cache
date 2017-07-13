# Laravel Russian Doll Cache Blade Directive

## Intro
This package provides a Blade directive to cache rendered partials in Laravel, based on the LRU auto evict poicy of memcached (by default) or redis [(optional)](https://redis.io/topics/lru-cache)

In this implementation you don't need to explicitly set cache keys. The cache key will be automatically generated based on the data you pass to the partial. 

If the data implements ```getCacheKey``` (by including the Cacheable trait) the key is based on the class, id and updated_at timestamp of the object. When the object changes, the key updates and the view will be regenerated on the next visit. 

If the data does not implement ```getCacheKey```, an md5 hash is used as cache key.

The cache store will be responsible for auto evicting the less recently used keys when the store is full.

## Install

**Make sure your cache store has ```max_memory``` configured and a proper eviction policy is set.**

You can install the package via Composer:

```bash
$ composer require temperworks/laravel-russian-doll-cache
```

Start by registering the package's service provider and facade:

```php
// config/app.php

'providers' => [
  ...
  TemperWorks\RussianDollCache\RussianDollCacheServiceProvider::class,
],

```

## Usage

The package registers a blade directive, `@cache`. The cache directive accepts the same arguments as `@include`, plus optional parameters for the amount of minutes a view should be cached for. If no minutes are provided, the view will be remembered until you manually remove it from the cache.

Only the data you pass explicitly to the partial will be available. Global variables will be ignored to make sure all the variables will be represented in the cache key.

```
{{-- Simple example --}}
@cache('footer.section.partial')

{{-- With extra view data --}}
@cache('products.card', ['product' => $category->products->first()])

{{-- For a certain time --}}
{{-- (cache will invalidate in 60 minutes in this example, set null to remember forever) --}}
@cache('homepage.news', null, 60)
```

### Clearing The PartialCache

Since we rely on the cache store to automatically flush old data, it's not needed to manually remove keys. 

If you want to flush all entries, you'll need to either call `PartialCache::flush()` (note: this is only supported by drivers that support tags), or clear your entire cache.

### Configuration

Configuration isn't necessary, but there are two options specified in the config file:

- `russian-doll-cache.enabled`: Fully enable or disable the cache. Defaults to `true`.
- `russian-doll-cache.directive`: The name of the blade directive to register. Defaults to `cache`.
- `russian-doll-cache.key`: The base key that used for cache entries. Defaults to `partialcache`.

## Credits

This package is forked from on [spatie-partialcache](https://github.com/spatie/laravel-partialcache) by the awesome webdesign agency [Spatie](https://spatie.be/opensource)

- [Jeroen Jochems](http://temper.works)
- [Sebastian De Deyne](https://github.com/sebastiandedeyne)
- [All Contributors](../../contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
