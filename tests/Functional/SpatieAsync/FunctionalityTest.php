<?php

namespace adamcameron\php8\tests\Functional\SpatieAsync;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Pool;

#[TestDox("tests of spatie/async functionality (https://github.com/spatie/async)")]
class FunctionalityTest extends TestCase
{

    #[TestDox("It supports exception handling")]
    public function testAsyncException()
    {
        $pool = Pool::create();
        $pool
            ->add(function () {
                throw new \Exception("This is an exception");
            })
            ->catch(function (\Exception $exception) {
                $this->assertStringStartsWith(
                    "This is an exception",
                    $exception->getMessage()
                );
            });

        $pool->wait();
    }

    #[TestDox("It does not support exception handling from a then handler")]
    public function testAsyncExceptionFromThen()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("This is an exception");

        $pool = Pool::create();
        $pool
            ->add(function () {
                // do nothing
            })
            ->then(function () {
                throw new \Exception("This is an exception");
            })
            ->catch(function (\Exception $exception) {
                $this->assertStringStartsWith(
                    "This is an exception",
                    $exception->getMessage()
                );
            });

        $pool->wait();
    }
}
