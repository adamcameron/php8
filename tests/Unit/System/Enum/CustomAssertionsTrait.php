<?php

namespace adamcameron\php8\tests\Unit\System\Enum;

use PHPUnit\Framework\AssertionFailedError;

trait CustomAssertionsTrait
{
    public function assertError(callable $callback, string $message)
    {
        try {
            $callback();
            $this->fail("Expected an error, didn't get one");
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->assertStringContainsString($message, $e->getMessage());
        }
    }
}
