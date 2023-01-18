# Memoize

<p align="left">
<!--<a href="https://packagist.org/packages/laracraft-tech/memoize"><img src="https://img.shields.io/packagist/dt/laracraft-tech/memoize" alt="Total Downloads"></a>-->
<a href="https://packagist.org/packages/laracraft-tech/memoize"><img src="https://img.shields.io/packagist/v/laracraft-tech/memoize" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laracraft-tech/memoize"><img src="https://img.shields.io/packagist/l/laracraft-tech/memoize" alt="License"></a>
</p>

## Introduction

This package provides you with a simple PHP trait, which adds high-performance [memoization](https://en.wikipedia.org/wiki/Memoization) to your classes!

## Installation

``` bash
$ composer require laracraft-tech/memoize
```


## Usage

The `memoize` method accepts a `callable`.

```php
use LaracraftTech\Memoize\HasMemoization;

$myClass = new class()
{    
    use HasMemoization;

    public function getNumber(): int
    {
        return $this->memoize(function () {
            return rand(1, 10000);
        });
    }
};
```

No matter how many times you run `$myClass->getNumber()` you'll always get the same number.

The `memoize` method will only run **once per combination** of `use` variables the ***closure*** receives.

```php
$myClass = new class()
{
    use HasMemoization;
    
    public function getNumberForLetter($letter)
    {
        return $this->memoize(function () use ($letter) {
            return $letter . rand(1, 10000000);
        });
    }
}
```

So calling `$myClass->getNumberForLetter('A')` will always return the same result, but calling `$myClass->getNumberForLetter('B')` will return something else.

Some memoization packages uses the arguments of the ***containing method*** for the **once per combination** idea.
We think this feels a bit unintuitive and in certain circumstances will affect performance. So we use the `use` variables of the closure as the **once per combination** key. As a fallback, if you like/need to, we also let you fully self define your **once per combination** key in a second optional parameter of the closure.

```php
use LaracraftTech\Memoize\HasMemoization;

$myClass = new class()
{
    use HasMemoization;
    
    public function processSomethingHeavy1($someModel)
    {
        // ...
        
        $relation = $this->memoize(function () use ($someModel) {
            return Foo::find($someModel->foo_relation_id);
        }, $someModel->foo_relation_id); // <--- custom once per combination key
        
        // ...
    }
    
    // you could also do something like this (maybe more convinient)
    public function processSomethingHeavy2($someModel)
    {
        // ...
        
        $foo_relation_id = $someModel->foo_relation_id;
        $relation = $this->memoize(function () use ($foo_relation_id) {
            return Foo::find($foo_relation_id);
        });
        
        // ...
    }
    
    // this would work but will lose performance on each new $someModel even foo_relation_id would be the same
    public function processSomethingHeavy3($someModel)
    {
        // ...
        
        $relation = $this->memoize(function () use ($someModel) {
            return Foo::find($someModel->foo_relation_id);
        });
        
        // ...
    }
}
```

So when calling `$myClass->processSomethingHeavy1(SomeModel::find(1))` the variable `$relation` will always have the same value like in `$myClass->processSomethingHeavy1(SomeModel::find(2))` as long as they have the same `foo_relation_id`. In some other memoization packges you would lose performance here, cause the ***containing method*** parameter ($someModel) has changed... Note that `processSomethingHeavy3` would also lose performance, even though the `foo_relation_id` would be the same, cause here also the changed **$someModel** would be used as the combination key and that model would have at least a different id.

## Enable/Disable

You can globally enable or disable the memoize cache by setting `MEMOIZATION_GLOBALLY_DISABLED` to `true` in your `.env` file.

If you only want to disable to memoize cache for a specifiy class, do something like this:

```php
use LaracraftTech\Memoize\HasMemoization;

class MyClass
{
    use HasMemoization;
    
    public function __construct()
    {
        $this->disableMemoization();
    }
}
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email zacharias.creutznacher@gmail.com instead of using the issue tracker.

## Credits

- [Zacharias Creutznacher][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/laracraft-tech/laravel-dynamic-model.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/laracraft-tech/laravel-dynamic-model.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/laracraft-tech/laravel-dynamic-model/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/laracraft-tech/laravel-dynamic-model
[link-downloads]: https://packagist.org/packages/laracraft-tech/laravel-dynamic-model
[link-travis]: https://travis-ci.org/laracraft-tech/laravel-dynamic-model
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/laracraft-tech
[link-contributors]: ../../contributors
