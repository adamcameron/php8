<?php

namespace System;

use PHPUnit\Framework\TestCase;

/** @testdox Tests of the Composer installation */
class ComposerTest extends TestCase
{
    /** @testdox It passes composer validate */
    public function testComposerValidates()
    {
        exec("composer validate 2> /dev/null", $output, $returnCode);
        $this->assertEquals(
            0,
            $returnCode,
            "Composer validate failed: " . implode("\n", $output)
        );
    }
}
