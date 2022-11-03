<?php

namespace LaracraftTech\DoOnce\Test;

use LaracraftTech\DoOnce\HasDoOnce;

class TestClass
{
    use HasDoOnce;

    protected $randomNumber;

    public function __construct()
    {
        $this->randomNumber = rand(1, 1000000);
    }

    public function getRandomNumber()
    {
        return $this->doOnce(function () {
            return $this->randomNumber;
        });
    }
}