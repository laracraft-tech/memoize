<?php

namespace LaracraftTech\DoOnce;

use Dotenv\Dotenv;

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
     * @param null $combinationCacheKey
     * @return array|mixed
     * @throws \ReflectionException
     */
    private static function doOnce(callable $callback, $combinationCacheKey = null)
    {
        if (!self::isEnabledDoOnce()) {
            return $callback();
        }

        $trace =  debug_backtrace(
            DEBUG_BACKTRACE_PROVIDE_OBJECT, 2
        );

        $cache = null;
        $cacheTypePrefix = $trace[1]['function'].'_'.$trace[0]['line'];

        // Since PHP 8.1 we can generate combination cache key by closure used variables,
        // if they are set and no combinationCacheKey was passed manually, we use them
        // in PHP versions below 8.1 one needs to add these manually
        if (is_null($combinationCacheKey) && PHP_VERSION_ID >= 80100) {
            $refFunc = new \ReflectionFunction($callback);
            $combinationCacheKey = $refFunc->getClosureUsedVariables();
        }


        if (!is_null($combinationCacheKey)) {
            $combinationCacheKey = (is_array($combinationCacheKey)) ? implode('_', $combinationCacheKey) : $combinationCacheKey ;
            if (!array_key_exists($cacheTypePrefix, self::$doOnceCache) || !array_key_exists($combinationCacheKey, self::$doOnceCache[$cacheTypePrefix])) {
                self::$doOnceCache[$cacheTypePrefix][$combinationCacheKey] = $callback();
            }

            $cache = self::$doOnceCache[$cacheTypePrefix][$combinationCacheKey];
        } else {
            if (!array_key_exists($cacheTypePrefix, self::$doOnceCache)) {
                self::$doOnceCache[$cacheTypePrefix] = $callback();
            }

            $cache = self::$doOnceCache[$cacheTypePrefix];
        }

        return $cache;
    }
}