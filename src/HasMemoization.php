<?php

namespace LaracraftTech\Memoize;

trait HasMemoization
{
    /**
     * the array where the cache is temporary stored during runtime
     *
     * @var array
     */
    protected static $memoizationCache = [];

    /**
     * specifies if memoization should be applied
     *
     * @var bool
     */
    protected static $memoizationEnabled = true;

    /**
     * @return void
     */
    public static function enableMemoization()
    {
        self::$memoizationEnabled = true;
    }

    /**
     * @return void
     */
    public static function disableMemoization()
    {
        self::$memoizationEnabled = false;
    }

    /**
     * @return bool
     */
    public static function isEnabledMemoization(): bool
    {
        if (isset($_ENV['MEMOIZATION_GLOBALLY_DISABLED'])) {
            return $_ENV['MEMOIZATION_GLOBALLY_DISABLED'];
        }

        return self::$memoizationEnabled;
    }

    /**
     * @param callable $callback
     * @param null $combinationCacheKey
     * @return array|mixed
     * @throws \ReflectionException
     */
    private static function memoize(callable $callback, array $combiArgs = [])
    {
        if (!self::isEnabledMemoization()) {
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

        $hash = self::getMemoCacheHash($combiArgs, $trace);

        if (!array_key_exists($hash, self::$memoizationCache)) {
            self::$memoizationCache[$hash] = $callback();
        }

        return self::$memoizationCache[$hash];
    }

    private static function getMemoCacheHash(array $arguments, $trace): string
    {
        $prefix = $trace[1]['function'].'_'.$trace[0]['line'];

        $normalizedArguments = array_map(function ($argument) {
            return is_object($argument) ? spl_object_hash($argument) : $argument;
        }, $arguments);

        return md5($prefix.'_'.serialize($normalizedArguments));
    }
}