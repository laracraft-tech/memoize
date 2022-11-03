<?php

namespace LaracraftTech\DoOnce;

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
        return self::$doOnceEnabled;
    }

    /**
     * @param callable $callback
     * @return array|mixed
     */
    private static function doOnce(callable $callback)
    {
        if (!self::$doOnceEnabled) {
            return $callback();
        }

        $trace =  debug_backtrace(
            DEBUG_BACKTRACE_PROVIDE_OBJECT, 2
        );

        $cache = null;
        $cacheTypePrefix = $trace[1]['function'].'_'.$trace[0]['line'];

        $cacheKey = null;
        if (!empty($trace[1]['args'])) {
            $cacheKey = implode('_', $trace[1]['args']);
        }

        if (!is_null($cacheKey)) {
            if (!array_key_exists($cacheTypePrefix, self::$doOnceCache) || !array_key_exists($cacheKey, self::$doOnceCache[$cacheTypePrefix])) {
                self::$doOnceCache[$cacheTypePrefix][$cacheKey] = $callback() ?? null;
            }

            $cache = self::$doOnceCache[$cacheTypePrefix][$cacheKey];
        } else {
            if (!array_key_exists($cacheTypePrefix, self::$doOnceCache)) {
                self::$doOnceCache[$cacheTypePrefix] = $callback() ?? null;
            }

            $cache = self::$doOnceCache[$cacheTypePrefix];
        }

        return $cache;
    }
}