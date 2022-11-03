# DoOnce

<p align="left">
<!--<a href="https://packagist.org/packages/laracraft-tech/do-once"><img src="https://img.shields.io/packagist/dt/laracraft-tech/do-once" alt="Total Downloads"></a>-->
<a href="https://packagist.org/packages/laracraft-tech/do-once"><img src="https://img.shields.io/packagist/v/laracraft-tech/do-once" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laracraft-tech/do-once"><img src="https://img.shields.io/packagist/l/laracraft-tech/do-once" alt="License"></a>
</p>

## Introduction

This package provides you with a simple PHP trait, which adds [memoization](https://en.wikipedia.org/wiki/Memoization) to your classes! It's inspired by spaties once package, but it is up to **50% faster**!

## Installation

``` bash
$ composer require laracraft-tech/do-once
```


## Usage

The `doOnce` function accepts a `callable`.

```php
use LaracraftTech\DoOnce\HasDoOnce;

$myClass = new class()
{    
    use HasDoOnce;

    public function getNumber(): int
    {
        return $this->doOnce(function () {
            return rand(1, 10000);
        });
    }
};
```

No matter how many times you run `$myClass->getNumber()` you'll always get the same number.

The `doOnce` function will only run once per combination of argument values the containing method receives.

```php
use LaracraftTech\DoOnce\HasDoOnce;

class MyClass
{
    use HasDoOnce;
    
    /**
     * It also works in static context!
     */
    public static function getNumberForLetter($letter)
    {
        return self::doOnce(function () use ($letter) {
            return $letter . rand(1, 10000000);
        });
    }
}
```

So calling `MyClass::getNumberForLetter('A')` will always return the same result, but calling `MyClass::getNumberForLetter('B')` will return something else.

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
