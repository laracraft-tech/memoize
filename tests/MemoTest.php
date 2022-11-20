<?php

namespace LaracraftTech\Memoize\Test;

use LaracraftTech\Memoize\DummyClass;
use LaracraftTech\Memoize\HasMemoization;
use LaracraftTech\Memoize\MemoHelper;

it('will run the a callback without arguments only once', function () {
    $testClass = new class() {
        use HasMemoization;

        public function getNumber()
        {
            return $this->memoize(function () {
                return rand(1, 10000000);
            });
        }
    };

    $firstResult = $testClass->getNumber();

    expect($firstResult)->toBeGreaterThanOrEqual(1);
    expect($firstResult)->toBeLessThanOrEqual(10000000);

    foreach (range(1, 100) as $i) {
        expect($testClass->getNumber())->toBe($firstResult);
    }
});

it('will run the given callback only once per use arguments combination', function () {
    $testClass = new class() {
        use HasMemoization;

        public function getNumberForLetter($letter)
        {
            return $this->memoize(function () use ($letter) {
                return $letter.rand(1, 10000000);
            }, [$letter]);
        }
    };

    foreach (range('A', 'Z') as $letter) {
        $firstResult = $testClass->getNumberForLetter($letter);
        expect($firstResult)->toStartWith($letter);

        foreach (range(1, 100) as $i) {
            expect($testClass->getNumberForLetter($letter))->toBe($firstResult);
        }
    }
});

it('will run the given callback only once for falsy result', function () {
    $testClass = new class() {
        use HasMemoization;

        public $counter = 0;

        public function getNull()
        {
            return $this->memoize(function () {
                $this->counter++;
            });
        }
    };

    expect($testClass->getNull())->toBeNull();
    expect($testClass->getNull())->toBeNull();
    expect($testClass->getNull())->toBeNull();

    expect($testClass->counter)->toBe(1);
});

// this approach do not work with unset objects...
//it('will work properly with unset objects', function () {
//    $previousNumbers = [];
//
//    foreach (range(1, 5) as $number) {
//        $testClass = new TestClass();
//
//        $number = $testClass->getRandomNumber();
//
//        expect($previousNumbers)->not()->toContain($number);
//
//        $previousNumbers[] = $number;
//
//        unset($testClass);
//    }
//});

it('will remember the memoized value when serialized when called in the same request', function () {
    $testClass = new TestClass();

    $firstNumber = $testClass->getRandomNumber();

    expect($testClass->getRandomNumber())->toBe($firstNumber);

    $serialized = serialize($testClass);
    $unserialized = unserialize($serialized);
    unset($unserialized);

    expect($testClass->getRandomNumber())->toBe($firstNumber);
});

it('can enable and disable the cache', function () {
    $testClass = new class() {
        use HasMemoization;

        public function getNumber()
        {
            return $this->memoize(function () {
                return random_int(1, 10000000);
            });
        }
    };

    expect($testClass->isEnabledMemoization())->toBeTrue();
    expect($testClass->getNumber())->toBe($testClass->getNumber());

    $testClass->disableMemoization();
    expect($testClass->isEnabledMemoization())->toBeFalse();
    expect($testClass->getNumber())->not()->toBe($testClass->getNumber());

    $testClass->enableMemoization();
    expect($testClass->isEnabledMemoization())->toBeTrue();
    expect($testClass->getNumber())->toBe($testClass->getNumber());
});
//
//it('will not throw error with eval', function () {
//    $result = eval('return once( function () { return random_int(1, 1000); } ) ;');
//
//    expect(in_array($result, range(1, 1000)))->toBeTrue();
//});

it('will differentiate between closures', function () {
    $testClass = new class() {
        use HasMemoization;

        public function getNumber()
        {
            $closure = function () {
                return $this->memoize(function () {
                    return random_int(1, 1000);
                });
            };

            return $closure();
        }

        public function secondNumber()
        {
            $closure = function () {
                return $this->memoize(function () {
                    return random_int(1001, 2000);
                });
            };

            return $closure();
        }
    };

    expect($testClass->secondNumber())->not()->toBe($testClass->getNumber());
});

it('will run faster then spatie/once', function () {
    $start1 = microtime(true);
    $testClass1 = new class() {
        use HasMemoization;

        public function getNumber()
        {
            return $this->memoize(function () {
                return rand(1, 10000000);
            });
        }
    };

    foreach (range(1, 10000) as $i) {
        $testClass1->getNumber();
    }
    $end1 = microtime(true);
    $diff1 = $end1-$start1;

    //spatie
    $start2 = microtime(true);
    $testClass2 = new class() {
        public function getNumber()
        {
            return once(function () {
                return rand(1, 10000000);
            });
        }
    };

    foreach (range(1, 10000) as $i) {
        $testClass2->getNumber();
    }
    $end2 = microtime(true);
    $diff2 = $end2-$start2;

//    dump($diff1, $diff2);
    expect($diff1)->toBeLessThan($diff2);
});