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
    private static function doOnce(callable $callback, array $combiArgs = [])
    {
        if (!self::isEnabledDoOnce()) {
            return $callback();
        }

        $trace =  debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);

        // Since PHP 8.1 we can generate combination cache key by closure used variables,
        // if they are set and no combinationCacheKey was passed manually, we use them
        // in PHP versions below 8.1 one needs to add these manually
        if (empty($combiArgs)) {
            if (PHP_VERSION_ID >= 80100) {
                $refFunc = new \ReflectionFunction($callback);
                $combiArgs = $refFunc->getClosureUsedVariables();
            } else {
                $combiArgs = $trace[1]['args'];
            }
        }

        $hash = self::getDoOnceCacheHash($combiArgs, $trace);

        if (!array_key_exists($hash, self::$doOnceCache)) {
            self::$doOnceCache[$hash] = $callback();
        }

        return self::$doOnceCache[$hash];
    }

    private static function getDoOnceCacheHash(array $arguments, $trace): string
    {
        $prefix = $trace[1]['function'].'_'.$trace[0]['line'];

        $normalizedArguments = array_map(function ($argument) {
            return is_object($argument) ? spl_object_hash($argument) : $argument;
        }, $arguments);

        return md5($prefix.'_'.serialize($normalizedArguments));
    }
}