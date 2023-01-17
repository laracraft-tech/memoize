<?php

namespace LaracraftTech\Memoize;

trait HasMemoization
{
    /**
     * the array where the cache is temporary stored during runtime
     *
     * @var array
     */
    protected $memoizationCache = [];

    /**
     * specifies if memoization should be applied
     *
     * @var bool
     */
    protected $memoizationEnabled = true;

    /**
     * @return void
     */
    public function enableMemoization()
    {
        $this->memoizationEnabled = true;
    }

    /**
     * @return void
     */
    public function disableMemoization()
    {
        $this->memoizationEnabled = false;
    }

    /**
     * @return bool
     */
    public function isEnabledMemoization(): bool
    {
        if (isset($_ENV['MEMOIZATION_GLOBALLY_DISABLED'])) {
            return $_ENV['MEMOIZATION_GLOBALLY_DISABLED'];
        }

        return $this->memoizationEnabled;
    }

    /**
     * @param callable $callback
     * @param array $customCombiArgs
     * @return array|mixed
     * @throws \ReflectionException
     */
    public function memoize(callable $callback, array $customCombiArgs = [])
    {
        if (!$this->isEnabledMemoization()) {
            return $callback();
        }

        $trace =  debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $prefix = $trace[1]['function'].'_'.$trace[0]['line'];

        // Since PHP 8.1 we can generate combination cache key by closure used variables,
        // if they are set and no combinationCacheKey was passed manually, we use them
        if (empty($customCombiArgs)) {
            $refFunc = new \ReflectionFunction($callback);
            $combiArgs = $refFunc->getClosureUsedVariables();
        } else {
            $combiArgs = $customCombiArgs;
        }

        $hash = $this->getMemoCacheHash($combiArgs, $prefix);

        if (!array_key_exists($hash, $this->memoizationCache)) {
            $this->memoizationCache[$hash] = $callback();
        }

        return $this->memoizationCache[$hash];
    }

    private function getMemoCacheHash(array $arguments, string $prefix): string
    {
        $normalizedArguments = array_map(function ($argument) {
            return is_object($argument) ? spl_object_hash($argument) : $argument;
        }, $arguments);

        return md5($prefix.'_'.serialize($normalizedArguments));
    }
}