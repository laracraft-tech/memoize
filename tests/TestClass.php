<?php

namespace LaracraftTech\Memoize\Tests;

use LaracraftTech\Memoize\HasMemoization;

class TestClass
{
    use HasMemoization;

    protected $randomNumber;

    public function __construct()
    {
        $this->randomNumber = rand(1, 1000000);
    }

    public function getRandomNumber()
    {
        return $this->memoize(function () {
            return $this->randomNumber;
        });
    }
}
