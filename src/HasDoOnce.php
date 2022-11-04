<?php

namespace LaracraftTech\DoOnce;

use Dotenv\Dotenv;
use http\Exception\InvalidArgumentException;

trait HasDoOnce
{
    /**
     * specifies if doOnce should be applied
     *
     * @var bool
     */
    protected static $doOnceEnabled = true;

    /**
     * the array where the cache is temporary stored during runtime
     *
     * @var array
     */
    protected static $doOnceCache = [];

    /**
     * @return void
     */
    public static function enableDoOnce()
    {
        self::$doOnceEnabled = true;
    }

    /**
     * @return void
     */
    public static function disableDoOnce()
    {
        self::$doOnceEnabled = false;
    }

    /**
     * @return bool
     */
    public static function isEnabledDoOnce(): bool
    {
        if (isset($_ENV['DO_ONCE_GLOBALLY_DISABLED'])) {
            return $_ENV['DO_ONCE_GLOBALLY_DISABLED'];
        }

        return self::$doOnceEnabled;
    }

    /**
     * @param callable $callback
     * @param null $cacheKey
     * @param null $cacheType
     * @return array|mixed
     * @throws \ReflectionException
     */
    private static function doOnce(callable $callback, $cacheKey = null, $cacheType = null)
    {
        if (!self::isEnabledDoOnce()) {
            return $callback();
        }

        if (!is_null($cacheKey) && (!is_string($cacheKey) && !is_numeric($cacheKey) && !is_array($cacheKey))) {
            throw new InvalidArgumentException('cacheKey needs to be a string|int|array. given: '.$cacheKey);
        }

        if (!is_null($cacheType) && !is_string($cacheType)) {
            throw new InvalidArgumentException('cacheType needs to be a string. given: '.$cacheType);
        }

        if (is_null($cacheType)) {
            $trace =  debug_backtrace(
                DEBUG_BACKTRACE_PROVIDE_OBJECT, 2
            );

            $cacheTypePrefix = $trace[1]['function'].'_'.$trace[0]['line'];
        } else {
            $cacheTypePrefix = $cacheType;
        }

        // Since PHP 8.1 we can generate the cache key by closure used variables,
        // if they are set and no combinationCacheKey was passed manually, we use them
        // in PHP versions below 8.1 one needs to add these manually
        if (is_null($cacheKey) && PHP_VERSION_ID >= 80100) {
            $refFunc = new \ReflectionFunction($callback);
            $cacheKey = $refFunc->getClosureUsedVariables();
        }

        $cache = null;
        if (!is_null($cacheKey)) {
            $cacheKey = (is_array($cacheKey)) ? implode('_', $cacheKey) : $cacheKey ;
            if (!array_key_exists($cacheTypePrefix, self::$doOnceCache) || !array_key_exists($cacheKey, self::$doOnceCache[$cacheTypePrefix])) {
                self::$doOnceCache[$cacheTypePrefix][$cacheKey] = $callback();
            }

            $cache = self::$doOnceCache[$cacheTypePrefix][$cacheKey];
        } else {
            if (!array_key_exists($cacheTypePrefix, self::$doOnceCache)) {
                self::$doOnceCache[$cacheTypePrefix] = $callback();
            }

            $cache = self::$doOnceCache[$cacheTypePrefix];
        }

        return $cache;
    }
}