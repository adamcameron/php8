<?php

namespace adamcameron\php8\tests\Integration\Http;

use PHPUnit\Framework\TestCase;

/** @testdox Tests of HTTP request functionality */
abstract class HttpTestBase extends TestCase
{

    protected function getUserAgentForCurl(): string
    {
        return sprintf("curl/%s", curl_version()['version']);
    }

    protected function assertGitInfoIsCorrect(string $response): void
    {
        $myGitMetadata = json_decode($response);
        $this->assertEquals('adamcameron', $myGitMetadata->login);
        $this->assertEquals('Adam Cameron', $myGitMetadata->name);
    }
}
